<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\List\ListAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class ListListener
{
    public const COUNT = 'list_count_param';
    public const SORT = 'sort';
    public const FILTER = 'filter';
    public const JOIN = 'join';
    public const GROUP = 'group';
    public const LIMIT = 'limit';
    public const OFFSET = 'offset';

    public function __construct(
        protected AssetPersistenceManagerInterface $assetPersistenceManager,
        protected AssetFileManagerInterface $assetFileManager,
    ) {
    }

    #[AsEventListener(event: ListAssetEvent::class)]
    public function listAssets(ListAssetEvent $event): void
    {
        $context = $event->getContext();
        $sort = $context->getProperty(self::SORT);
        $filter = $context->getProperty(self::FILTER);
        $join = $context->getProperty(self::JOIN);
        $group = $context->getProperty(self::GROUP);

        $assets = $this->assetPersistenceManager->list(
            $context->getProperty(self::LIMIT, 100),
            $context->getProperty(self::OFFSET, 0),
            $sort,
            $filter,
            $join,
            $group,
        );

        if ($context->hasProperty(self::COUNT)) {
            $event->setTotal($this->assetPersistenceManager->count($sort, $filter, $join, $group));
        }

        $found = [];
        $sharedContext = new Context();
        foreach ($assets as $asset) {
            $found[] = new Asset(
                $this->assetFileManager->get($asset->getFullPath()),
                $asset,
                $sharedContext,
            );
        }

        $event->setAssets($found);
    }
}
