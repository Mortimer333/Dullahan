<?php

declare(strict_types=1);

namespace Dullahan\Main\Command;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Main\Service\TraceService;
use Dullahan\Main\Service\Util\BinUtilService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dullahan:image:remove:orphaned',
    description: 'Remove images without references (orphaned)',
)]
class RemoveImagesWithoutPointersCommand extends BaseCommandAbstract
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected LoggerInterface $logger,
        protected BinUtilService $binUtilService,
        protected TraceService $traceService,
        protected AssetServiceInterface $assetService,
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
        $batchSize = 200;
        $i = 1;
        $q = $this->em->createQuery('SELECT a FROM ' . Asset::class . ' a WHERE a.pointers IS EMPTY AND a.mimeType IS NOT NULL');
        $this->log('Starting command to remove all images without any reference (batch size: ' . $batchSize . ')');
        $this->increaseIndent();
        /** @var Asset $asset */
        foreach ($q->toIterable() as $asset) {
            $this->log('Asset: ' . $asset->getName() . ' [' . $asset->getId() . ']');
            $this->assetService->remove($this->assetService->get($asset->getId()));

            ++$i;
            if (($i % $batchSize) === 0) {
                if ($dry) {
                    $this->log('=== Due to dry run batch data was not send ===');
                } else {
                    $this->log('=== Sent batched data ===');
                    $this->assetService->flush();
                }
                $this->assetService->clear();
            }
        }
        $this->decreaseIndent();
        !$dry ? $this->assetService->flush() : $this->log('=== Due to dry run batch data was not send ===');
        $this->log('Command finished successfully');
    }
}
