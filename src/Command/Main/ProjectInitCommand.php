<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectInitCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'project:init';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Initialize new project (skeleton)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!isset($_SERVER['APP_ROOT']) || !is_dir($_SERVER['APP_ROOT'])) {
                throw new \Exception('App root directory does not exist.');
            }

            $this->bxDownloadScripts($output);
            $this->bxMakePublicSymlinks($output);
            $this->bxCopySettings($output);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function bxCopySettings(OutputInterface $output): void
    {
        if (!is_dir($_SERVER['APP_ROOT'] . '/bitrix')) {
            mkdir($_SERVER['APP_ROOT'] . '/bitrix', BX_DIR_PERMISSIONS, true);
        }
        if (!file_exists($_SERVER['APP_ROOT'] . '/bitrix/.settings.php')) {
            copy($_SERVER['APP_ROOT'] . '/config/.settings_default.php', $_SERVER['APP_ROOT'] . '/bitrix/.settings.php');
        }

        symlink($_SERVER['APP_ROOT'] . '/config/.settings_extra.php', $_SERVER['APP_ROOT'] . '/bitrix/.settings_extra.php');
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function bxDownloadScripts(OutputInterface $output): void
    {
        foreach (['bitrixsetup.php', 'restore.php'] as $script) {
            $content = file_get_contents('https://www.1c-bitrix.ru/download/files/scripts/' . $script);
            if (!is_string($content)) {
                $output->writeln(sprintf('<error>Failed download %s', $script));
                continue;
            }
            if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $script, $content)) {
                $output->writeln(sprintf('<error>Failed create public %s', $script));
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function bxMakePublicSymlinks(OutputInterface $output): void
    {
        foreach (['bitrix', 'local', 'upload'] as $dir) {
            $sourceDir = $_SERVER['APP_ROOT'] . '/' .  $dir;
            if (!is_dir($sourceDir) && !mkdir($sourceDir, BX_DIR_PERMISSIONS, true)) {
                $output->writeln('<error>Failed to create directory ' . $sourceDir . '</error>');
                continue;
            }
            $publicDir = $_SERVER['APP_ROOT'] . '/public/' .  $dir;
            if (!is_link($publicDir) && !symlink($sourceDir, $publicDir)) {
                $output->writeln('<error>Failed creatr symlink ' . $sourceDir . '</error>');
            }
        }
    }
}
