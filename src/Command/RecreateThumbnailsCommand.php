<?php

declare(strict_types=1);

namespace Dullahan\Command;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\AssetManager\EntityBasedAssetManager;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Entity\Asset;
use Dullahan\Service\CacheService;
use Dullahan\Service\TraceService;
use Dullahan\Service\Util\BinUtilService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dullahan:thumbnail:regenerate',
    description: 'Remove images without references (orphaned)',
)]
class RecreateThumbnailsCommand extends BaseCommandAbstract
{
    public function __construct(
        protected EntityManagerInterface  $em,
        protected LoggerInterface         $logger,
        protected BinUtilService          $binUtilService,
        protected TraceService            $traceService,
        protected EntityBasedAssetManager $assetService,
        protected CacheService            $cacheService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'Do a dry-run. No changes will be made.'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function command(InputInterface $input, OutputInterface $output): void
    {
        $dry = $input->getOption('dry-run');
        $this->removeThumbnails($dry);
        $this->recreateThumbnails($dry);
        !$dry ? $this->em->flush() : $this->log('=== Due to dry run batch data was not send ===');
        $this->log('Command finished successfully');
    }

    protected function recreateThumbnails(bool $dry): void
    {
        $this->log('Old thumbnails deleted, create new one');
        $q = $this->em->createQuery('SELECT a FROM ' . Asset::class . ' a');
        /** @var Asset $asset */
        foreach ($q->toIterable() as $asset) {
            $this->increaseIndent();
            $this->log('Asset ' . $asset->getName() . ' [' . $asset->getId() . ']');
            foreach ($asset->getPointers() as $pointer) {
                $entity = $pointer->getEntity();
                if (!$entity instanceof AssetAwareInterface || !$pointer->getEntityColumn()) {
                    continue;
                }
                $this->log(
                    'Save entity to recreate thumbnails ' . $entity::class . ' [' . $entity->getId()
                    . '] ' . $pointer->getEntityColumn()
                );
                $this->assetService->createThumbnails($entity, $pointer->getEntityColumn());

                if (!$dry) {
                    $this->log('Create thumbnails...');
                    $this->cacheService->deleteEntityCache($entity, true);
                    $this->cacheService->deleteEntityCache($entity, false);
                    $this->em->flush();
                    $this->em->clear();
                }
            }
            $this->decreaseIndent();
        }
    }

    protected function removeThumbnails(bool $dry): void
    {
        $q = $this->em->createQuery('SELECT a FROM ' . Asset::class . ' a');
        $this->log('Starting command to remove old thumbnails and create new one');
        $this->increaseIndent();
        /** @var Asset $asset */
        foreach ($q->toIterable() as $asset) {
            $this->log('Asset ' . $asset->getName() . ' [' . $asset->getId() . ']');
            $this->increaseIndent();
            foreach ($asset->getThumbnails() as $thumbnail) {
                foreach ($thumbnail->getAssetPointers() as $assetPointer) {
                    $this->em->remove($assetPointer);
                }
                $this->em->flush();
                $this->log('Remove thumbnail ' . $thumbnail->getName() . ' [' . $thumbnail->getId() . ']');
                $this->em->remove($thumbnail);
            }
            $this->decreaseIndent();
            if (!$dry) {
                $this->log('Push changes...');
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->decreaseIndent();
    }
}
