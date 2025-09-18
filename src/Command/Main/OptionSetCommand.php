<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Config\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptionSetCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'option:set';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Set module option');
        $this->addArgument('module', InputArgument::REQUIRED, 'Module ID');
        $this->addArgument('option', InputArgument::REQUIRED, 'Option name');
        $this->addArgument('value', InputArgument::REQUIRED, 'Option value');
        $this->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Site ID', 's1');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            Option::set(
                $input->getOption('module'),
                $input->getArgument('option'),
                $input->getArgument('value'),
                $input->getOption('site') ?? 's1'
            );

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
