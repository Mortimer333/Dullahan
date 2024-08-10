<?php

declare(strict_types=1);

namespace Dullahan\Asset\Manager;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Contract\AssetManager\UploadedFileInterface;
use Dullahan\Document\JackrabbitAsset;
use Dullahan\Entity\Asset;
use Dullahan\Entity\User;
use Dullahan\Exception\AssetManager\AssetEntityNotFoundException;
use Dullahan\Exception\AssetManager\AssetExistsException;
use Dullahan\Exception\AssetManager\AssetNotFoundException;
use Dullahan\Exception\AssetManager\MissingParentException;
use Dullahan\Repository\AssetRepository;
use Dullahan\Service\AssetManager\Jackrabbit\JackrabbitRuntimeCachePoolService;
use Dullahan\Service\UserService;
use Jackalope\Session;
use PHPCR\ItemExistsException;
use PHPCR\NodeInterface;
use PHPCR\PathNotFoundException;
use PHPCR\PropertyType;

class JackrabbitAssetManager implements AssetManagerInterface
{
    public const PROPERTY_MIME_TYPE = "jcr:mimeType";
    public const PROPERTY_FILE = "jcr:data";
    public const CONTENT_META_NAME = "jcr:content";
    public const CONTENT_RESOURCE_TYPE =  "nt:resource";
    public const CONTENT_FILE_TYPE =  "nt:file";
    public const CONTENT_FOLDER_TYPE =  "nt:folder";
    /** @var \WeakMap<AssetInterface, true> */
    protected \WeakMap $toRemove;
    protected Session $session;

    /**
     * @TODO make interfaces for AssetRepository and UserService
     */
    public function __construct(
        protected DocumentManagerInterface $documentManager,
        protected AssetRepository $assetRepository,
        protected JackrabbitRuntimeCachePoolService $runtimeCache,
        protected EntityManagerInterface $em,
        protected UserService $userService,
    ) {
        $this->session = $this->documentManager->getPhpcrSession();
        $this->toRemove = new \WeakMap();
    }

    public function get(int $id): AssetInterface
    {
        return $this->uniGet('id', (string) $id);
    }

    public function getByPath(string $path): AssetInterface
    {
        return $this->uniGet('path', $path);
    }

    public function folder(string $path, string $name, ?User $owner = null): AssetInterface
    {
        $node = $this->createStructure($path, $name, self::CONTENT_FOLDER_TYPE);
        $asset = new Asset();
        $asset->setUser($owner ?? $this->userService->getLoggedInUser());
        $this->assetRepository->save($asset);
        $this->updateEntity(
            $asset,
            $node->getPath(),
            $node->getName(),
            0,
            '',
            'folder',
        );

        return $this->generateJackrabbitAssetProxy($asset, fn() => $node);
    }

    public function upload(
        string $path,
        string $name,
        UploadedFileInterface $file,
        ?User $owner = null,
    ): AssetInterface {
        $node = $this->createStructure($path, $name . '.' . $file->getExtension(), self::CONTENT_FILE_TYPE);
        $content = $node->addNode(self::CONTENT_META_NAME, self::CONTENT_RESOURCE_TYPE);
        $content->setProperty(self::PROPERTY_FILE, $file->getResource(), PropertyType::BINARY);

        $asset = new Asset();
        $asset->setUser($owner ?? $this->userService->getLoggedInUser());
        $this->assetRepository->save($asset);
        $this->updateNodeProperties($node, $file, $asset);

        return $this->generateJackrabbitAssetProxy($asset, fn() => $node);
    }

    public function exists(string $path): bool
    {
        return $this->session->nodeExists($path);
    }

    public function remove(AssetInterface $asset): bool
    {
        if ($this->toRemove->offsetExists($asset)) {
            return false;
        }

        $this->toRemove->offsetSet($asset, true);

        return true;
    }

    public function dontRemove(AssetInterface $asset): bool
    {
        if (!$this->toRemove->offsetExists($asset)) {
            return false;
        }

        $this->toRemove->offsetUnset($asset);

        return true;
    }

    public function move(AssetInterface $asset, string $path, ?UploadedFileInterface $file = null): AssetInterface
    {
        if ($file) {
            $asset = $this->reupload($asset, $file);
        }

        if ($asset->getPath() !== $path) {
            $this->session->move($asset->getPath(), $path);
            $entity = $asset->getEntity();
            $entity->setPath($path);
            $this->assetRepository->save($entity);
        }

        return $asset;
    }

    public function clone(AssetInterface $asset, string $path): AssetInterface
    {
        $workspace = $this->session->getWorkspace();
        $workspace->copy($asset->getPath(), $path);
        $newAsset = new Asset();
        $newAsset->setUser($owner ?? $this->userService->getLoggedInUser());
        $this->updateEntity(
            $newAsset,
            $path,
            basename($path, '.' . $asset->getExtension()),
            (int) $asset->getWeight(),
            (string) $asset->getExtension(),
            (string) $asset->getMimeType(),
        );
        $this->assetRepository->save($newAsset);

        return $this->generateJackrabbitAssetProxy(
            $newAsset,
            fn() => $this->getNode($newAsset->getPath()),
        );
    }

