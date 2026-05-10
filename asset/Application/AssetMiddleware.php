<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset\ListListener;
use Dullahan\Asset\Domain\Directory;
use Dullahan\Asset\Domain\Exception\AssetExistsException;
use Dullahan\Asset\Domain\Exception\AssetInvalidNameException;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\File;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetMiddlewareInterface;
use Dullahan\Asset\Port\Presentation\AssetPersistManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetRetrievalManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetSerializeManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Main\Model\Context;

class AssetMiddleware implements AssetMiddlewareInterface
{
    public const CONTROLLER_TYPE = 'controller';

    public function __construct(
        protected AssetPersistenceManagerInterface $assetManager,
        protected EntityManagerInterface $em,
        protected AssetServiceInterface $assetService,
        protected AssetRetrievalManagerInterface $assetRetrievalManager,
        protected AssetPersistManagerInterface $assetPersistManager,
        private AssetSerializeManagerInterface $assetSerializeManager,
    ) {
    }

    public function serialize(int $id): array
    {
        $context = $this->generateControllerContext();

        return $this->assetSerializeManager->serialize(
            $this->assetRetrievalManager->get($id, $context),
            $context,
        );
    }

    public function retrieve(int $id): array
    {
        return $this->serialize($id);
    }

    public function move(string $from, string $to): array
    {
        if (!$this->assetRetrievalManager->exists($from, $this->generateControllerContext())) {
            throw new AssetNotFoundException($from);
        }

        if ($this->assetRetrievalManager->exists($to, $this->generateControllerContext())) {
            throw new AssetExistsException($to);
        }

        $asset = $this->assetRetrievalManager->getByPath($from);
        $asset = $this->assetPersistManager->move($asset, $to, $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializeManager->serialize($asset);
    }

    public function list(array $pagination): array
    {
        $images = [];
        $assets = $this->assetRetrievalManager->list(
            new Context([
                ListListener::LIMIT => $pagination['limit'] ?? null,
                ListListener::OFFSET => $pagination['offset'] ?? null,
                ListListener::FILTER => $pagination['filter'] ?? null,
                ListListener::SORT => $pagination['sort'] ?? null,
                ListListener::JOIN => $pagination['join'] ?? null,
                ListListener::GROUP => $pagination['group'] ?? null,
                ListListener::COUNT => (bool) ($pagination['count'] ?? true),
                Context::TYPE => self::CONTROLLER_TYPE,
            ])
        );
        foreach ($assets as $asset) {
            $images[] = $this->assetSerializeManager->serialize($asset);
        }

        return $images;
    }

    public function upload(
        string $name,
        string $path,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array {
        if (!$this->assetService->validName($name, $this->generateControllerContext())) {
            throw new AssetInvalidNameException($name);
        }

        $fullPath = rtrim($path, '/') . '/' . $name;
        if ($this->assetRetrievalManager->exists($fullPath, $this->generateControllerContext())) {
            throw new AssetExistsException($fullPath);
        }

        $file = $this->assetPersistManager->create(
            new File(
                $path,
                $name,
                $originalName,
                $resource,
                $size,
                $extension,
                $mimeType,
            ),
            $this->generateControllerContext(),
        );
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializeManager->serialize($file);
    }

    public function folder(string $parent, string $name): array
    {
        if (!$this->assetService->validName($name, $this->generateControllerContext())) {
            throw new AssetInvalidNameException($name);
        }

        $path = rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if ($this->assetRetrievalManager->exists($path, $this->generateControllerContext())) {
            throw new AssetExistsException($path);
        }

        $file = $this->assetPersistManager->create(
            new Directory($path),
            $this->generateControllerContext(),
        );
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializeManager->serialize($file);
    }

    public function reupload(
        int $id,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array {
        $asset = $this->assetRetrievalManager->get($id, $this->generateControllerContext());
        $asset = $this->assetPersistManager->replace($asset, new File(
            $asset->structure->path,
            $asset->structure->name,
            $originalName,
            $resource,
            $size,
            $extension,
            $mimeType,
        ), $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializeManager->serialize($asset);
    }

    public function remove(int $id): void
    {
        $this->assetPersistManager->remove($this->assetRetrievalManager->get($id), $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());
    }

    protected function generateControllerContext(): Context
    {
        return new Context([
            Context::TYPE => self::CONTROLLER_TYPE,
        ]);
    }
}
