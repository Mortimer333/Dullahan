<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Exception\AssetNotClonedException;
use Dullahan\Asset\Domain\Exception\AssetNotCreatedException;
use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\Exception\AssetNotMovedException;
use Dullahan\Asset\Domain\Exception\AssetNotReplacedException;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Presentation\Event\Transport\Clear\ClearAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Clone\CloneAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Clone\PostCloneAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Clone\PreCloneAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Create\CreateAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Create\PostCreateAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Create\PreCreateAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Exist\AssetExistEvent;
use Dullahan\Asset\Presentation\Event\Transport\Flush\FlushAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\List\ListAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Move\MoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Move\PostMoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Move\PreMoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Remove\PostRemoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Remove\PreRemoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Remove\RemoveAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Replace\PostReplaceAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Replace\PreReplaceAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Replace\ReplaceAssetEvent;
use Dullahan\Asset\Presentation\Event\Transport\Retrieve\RetrieveByIdEvent;
use Dullahan\Asset\Presentation\Event\Transport\Retrieve\RetrieveByPathEvent;
use Dullahan\Asset\Presentation\Event\Transport\Validate\AsseNameEvent;
use Dullahan\Main\Contract\EventDispatcherInterface;

class AssetEventFacadeService implements AssetServiceInterface
{
    public function __construct(
        readonly protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function exists(string $path, ?Context $context = null): bool
    {
        $context ??= new Context();
        $event = new AssetExistEvent($path, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->exists();
    }

    public function validName(string $name, ?Context $context = null): bool
    {
        $context ??= new Context();
        $event = new AsseNameEvent($name, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->isValid();
    }

    public function list(?Context $context = null): array
    {
        $context ??= new Context();

        $event = new ListAssetEvent($context);
        $this->eventDispatcher->dispatch($event);

        return $event->getAssets();
    }

    public function get(mixed $id, ?Context $context = null): Asset
    {
        $context ??= new Context();

        $event = new RetrieveByIdEvent($id, $context);
        $this->eventDispatcher->dispatch($event);

        if (!$event->getEntity() || !$event->getStructure()) {
            throw new AssetNotFoundException('Asset not found');
        }

        return new Asset(
            $event->getStructure(),
            $event->getEntity(),
            $event->getContext(),
        );
    }

    public function getByPath(string $path, ?Context $context = null): Asset
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $context ??= new Context();

        $event = new RetrieveByPathEvent($path, $context);
        $this->eventDispatcher->dispatch($event);

        if (!$event->getEntity() || !$event->getStructure()) {
            throw new AssetNotFoundException('Asset not found');
        }

        return new Asset(
            $event->getStructure(),
            $event->getEntity(),
            $event->getContext(),
        );
    }

    public function create(NewStructureInterface $file, ?Context $context = null): Asset
    {
        $context ??= new Context();

        $before = new PreCreateAssetEvent($file, $context);
        $this->eventDispatcher->dispatch($before);

        $create = new CreateAssetEvent($before->getFile(), $before->getContext());
        $this->eventDispatcher->dispatch($create);

        $structure = $create->getCreatedStructure();
        $entity = $create->getEntity();
        if (!$structure || !$entity) {
            throw new AssetNotCreatedException('Asset was not created', 500);
        }

        $after = new PostCreateAssetEvent($structure, $entity, $create->getContext());
        $this->eventDispatcher->dispatch($after);

        return new Asset(
            $after->getStructure(),
            $after->getEntity(),
            $after->getContext(),
        );
    }

    public function move(Asset $asset, string $path, ?Context $context = null): Asset
    {
        $context ??= new Context();

        $before = new PreMoveAssetEvent($asset, $path, $context);
        $this->eventDispatcher->dispatch($before);

        $event = new MoveAssetEvent($before->getAsset(), $before->getPath(), $before->getContext());
        $this->eventDispatcher->dispatch($event);
        if ($event->getAsset()->structure->path !== $path) {
            throw new AssetNotMovedException('Asset was not moved', 500);
        }

        $after = new PostMoveAssetEvent($event->getAsset(), $event->getPath(), $event->getContext());
        $this->eventDispatcher->dispatch($after);

        return $after->getAsset();
    }

    public function replace(Asset $asset, NewStructureInterface $file, ?Context $context = null): Asset
    {
        $context ??= new Context();

        $before = new PreReplaceAssetEvent($asset, $file, $context);
        $this->eventDispatcher->dispatch($before);

        $event = new ReplaceAssetEvent($before->getAsset(), $before->getFile(), $before->getContext());
        $this->eventDispatcher->dispatch($event);
        if ($asset === $event->getAsset()) {
            throw new AssetNotReplacedException('Asset was not replaced', 500);
        }

        $after = new PostReplaceAssetEvent($event->getAsset(), $event->getFile(), $event->getContext());
        $this->eventDispatcher->dispatch($after);

        return $after->getAsset();
    }

    public function remove(Asset $asset, ?Context $context = null): void
    {
        $context ??= new Context();

        $before = new PreRemoveAssetEvent($asset, $context);
        $this->eventDispatcher->dispatch($before);

        $remove = new RemoveAssetEvent($before->getAsset(), $before->getContext());
        $this->eventDispatcher->dispatch($remove);

        $remove = new PostRemoveAssetEvent($remove->getAsset(), $remove->getContext());
        $this->eventDispatcher->dispatch($remove);
    }

    public function clone(Asset $asset, string $path, ?Context $context = null): Asset
    {
        $context ??= new Context();

        $before = new PreCloneAssetEvent($asset, $path, $context);
        $this->eventDispatcher->dispatch($before);

        $event = new CloneAssetEvent($before->getAsset(), $before->getPath(), $before->getContext());
        $this->eventDispatcher->dispatch($event);
        if ($asset === $event->getAsset()) {
            throw new AssetNotClonedException('Asset was not cloned', 500);
        }

        $after = new PostCloneAssetEvent($event->getAsset(), $event->getPath(), $event->getContext());
        $this->eventDispatcher->dispatch($after);

        return $after->getAsset();
    }

    public function flush(?Context $context = null): void
    {
        $context ??= new Context();
        $this->eventDispatcher->dispatch(new FlushAssetEvent($context));
    }

    public function clear(?Context $context = null): void
    {
        $context ??= new Context();
        $this->eventDispatcher->dispatch(new ClearAssetEvent($context));
    }
}
