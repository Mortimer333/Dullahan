<?php

declare(strict_types=1);

namespace Dullahan\AssetManager;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Document\JackrabbitAsset;
use Dullahan\Entity\Asset;
use Dullahan\Repository\AssetRepository;
use Dullahan\Service\AssetManager\Jackrabbit\JackrabbitRuntimeCachePoolService;
use Jackalope\Session;
use PHPCR\NodeInterface;
use PHPCR\PropertyType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class JackrabbitAssetManager implements AssetManagerInterface
{
    public const PROPERTY_EXTENSION = 'extension';
    public const PROPERTY_MIME_TYPE = 'mimeType';
    public const PROPERTY_WEIGHT = 'weight';
    public const PROPERTY_FILE = 'file';

    protected bool $requiresFlush = false;
    protected bool $requiresSave = false;
    protected Session $session;

    public function __construct(
        protected DocumentManagerInterface $documentManager,
        protected AssetRepository $assetRepository,
        protected JackrabbitRuntimeCachePoolService $runtimeCache,
        protected EntityManagerInterface $em,
    ) {
        $this->session = $this->documentManager->getPhpcrSession();
    }

    /**
     * @inheritDoc
     */
    public function get(int $id): AssetInterface
    {
        return $this->uniGet('id', (string) $id);
    }

    /**
     * @inheritDoc
     */
    public function getByPath(string $path): AssetInterface
    {
        return $this->uniGet('path', $path);
    }

    /**
     * @inheritDoc
     */
    public function upload(string $path, string $name, UploadedFile $file, array $properties = []): AssetInterface
    {
        $parent = $this->session->getNode($path);

        // @TODO figure out node types
        //   There are specific default types in Jackrabbit, something like file or folder - it might be necessary to
        //   set them up if we want to enable WebDAV for usage. It might change how we save files contents
        $node = $parent->addNode($name, 'nt:unstructured');

        $this->updateNodeProperties($node, $file);
        foreach ($properties as $name => $value) {
            $node->setProperty($name, $value);
        }

        $asset = new Asset();
        $asset->setPath($path . '/' . $name);
        $this->assetRepository->save($asset);
        $this->requiresFlush = true;
        $this->requiresSave = true;

        return new JackrabbitAsset($asset, $node);
    }

    /**
     * @inheritDoc
     */
    public function reupload(AssetInterface $asset, UploadedFile $file): AssetInterface
    {
        $this->validateIsJackrabbitAsset($asset);

        $this->updateNodeProperties($asset->getNode(), $file);
        $this->requiresSave = true;

        return $asset;
    }

    /**
     * @inheritDoc
     */
    public function exists(string $path): bool
    {
        return $this->session->nodeExists($path);
    }

    /**
     * @inheritDoc
     */
    public function remove(AssetInterface $asset): bool
    {
        $this->validateIsJackrabbitAsset($asset);
        $asset->markToRemove(true);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function move(AssetInterface $asset, string $path): AssetInterface
    {
        $this->validateIsJackrabbitAsset($asset);
        /** @var JackrabbitAsset $asset */
        $this->session->move($asset->getPath(), $path);
        $entity = $asset->getEntity();
        $entity->setPath($path);
        $this->assetRepository->save($entity);
        $this->requiresFlush = true;
        $this->requiresSave = true;

        return $asset;
    }

    /**
     * @inheritDoc
     */
    public function duplicate(AssetInterface $asset, string $path): AssetInterface
    {
        $workspace = $this->session->getWorkspace();
        $workspace->copy($asset->getPath(), $path);
        $asset = new Asset();
        $asset->setPath($path);
        $this->assetRepository->save($asset);
        $this->requiresFlush = true;

        return $this->getByPath($path);
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $flushDoctrine = $this->requiresFlush;
        $save = $this->requiresSave;
        foreach ($this->runtimeCache->traverse() as $item) {
            if (!$item->isHit()) {
                continue;
            }

            /** @var JackrabbitAsset $asset */
            $asset = $item->get();
            if ($asset->requiresFlush()) {
                $flushDoctrine = true;
            }

            if ($asset->isDirty()) {
                $save = true;
            }
        }


        if ($flushDoctrine) {
            $this->em->flush();
        }

        if ($save) {
            $this->session->save();
        }

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


    protected function validateIsJackrabbitAsset(AssetInterface $asset): void
    {
        if (!($asset instanceof JackrabbitAsset)) {
            throw new \InvalidArgumentException(
                sprintf('Jackrabbit Asset Manager only manages Jackrabbit Assets not %s', $asset::class),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    protected function updateNodeProperties(NodeInterface $node, UploadedFile $file): void
    {
        $node->setProperty(self::PROPERTY_WEIGHT, (int) $file->getSize(), PropertyType::STRING);
        $node->setProperty(self::PROPERTY_EXTENSION, (string) $file->guessExtension(), PropertyType::STRING);
        $node->setProperty(self::PROPERTY_MIME_TYPE, (string) $file->getMimeType(), PropertyType::LONG);
        $node->setProperty(self::PROPERTY_FILE, fopen($file->getRealPath()), PropertyType::BINARY);
    }

    protected function uniGet(string $type, string $id): AssetInterface
    {
        $item = $this->runtimeCache->getItem($id);
        if ($item->isHit()) {
            return $item->get();
        }

        $assetEntity = $this->assetRepository->findOneBy([$type => $id]);
        if (!$assetEntity) {
            throw new \Exception("Asset not found", Response::HTTP_NOT_FOUND);
        }
        $node = $this->session->getNode($assetEntity->getPath());
        if (!$node) {
            throw new \Exception("Asset File not found", Response::HTTP_NOT_FOUND);
        }

        $asset = new JackrabbitAsset($assetEntity, $node);

        $item->set($asset);
        $this->runtimeCache->save($asset);

        return $asset;
    }
}