    public function flush(): void
    {
        foreach ($this->toRemove->getIterator() as $asset => $value) {
            $this->em->remove($asset->getEntity());
            try {
                $this->getNode($asset->getPath())->remove();
            } catch (AssetNotFoundException) {
                // Do nothing - file might not exist but entity in database does (desynchronization via user actions)
            }
        }

        $this->em->flush();
        $this->session->save();

        foreach ($this->runtimeCache->traverse() as $item) {
            if (!$item->isHit()) {
                continue;
            }

            /** @var JackrabbitAsset $asset */
            $asset = $item->get();
            $asset->resetFlush();
            $asset->markAsClean();
        }
    }

    public function clear(): void
    {
        $this->em->clear();
        $this->runtimeCache->clear();
        $this->session->clear();
    }

    protected function createStructure(string $path, string $name, string $type): NodeInterface
    {
        try {
            $parent = $this->session->getNode($path === '/' ? $path : rtrim($path, '/'));
        } catch (PathNotFoundException) {
            throw new AssetNotFoundException($path);
        }

        try {
            return $parent->addNode($name, $type);
        } catch (ItemExistsException) {
            throw new AssetExistsException(rtrim($path, '/') . '/' . $name);
        }
    }

    protected function updateNodeProperties(NodeInterface $node, UploadedFileInterface $file, Asset $asset): void
    {
        $this->updateEntity(
            $asset,
            $node->getPath(),
            $node->getName(),
            $file->getSize(),
            $file->getExtension(),
            $file->getMimeType(),
        );
        $content = $node->getNode(self::CONTENT_META_NAME);
        $content->setProperty(self::PROPERTY_MIME_TYPE, $file->getMimeType());
    }

    protected function updateEntity(
        Asset $asset,
        string $path,
        string $name,
        int $weight,
        string $extension,
        string $mime,
    ): void {
        $asset->setPath($path);
        $asset->setName($name);
        $asset->setWeight($weight);
        $asset->setExtension($extension);
        $asset->setMimeType($mime);
    }

    protected function uniGet(string $type, string $id): AssetInterface
    {
        $item = $this->runtimeCache->getItem($id);
        if ($item->isHit()) {
            return $item->get();
        }

        $assetEntity = $this->assetRepository->findOneBy([$type => $id]);
        if (!$assetEntity) {
            throw new AssetEntityNotFoundException("Asset not found");
        }

        $nodeDecorator = function() use ($assetEntity) {
            return $this->getNode($assetEntity->getPath());
        };

        $asset = $this->generateJackrabbitAssetProxy($assetEntity, $nodeDecorator);

        $item->set($asset);
        $this->runtimeCache->save($item);

        return $asset;
    }

    protected function getNode(string $path): NodeInterface
    {
        try {
            return $this->session->getNode($path);
        } catch (PathNotFoundException) {
            throw new AssetNotFoundException($path);
        }
    }

    protected function generateJackrabbitAssetProxy(AssetInterface $asset, \Closure $nodeDecorator): JackrabbitAsset
    {
        return new JackrabbitAsset(
            $asset,
            $nodeDecorator,
            function(JackrabbitAsset $asset): ?JackrabbitAsset
            {
                if ($asset->getPath() === '/') {
                    return null;
                }

                return $this->getByPath(dirname($asset->getPath()));
            },
            function(JackrabbitAsset $asset, ?string $nameMatch = null, ?string $typeMatch = null): array|\Iterator
            {
                foreach ($this->getNode($asset->getPath())->getNodes($nameMatch, $typeMatch) as $item) {
                    yield $this->getByPath($item->getPath());
                }
            },
        );
    }

    protected function reupload(AssetInterface $asset, UploadedFileInterface $file): AssetInterface
    {
        try {
            $node = $this->getNode($asset->getPath());
        } catch (AssetNotFoundException) {
            $node = $this->regenerateNode($asset->getPath());
        }

        try {
            $content = $node->getNode(self::CONTENT_META_NAME);
        } catch (PathNotFoundException) {
            $content = $node->addNode(self::CONTENT_META_NAME, self::CONTENT_RESOURCE_TYPE);
        }

        $content->setProperty(self::PROPERTY_FILE, $file->getResource(), PropertyType::BINARY);
        $this->updateNodeProperties($node, $file, $asset->getEntity());
        return $this->generateJackrabbitAssetProxy($asset->getEntity(), fn() => $node);
    }

    protected function regenerateNode(string $path): NodeInterface
    {
        try {
            $parent = $this->getNode(dirname($path));
        } catch (AssetNotFoundException) {
            throw new MissingParentException($path);
        }

        return $parent->addNode(basename($path), self::CONTENT_FILE_TYPE);
    }
}
