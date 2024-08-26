<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Service;

use Dullahan\Asset\Application\Exception\AssetNotFoundException;
use Dullahan\Asset\Application\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Directory;
use Dullahan\Asset\Domain\File;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailPersisterInterface;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailRetrieveInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailGeneratorInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailMapperInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailServiceInterface;
use Dullahan\Thumbnail\Domain\Thumbnail;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;

/**
 * @TODO refactor to be Event based.
 *   - generate - mapping/reading should be moved to event listener
 *   - generateWithConfig - finding match should be a separate event listener
 *                        - creating pointer should bea separate event listener
 *                        - file generation should be separate event listener
 *                        - thumbnail db persisting should be separate event listener
 */
final readonly class ThumbnailService implements ThumbnailServiceInterface
{
    public function __construct(
        protected AssetFileManagerInterface $assetFileManager,
        protected AssetServiceInterface $assetService,
        protected ThumbnailMapperInterface $thumbnailMapper,
        protected ThumbnailGeneratorInterface $thumbnailGenerator,
        protected ThumbnailRetrieveInterface $thumbnailRetrieve,
        protected ThumbnailPersisterInterface $thumbnailPersist,
    ) {
    }

    public function get(mixed $id): Thumbnail
    {
        $entity = $this->thumbnailRetrieve->get((int) $id);
        $structure = $this->assetFileManager->get((string) $entity->getPath());

        return new Thumbnail($structure, $entity, new Context());
    }

    public function getByPath(string $path): Thumbnail
    {
        $entity = $this->thumbnailRetrieve->getByPath($path);
        $structure = $this->assetFileManager->get($path);

        return new Thumbnail($structure, $entity, new Context());
    }

    public function getThumbnails(Asset $asset): array
    {
        $thumbnails = [];
        $entities = $this->thumbnailRetrieve->getThumbnails($asset->entity);
        foreach ($entities as $entity) {
            $thumbnails[] = $this->get($entity->getId());
        }

        return $thumbnails;
    }

    public function getThumbnailsByPointer(AssetPointerInterface $pointer): array
    {
        $thumbnails = [];
        $entities = $this->thumbnailRetrieve->getThumbnailsByPointer($pointer);
        foreach ($entities as $entity) {
            $thumbnails[] = $this->get($entity->getId());
        }

        return $thumbnails;
    }

    public function generate(AssetAwareInterface $asset, string $fieldName): array
    {
        BinUtilService::logToTest('Generate thumbnails: ' . $fieldName, 'a');
        $thumbnails = [];
        $mapped = $this->thumbnailMapper->mapField($asset, $fieldName);
        BinUtilService::logToTest('Mapped: ' . count($mapped), 'a');
        foreach ($mapped as $config) {
            if ($thumbnail = $this->generateWithConfig($config)) {
                $thumbnails[] = $thumbnail;
            }
        }

        return $thumbnails;
    }

    public function flush(): void
    {
        $this->assetFileManager->flush();
        $this->thumbnailPersist->flush();
    }

    public function generateWithConfig(ThumbnailConfig $config): ?Thumbnail
    {
        $exitingThumbnailEntity = $this->thumbnailRetrieve->findSame($config->assetId, $config);
        if ($exitingThumbnailEntity && $this->assetFileManager->exists((string) $exitingThumbnailEntity->getPath())) {
            $this->thumbnailPersist->createPointer($exitingThumbnailEntity, $config->pointerId, $config->code);

            return new Thumbnail(
                $this->assetFileManager->get((string) $exitingThumbnailEntity->getPath()),
                $exitingThumbnailEntity,
                new Context(),
            );
        }

        try {
            $asset = $this->assetService->get($config->assetId);
            $assetEntity = $asset->entity;
        } catch (AssetNotFoundException) {
            return null;
        }

        $filename = $config->getFingerPrint() . '.' . $asset->structure->extension;
        $thumbFile = $this->thumbnailGenerator->generate($config, $filename);
        $path = rtrim($this->getThumbnailRoot()->structure->path, '/') . '/'
            . trim($asset->structure->path, '/') . '/'
        ;

        if (!$this->assetFileManager->exists($path)) {
            $this->assetService->create(
                new Directory($path),
                new Context([AssetFileManagerInterface::RECURSIVE => true])
            );
        }

        $structure = $this->assetFileManager->upload(
            new File(
                $path,
                $filename,
                $filename,
                $thumbFile,
                fstat($thumbFile)['size'] ?? 0,
                (string) $asset->structure->extension,
                (string) $asset->structure->mimeType,
            )
        );

        $entity = $this->thumbnailPersist->create($assetEntity, $path . $filename, $filename, $thumbFile, $config);

        return new Thumbnail($structure, $entity, new Context());
    }

    protected function getThumbnailRoot(): Asset
    {
        $path = '/.thumbnail/';
        if ($this->assetService->exists($path)) {
            return $this->assetService->getByPath($path);
        }

        $root = $this->assetService->create(new Directory($path));
        $this->assetService->flush();

        return $root;
    }
}
