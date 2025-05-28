<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\List\ListAssetEvent;
use Dullahan\Main\Service\Util\HttpUtilService;
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
            (int) $context->getProperty(self::LIMIT, 100),
            (int) $context->getProperty(self::OFFSET, 0),
            $sort,
            $filter,
            $join,
            $group,
        );

        if ($context->hasProperty(self::COUNT)) {
            $event->setTotal($this->assetPersistenceManager->count($sort, $filter, $join, $group));
            HttpUtilService::setTotal($event->getTotal());
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
