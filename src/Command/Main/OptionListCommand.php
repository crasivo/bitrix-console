<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Config\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptionListCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'option:list';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Get option list');
        $this->addArgument('module', InputArgument::REQUIRED, 'Module ID');
        $this->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Site ID', 's1');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $table = new Table($output);
            $table->setHeaders(['NAME', 'VALUE']);
            $options = Option::getForModule(
                $input->getArgument('module'),
                $input->getOption('site')
            );
            foreach ($options as $k => $v) {
                $table->addRow([$k, $v]);
            }

            $table->render();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
