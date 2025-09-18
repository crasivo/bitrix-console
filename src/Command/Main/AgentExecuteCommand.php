<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AgentExecuteCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'agent:execute';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Execute agents (without backup/sender/etc)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            @define('BX_CRONTAB', true);
            \CAgent::ExecuteAgents();
            $output->writeln('OK'); // for healthcheck

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
