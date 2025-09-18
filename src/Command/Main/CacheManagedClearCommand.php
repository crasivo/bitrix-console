<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheManagedClearCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'cache:managed:clear';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Clear managed cache');
        $this->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Directory');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $directory = $input->getOption('dir');
            $managedCache = Application::getInstance()->getManagedCache();
            if (is_string($directory) && $directory !== '') {
                $managedCache->cleanDir($directory);
            } else {
                $managedCache->cleanAll();
            }

            $output->writeln('OK');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
