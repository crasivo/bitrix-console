<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleDeleteCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'module:delete';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Delete module');
        $this->addArgument('module', InputArgument::REQUIRED, 'Module ID');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $moduleId = $input->getArgument('module');
            if (ModuleManager::isModuleInstalled($moduleId)) {
                throw new \RuntimeException(sprintf('Module %s has installed status. Please uninstall manually.', $moduleId));
            }

            ModuleManager::delete($moduleId);

            $output->writeln(sprintf('Module %s has been deleted.', $moduleId));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
