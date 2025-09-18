<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Web\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationSetCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'configuration:set';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Set settings value');
        $this->addArgument('key', InputArgument::REQUIRED, 'Configuration key');
        $this->addOption('value', 'v', InputOption::VALUE_REQUIRED, 'Value');
        $this->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Module ID');
        $this->setAliases(['config:set']);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!in_array($_ENV['APP_DEBUG'] ?? false, [true, 'true'])) {
                throw new \Exception('App debug mode must be set true');
            }

            $value = $input->getOption('value');
            if (0 === strpos($value, '{') || 0 === strpos($value, '[')) {
                $value = Json::decode($value);
            }

            $configuration = Configuration::getInstance($input->getOption('module') ?? '');
            $configuration->add($input->getArgument('key'), $value);
            $configuration->saveConfiguration();

            $output->writeln('<info>Configuration was successfully saved</info>');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
