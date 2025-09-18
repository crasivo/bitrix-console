<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\SiteTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteActivateCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'site:activate';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Activate site');
        $this->addArgument('site', InputArgument::REQUIRED, 'Site server name or ID');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $id = $input->getArgument('site');
            $site = SiteTable::getList([
                'filter' => [
                    'LOGIC' => 'OR',
                    ['LID' => $id],
                    ['SERVER_NAME' => $id],
                ],
                'limit' => 1,
            ])->fetch();
            if (!is_array($site)) {
                throw new \Exception(sprintf('Site "%s" not found', $id));
            }

            $result = SiteTable::update($site['LID'], ['ACTIVE' => 'Y']);
            if (!$result->isSuccess()) {
                throw new \Exception(sprintf('Failed activate "%s" site', $id));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
