<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbTableListCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:table:list';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('List exists tables');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name');
        $this->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $connection = Application::getConnection($input->getOption('connection') ?? '');
            $database = $input->getOption('database') ?? $connection->getDatabase();
            $queryResult = $connection->query(sprintf('SHOW TABLE STATUS FROM %s;', $database));

            // build output result
            $tableCollection = [];
            while ($row = $queryResult->fetch()) {
                $tableCollection[] = [
                    $row['Name'],
                    $row['Engine'],
                    $row['Collation'],
                    $row['Rows'],
                    round(((int)$row['Data_length'] + (int)$row['Index_length']) / 1024, 2),
                ];
            }

            // render output result
            $outputTable = new Table($output);
            $outputTable->setHeaders(['Table name', 'Engine', 'Collation', 'Rows count', 'Size (kb)']);
            $outputTable->setRows($tableCollection);
            $outputTable->render();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
