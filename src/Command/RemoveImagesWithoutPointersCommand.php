<?php

declare(strict_types=1);

namespace Dullahan\Command;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Asset;
use Dullahan\Service\TraceService;
use Dullahan\Service\Util\BinUtilService;
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
        $q = $this->em->createQuery('SELECT a FROM ' . Asset::class . ' a WHERE a.pointers IS EMPTY');
        $this->log('Starting command to remove all images without any reference (batch size: ' . $batchSize . ')');
        $this->increaseIndent();
        /** @var Asset $asset */
        foreach ($q->toIterable() as $asset) {
            $this->log('Asset: ' . $asset->getName() . ' [' . $asset->getId() . ']');
            $this->em->remove($asset);

            ++$i;
            if (($i % $batchSize) === 0) {
                if ($dry) {
                    $this->log('=== Due to dry run batch data was not send ===');
                } else {
                    $this->log('=== Sent batched data ===');
                    $this->em->flush();
                }
                $this->em->clear();
            }
        }
        $this->decreaseIndent();
        !$dry ? $this->em->flush() : $this->log('=== Due to dry run batch data was not send ===');
        $this->log('Command finished successfully');
    }
}
