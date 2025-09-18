<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleRegisterCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'module:register';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Register module');
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
                throw new \Exception(sprintf('Module %s already installed (registered)', $moduleId));
            }

            ModuleManager::registerModule($moduleId);
            $output->writeln(sprintf('Module %s has been registered.', $moduleId));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
