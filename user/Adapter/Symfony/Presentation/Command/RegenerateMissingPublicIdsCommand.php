<?php

namespace Dullahan\User\Adapter\Symfony\Presentation\Command;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Command\BaseCommandAbstract;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Entity\UserData;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dullahan:user:regenerate:public-id',
    description: 'Regenerate App Secret',
)]
class RegenerateMissingPublicIdsCommand extends BaseCommandAbstract
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected LoggerInterface $logger,
        protected BinUtilService $binUtilService,
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
        $q = $this->em->createQuery(
            'select u from ' . UserData::class . ' u '
            . 'WHERE LENGTH(u.publicId) = 0 OR u.publicId IS NULL'
        );
        $this->log('Starting command to set public IDs (batch size: ' . $batchSize . ')');
        $this->increaseIndent();
        /** @var UserData $userData */
        foreach ($q->toIterable() as $userData) {
            /** @var ?User $user */
            $user = $userData->getUser();
            if (!$user) {
                $this->log('User: ' . $userData->getName() . ' was deleted, skipping');
                continue;
            }
            $this->log('User: ' . $userData->getName() . ' [' . $user->getId() . ']');
            $userData->setPublicId($this->binUtilService->generateUniqueToken((string) $user->getId()));
            $this->log('Public ID: ' . $userData->getPublicId());

            $this->em->persist($userData);

            ++$i;
            if (($i % $batchSize) === 0) {
                $this->log('=== Sent batched data ===');
                !$dry ? $this->em->flush() : $this->log('=== Due to dry run batch data was not send ===');
                $this->em->clear();
            }
        }
        $this->decreaseIndent();
        !$dry ? $this->em->flush() : $this->log('=== Due to dry run batch data was not send ===');
        $this->log('Command finished successfully');
    }
}
