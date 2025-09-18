<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Composite\Page as CompositePage;
use Bitrix\Main\Application as BitrixApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'cache:clear';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Cleanup cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // app cache
            $application = BitrixApplication::getInstance();
            if (is_callable([$application, 'getManagedCache'])) {
                $application->getManagedCache()->cleanAll();
            }
            if (is_callable([$application, 'getTaggedCache'])) {
                $application->getTaggedCache()->deleteAllTags();
            }

            // Composite cache
            if (class_exists(CompositePage::class)) {
                CompositePage::getInstance()->deleteAll();
            }

            // OPcache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            $output->writeln("<info>Cache successfully deleted.</info>");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
