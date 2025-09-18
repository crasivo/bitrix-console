<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\ModuleTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleListCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'module:list';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('List all modules');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $allModules = [];
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules')) {
                foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/*') as $d) {
                    $id = basename($d);
                    if (!isset($allModules[$id]) && file_exists($d . '/install/index.php')) {
                        $allModules[$id] = ['ID' => $id, 'VERSION' => $this->getModuleVersion($d), 'INSTALLED' => 'N'];
                    }
                }
            }
            if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/local/modules')) {
                foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/local/modules/*') as $d) {
                    if (!isset($allModules[$id]) && file_exists($d . '/install/index.php')) {
                        $allModules[] = ['ID' => $id, 'VERSION' => $this->getModuleVersion($d), 'INSTALLED' => 'N'];
                    }
                }
            }

            // check installed modules
            $queryResult = ModuleTable::getList(['select' => ['ID']]);
            while ($row = $queryResult->fetch()) {
                if (isset($allModules[$row['ID']])) {
                    $allModules[$row['ID']]['INSTALLED'] = 'Y';
                }
            }

            @ksort($allModules);

            // render output table
            $table = new Table($output);
            $table->setHeaders(['ID', 'VERSION', 'INSTALLED']);
            $table->addRows($allModules);
            $table->render();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }

    /**
     * @param string $moduleDir
     * @return string|null
     */
    protected function getModuleVersion(string $moduleDir): ?string
    {
        // check version file
        if (!file_exists($moduleDir . '/install/version.php')) {
            if (basename($moduleDir) === 'main') {
                return SM_VERSION;
            }

            return null;
        }

        include $moduleDir . '/install/version.php';
        if (!isset($arModuleVersion) || !is_array($arModuleVersion)) {
            return null;
        }
        if (isset($arModuleVersion['VERSION'])) {
            return $arModuleVersion['VERSION'];
        }

        return null;
    }
}
