<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\UserTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserActivateCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'user:activate';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Activate user');
        $this->addArgument('user', InputArgument::REQUIRED, 'ID, login or email');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $id = $input->getArgument('user');
            if (is_numeric($id)) {
                $filter = ['=ID' => (int)$id];
            } else {
                $filter = ['LOGIC' => 'OR', ['LOGIN' => $id], ['EMAIL' => $id]];
            }

            $user = UserTable::getList([
                'filter' => $filter,
                'limit' => 1,
            ])->fetch();
            if (!is_array($user)) {
                throw new \Exception(sprintf('User "%s" not found', $id));
            }

            $cUser = new \CUser();
            $res = $cUser->Update((int)$user['ID'], ['ACTIVE' => 'Y']);
            if (!$res) {
                throw new \Exception(sprintf('Failed activate "%s" user: %s', $id, $cUser->LAST_ERROR));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
