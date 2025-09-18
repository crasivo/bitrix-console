<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbDatabaseSizeCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:database:size';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Get database size');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name');
        $this->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name');
        $this->addOption('quiet', 'q', InputOption::VALUE_NONE);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $connection = Application::getConnection($input->getOption('connection') ?? '');
            $database = $input->getOption('database') ?? $connection->getDatabase();
            $result = $connection->query(sprintf('SELECT table_schema db_name, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) db_size FROM information_schema.tables WHERE table_schema = "bitrix_development" GROUP BY table_schema;', $database))->fetch();
            if ($input->getOption('quiet')) {
                $output->writeln($result['db_size']);
            } else {
                $output->writeln(sprintf('<info>Database size: %s Mb</info>', $result['db_size']));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
