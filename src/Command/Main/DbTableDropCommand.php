<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbTableDropCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:table:drop';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Drop exists table');
        $this->addArgument('table', InputArgument::REQUIRED, 'Table name');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $tableName = $input->getArgument('table');
            $connection = Application::getInstance()->getConnection($input->getOption('connection') ?? '');
            $connection->dropTable($tableName);
            $output->writeln(sprintf('<info>Table %s was dropped</info>', $tableName));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
