<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbTableSizeCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:table:size';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Get the size of a table');
        $this->addArgument('table', InputArgument::REQUIRED, 'Table name');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name');
        $this->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name');
        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format', 'kb');
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
            $table = $input->getArgument('table');

            // build sql
            $sql = sprintf('SELECT (data_length + index_length) AS size_byte FROM information_schema.TABLES WHERE table_schema = "%s"', $database);
            if (is_string($table) && $table !== '') {
                $sql .= sprintf(' AND table_name = "%s"', $table);
            }

            // show output result
            $result = $connection->query($sql . ';')->fetch();
            switch ($input->getOption('format')) {
                case 'gb':
                    $format = 'gb';
                    $size = round((int)$result['size_byte'] / 1073741824, 2);
                    break;
                case 'mb':
                    $format = 'mb';
                    $size = round((int)$result['size_byte'] / 1048576, 2);
                    break;
                default:
                    $format = 'kb';
                    $size = round((int)$result['size_byte'] / 1024, 2);
                    break;
            }

            if ($input->getOption('quiet')) {
                $output->writeln($size);
            } else {
                $output->writeln(sprintf('<info>Table size: %s %s</info>', $size, $format));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
