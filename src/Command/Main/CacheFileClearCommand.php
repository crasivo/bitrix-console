<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheFileClearCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'cache:file:clear';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Clear file cache');
        $this->setAliases(['cache:files:clear']);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $permissions = defined('BX_DIR_PERMISSIONS') ? BX_DIR_PERMISSIONS : 0755;
            foreach (['cache', 'managed_cache', 'stack_cache'] as $dir) {
                $absDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $dir;
                if (is_dir($absDir)) {
                    @rmdir($absDir);
                    @mkdir($absDir, $permissions, true);
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
