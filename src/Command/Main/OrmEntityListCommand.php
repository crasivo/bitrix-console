<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrmEntityListCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'orm:entity:list';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('List rows via ORM class');
        $this->addArgument('entity', InputArgument::REQUIRED, 'ORM class');
        $this->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Filter fields');
        $this->addOption('select', 's', InputOption::VALUE_OPTIONAL, 'Select fields', '*');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of rows');
        $this->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset number of rows');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var \class-string<DataManager> $ormClass */
            $ormClass = $input->getArgument('entity');
            if (!class_exists($ormClass) || !is_subclass_of($ormClass, DataManager::class)) {
                throw new \Exception(sprintf('Class "%s" does not exist or not ORM.', $ormClass));
            }
            $limit = $input->getOption('limit');
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 20;
            }
            $offset = $input->getOption('offset');
            if (!is_numeric($offset) || $offset < 0) {
                $offset = 0;
            }

            // build select fields
            $existsFields = $this->getOrmExistsColumns($ormClass);
            $selectFields = $this->buildOrmSelectFields($input->getOption('select'), $existsFields);

            // build filter fields
            $filter = $input->getOption('filter') ?? null;
            if (is_string($filter) && $filter !== '') {
                $filter = $this->buildOrmFilterFields($filter);
            }

            $table = new Table($output);
            $table->setHeaders(array_keys($selectFields));
            $queryResult = $ormClass::getList([
                'filter' => $filter ?? [],
                'select' => $selectFields,
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

    /**
     * @param string $inputValue
     * @return array|null
     * @throws \Throwable
     */
    protected function buildOrmFilterFields(string $inputValue)
    {
        if (str_starts_with($inputValue, '{') || str_starts_with($inputValue, '[')) {
            return Json::decode($inputValue);
        }

        $inputValue = explode('=', $inputValue);
        if (count($inputValue) === 2) {
            return [$inputValue[0] => $inputValue[1]];
        }

        return null;
    }

    /**
     * @param string $inputValue
     * @param array $existsFields
     * @return array
     * @throws \Throwable
     */
    protected function buildOrmSelectFields(string $inputValue, array $existsFields)
    {
        if ($inputValue === '' || $inputValue === '*') {
            return $existsFields;
        }
        if (str_starts_with($inputValue, '[')) {
            $selectFields = [];
            foreach (Json::decode($inputValue) as $field) {
                if (isset($existsFields[$field])) {
                    $selectFields[$field] = $existsFields[$field];
                }
            }

            return $selectFields;
        }
        if (str_starts_with($inputValue, '{')) {
            $selectFields = [];
            foreach (Json::decode($inputValue) as $k => $field) {
                if (isset($existsFields[$k])) {
                    $selectFields[$k] = $existsFields[$field];
                }
            }

            return $selectFields;
        }
        if (str_contains($inputValue, ',')) {
            $selectFields = [];
            foreach (explode(',', $inputValue) as $field) {
                if (isset($existsFields[$field])) {
                    $selectFields[$field] = $existsFields[$field];
                }
            }

            return $selectFields;
        }

        return $existsFields;
    }

    /**
     * @param \class-string<DataManager> $ormClass
     * @return array
     */
    protected function getOrmExistsColumns(string $ormClass): array
    {
        $result = [];
        $columns = $ormClass::getMap();
        foreach ($columns as $key => $field) {
            if (!is_numeric($key)) {
                $result[$key] = $key;
                continue;
            }
            if ($field instanceof ScalarField && !$field->isPrivate()) {
                $result[$field->getName()] = $field->getName();
            }
        }

        return $result;
    }
}
