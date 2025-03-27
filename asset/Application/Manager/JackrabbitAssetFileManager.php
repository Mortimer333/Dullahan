<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Manager;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Dullahan\Asset\Application\Exception\AssetExistsException;
use Dullahan\Asset\Application\Exception\AssetNotFoundException;
use Dullahan\Asset\Application\Exception\MissingParentException;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Domain\StructureTypeEnum;
use PHPCR\ItemExistsException;
use PHPCR\NodeInterface;
use PHPCR\PathNotFoundException;
use PHPCR\PropertyType;
use PHPCR\SessionInterface;

class JackrabbitAssetFileManager implements AssetFileManagerInterface
{
    public const PROPERTY_MIME_TYPE = 'jcr:mimeType';
    public const PROPERTY_FILE = 'jcr:data';
    public const PROPERTY_SIZE = 'rep:size';

    public const NODE_NAME_CONTENT = 'jcr:content';
    public const NODE_NAME_PROPERTIES = 'dl:properties';

    public const TYPE_CONTENT_RESOURCE = 'nt:resource';
    public const TYPE_UNSTRUCTURED = 'nt:unstructured';
    public const TYPE_CONTENT_FILE = 'dl:file';
    public const TYPE_CONTENT_FOLDER = 'dl:folder';

    /** @var \WeakMap<Structure, true> */
    protected \WeakMap $toRemove;
    protected SessionInterface $session;

    /** @var array<array<string>> */
    protected array $toClone = [];

    public function __construct(
        protected DocumentManagerInterface $documentManager,
    ) {
        $this->session = $this->documentManager->getPhpcrSession();
        $this->toRemove = new \WeakMap();
    }

    public function get(string $path): Structure
    {
        return $this->generateStructure($this->getNode($path));
    }

    public function folder(NewStructureInterface $folder): Structure
    {
        $node = $this->newNode($folder->getPath(), $folder->getName(), self::TYPE_CONTENT_FOLDER);
        $node->addNode(self::NODE_NAME_PROPERTIES, self::TYPE_UNSTRUCTURED);
        $this->setFolderProperties($node, $folder);

        return $this->generateStructure($node);
    }

    public function upload(NewStructureInterface $file): Structure
    {
        $name = $this->trimEnd($file->getName(), '.' . $file->getExtension()) . '.' . $file->getExtension();
        $path = rtrim($file->getPath(), '/') . '/';
        $fullPath = $path . $name;
        if ($this->exists($fullPath)) {
            throw new AssetExistsException($fullPath);
        }

        if (!$file->getResource()) {
            return $this->folder($file);
        }

        $node = $this->newNode($file->getPath(), $name, self::TYPE_CONTENT_FILE);
        $node->addNode(self::NODE_NAME_CONTENT, self::TYPE_CONTENT_RESOURCE);
        $node->addNode(self::NODE_NAME_PROPERTIES, self::TYPE_UNSTRUCTURED);
        $this->setFileProperties($node, $file);

        return $this->generateStructure($node);
    }

    protected function trimEnd(string $target, string $toRemove): string
    {
        $len = strlen($toRemove);
        if (0 === strcmp(substr($target, -$len, $len), $toRemove)) {
            return substr($target, 0, -$len);
        }

        return $target;
    }

    public function exists(string $path): bool
    {
        return $this->session->nodeExists(rtrim($path, '/'));
    }

    public function remove(Structure $asset): bool
    {
        if ($this->toRemove->offsetExists($asset)) {
            return false;
        }

        $this->toRemove->offsetSet($asset, true);

        return true;
    }

    public function dontRemove(Structure $asset): bool
    {
        if (!$this->toRemove->offsetExists($asset)) {
            return false;
        }

        $this->toRemove->offsetUnset($asset);

        return true;
    }

    public function move(Structure $asset, string $path): Structure
    {
        if ($this->exists($path)) {
            throw new AssetExistsException($path);
        }

        $this->session->move($asset->path, $path);

        $name = explode(DIRECTORY_SEPARATOR, $path);
        $name = $name[count($name) - 1];
        $name = explode('.', $name);
        if (count($name) > 1) {
            $name = array_slice($name, 0, -1);
        }

        return new Structure(
            $path,
            implode('.', $name),
            $asset->type,
            $asset->extension,
            $asset->mimeType,
            $asset->weight,
        );
    }

    public function reupload(Structure $asset, NewStructureInterface $file): Structure
    {
        if (!$file->getResource()) {
            throw new \InvalidArgumentException('Cannot reupload directory', 422);
        }

        try {
            $node = $this->getNode($asset->path);
        } catch (AssetNotFoundException) {
            $node = $this->regenerateNode($asset->path);
        }

        try {
            $node->getNode(self::NODE_NAME_CONTENT);
        } catch (PathNotFoundException) {
            $node->addNode(self::NODE_NAME_CONTENT, self::TYPE_CONTENT_RESOURCE);
        }

        try {
            $node->getNode(self::NODE_NAME_PROPERTIES);
        } catch (PathNotFoundException) {
            $node->addNode(self::NODE_NAME_PROPERTIES, self::TYPE_UNSTRUCTURED);
        }

        if ($file->getExtension() != $asset->extension) {
            $name = explode('.', $asset->name);
            if (count($name) > 1) {
                $name = array_slice($name, 0, -1);
            }
            $this->move(
                $asset,
                rtrim(dirname($asset->path), '/') . '/' . implode('.', $name) . '.' . $file->getExtension(),
            );
        }

        $this->setFileProperties($node, $file);

        return $this->generateStructure($node);
    }

