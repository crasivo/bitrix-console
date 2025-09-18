<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbDatabaseDropCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:database:drop';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Drop existing database');
        $this->addArgument('database', InputArgument::REQUIRED, 'Database name');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection to use');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $database = $input->getArgument('database');
            $connection = Application::getConnection($input->getOption('connection') ?? '');
            $connection->queryExecute(sprintf('DROP DATABASE IF EXISTS %s;', $database));
            $output->writeln(sprintf('<info>Database %s has been dropped</info>', $database));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
