<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\ORM\Data\DataManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrmEntityDeleteCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'orm:entity:delete';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Delete ORM entity');
        $this->addArgument('entity', InputArgument::REQUIRED, 'Entity class');
        $this->addOption('primary', 'p', InputOption::VALUE_REQUIRED, 'Primary key');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \class-string<DataManager> $ormClass */
            $entityClass = $input->getArgument('entity');
            if (!class_exists($entityClass) || !is_subclass_of($entityClass, DataManager::class)) {
                throw new \Exception(sprintf('Class "%s" does not exist or not ORM.', $entityClass));
            }

            $result = $entityClass::delete($input->getOption('primary'));
            if (!$result->isSuccess()) {
                throw new \Exception('Failed delete ORM entity: ' . implode(', ', $result->getErrorMessages()));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
