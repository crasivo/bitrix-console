<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\UserTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserListCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'user:list';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('List users');
        $this->addOption('active', null, InputOption::VALUE_NONE, 'List only active');
        $this->addOption('blocked', null, InputOption::VALUE_NONE, 'List only blocked');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of rows');
        $this->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset number of rows');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $limit = $input->getOption('limit');
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 20;
            }
            $offset = $input->getOption('offset');
            if (!is_numeric($offset) || $offset < 0) {
                $offset = 0;
            }

            // build select fields (columns)
            $select = [
                'ID' => 'ID',
                'LOGIN' => 'LOGIN',
                'EMAIL' => 'EMAIL',
                'PHONE_NUMBER' => 'PHONE_AUTH.PHONE_NUMBER',
                'NAME' => 'NAME',
                'LAST_NAME' => 'LAST_NAME',
                'DATE_REGISTER' => 'DATE_REGISTER',
                'ACTIVE' => 'ACTIVE',
                'BLOCKED' => 'BLOCKED',
            ];
            if (!class_exists(UserPhoneAuthTable::class)) {
                unset($select['PHONE_NUMBER']);
            }

            // build filter
            $filter = [];
            if ($input->getOption('active')) {
                $filter['=ACTIVE'] = 'Y';
            }
            if ($input->getOption('blocked')) {
                $filter['=BLOCKED'] = 'Y';
            }

            $table = new Table($output);
            $table->setHeaders(array_keys($select));
            $queryResult = UserTable::getList([
                'order' => ['ID' => 'ASC'],
                'filter' => $filter,
                'select' => $select,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            while ($row = $queryResult->fetch()) {
                $row = array_map(function ($r) {
                    if ($r === null) { return '(null)'; }
                    if ($r === '') { return '(empty)'; }
                    if ($r instanceof DateTime) { return $r->format(\DATE_ATOM); }
                    return (string)$r;
                }, $row);

                $table->addRow($row);
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
