<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbDatabaseShowTableCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'db:database:show:table';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Show database tables');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection to use');
        $this->addOption('database', 'd', InputOption::VALUE_OPTIONAL, 'Database name');
        $this->setAliases(['db:database:show:table']);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $connection = Application::getConnection($input->getOption('connection') ?? 'default');
            $database = $input->getOption('database') ?? $connection->getDatabase();
            $sqlHelper = $connection->getSqlHelper();
            $sqlString = sprintf(sprintf('SHOW TABLES FROM `%s`;', $sqlHelper->forSql($database)));
            $rowsCollection = $connection->query($sqlString)->fetchAll();
            if ($rowsCollection === []) {
                $output->writeln('<error>No tables found</error>');

                return self::SUCCESS;
            }

            /** @var array[] $rowsCollection */
            foreach ($rowsCollection as &$row) {
                foreach ($row as &$r) {
                    if ($r === null) { $r = '(null)'; continue; }
                    if (is_bool($r)) { $r = $r ? '(true)' : '(false)'; }
                }
            }

            // show output result
            $table = new Table($output);
            $table->setHeaders(array_keys($rowsCollection[0]));
            $table->setRows($rowsCollection);
            $table->render();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