    /**
     * Queues clone to be done on flush as PHPCR dispatches changes immediately with their "copy" method.
     *
     * @throws AssetExistsException
     */
    public function clone(Structure $asset, string $path): Structure
    {
        if ($this->exists($path)) {
            throw new AssetExistsException($path);
        }

        $this->toClone[] = [$asset->path, $path];

        return new Structure(
            $path,
            $asset->name,
            $asset->type,
            $asset->extension,
            $asset->mimeType,
            $asset->weight,
        );
    }

    public function flush(): void
    {
        $workspace = $this->session->getWorkspace();
        foreach ($this->toClone as [$from, $to]) {
            $workspace->copy($from, $to);
        }
        $this->toClone = [];

        foreach ($this->toRemove->getIterator() as $asset => $value) {
            try {
                $this->getNode($asset->path)->remove();
            } catch (AssetNotFoundException) {
                // Do nothing - file might not exist but entity in database does (desynchronization via user actions)
            }
        }

        $this->session->save();
    }

    public function clear(): void
    {
        // Clear is not present in the interface but is implemented on class, and we need it
        $this->session->clear(); // @phpstan-ignore-line
    }

    protected function setFolderProperties(NodeInterface $node, NewStructureInterface $folder): void
    {
        $properties = $node->getNode(self::NODE_NAME_PROPERTIES);

        $properties->setProperty(self::PROPERTY_SIZE, $folder->getSize());
    }

    protected function setFileProperties(NodeInterface $node, NewStructureInterface $file): void
    {
        $content = $node->getNode(self::NODE_NAME_CONTENT);
        $properties = $node->getNode(self::NODE_NAME_PROPERTIES);

        $content->setProperty(self::PROPERTY_FILE, $file->getResource(), PropertyType::BINARY);
        $content->setProperty(self::PROPERTY_MIME_TYPE, $file->getMimeType());

        $properties->setProperty(self::PROPERTY_SIZE, $file->getSize());
    }

    protected function generateStructure(NodeInterface $node): Structure
    {
        $name = $node->getName();
        $extension = '';
        if ('.' !== $name[0]) {
            $filename = explode('.', $node->getName());
            $name = $filename[0];
            $extension = $filename[1] ?? null;
        }

        if ($node->hasNode(self::NODE_NAME_CONTENT)) {
            $mimeType = $node->getNode(self::NODE_NAME_CONTENT)
                ->getProperty(self::PROPERTY_MIME_TYPE)
                ->getValue()
            ;
        }
        if ($node->hasNode(self::NODE_NAME_PROPERTIES)) {
            $size = $node->getNode(self::NODE_NAME_PROPERTIES)
                ->getProperty(self::PROPERTY_SIZE)
                ->getValue()
            ;
        }

        $structure = new Structure(
            $node->getPath(),
            $name,
            $node->isNodeType(self::TYPE_CONTENT_FILE) ? StructureTypeEnum::File : StructureTypeEnum::Folder,
            $extension ?: null,
            $mimeType ?? null,
            $size ?? null,
        );

        if ($node->hasNode(self::NODE_NAME_CONTENT)) {
            $structure->setResource(
                $node->getNode(self::NODE_NAME_CONTENT)
                    ->getProperty(self::PROPERTY_FILE)
                    ->getValue()
            );
        }

        return $structure;
    }

    protected function newNode(string $path, string $name, string $type): NodeInterface
    {
        try {
            $parent = $this->session->getNode('/' === $path ? $path : rtrim($path, '/'));
        } catch (PathNotFoundException) {
            throw new AssetNotFoundException($path);
        }

        try {
            return $parent->addNode($name, $type);
        } catch (ItemExistsException) {
            throw new AssetExistsException(rtrim($path, '/') . '/' . $name);
        }
    }

    //    protected function updateNodeProperties(NodeInterface $node, UploadedFileInterface $file, Asset $asset): void
    //    {
    //        $this->updateEntity(
    //            $asset,
    //            $node->getPath(),
    //            $node->getName(),
    //            $file->getSize(),
    //            $file->getExtension(),
    //            $file->getMimeType(),
    //        );
    //    }

    protected function getNode(string $path): NodeInterface
    {
        try {
            return $this->session->getNode($path);
        } catch (PathNotFoundException) {
            throw new AssetNotFoundException($path);
        }
    }

    //    protected function generateJackrabbitAssetProxy(AssetInterface $asset, \Closure $nodeDecorator): JackrabbitAsset
    //    {
    //        return new JackrabbitAsset(
    //            $asset,
    //            $nodeDecorator,
    //            function (JackrabbitAsset $asset): ?JackrabbitAsset {
    //                if ('/' === $asset->getPath()) {
    //                    return null;
    //                }
    //
    //                return $this->getByPath(dirname($asset->getPath()));
    //            },
    //            function (JackrabbitAsset $asset, ?string $nameMatch = null, ?string $typeMatch = null): array|\Iterator {
    //                foreach ($this->getNode($asset->getPath())->getNodes($nameMatch, $typeMatch) as $item) {
    //                    yield $this->getByPath($item->getPath());
    //                }
    //            },
    //        );
    //    }

    protected function regenerateNode(string $path): NodeInterface
    {
        try {
            $parent = $this->getNode(dirname($path));
        } catch (AssetNotFoundException) {
            throw new MissingParentException($path);
        }

        return $parent->addNode(basename($path), self::TYPE_CONTENT_FILE);
    }
}
