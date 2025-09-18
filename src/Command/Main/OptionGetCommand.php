<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Config\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptionGetCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'option:get';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Get module option');
        $this->addArgument('module',  InputArgument::REQUIRED, 'Module ID');
        $this->addArgument('option', InputArgument::REQUIRED, 'Option name');
        $this->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Site ID', 's1');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $value = Option::get(
                $input->getArgument('module'),
                $input->getArgument('option'),
                '(null)',
                $input->getOption('site')
            );

            $output->writeln($value);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
