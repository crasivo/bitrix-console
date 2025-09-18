<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheTaggedClearCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'cache:tagged:clear';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Clear tagged cache');
        $this->addOption('tag', 't', InputOption::VALUE_OPTIONAL, 'Tag name');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $taggedCache = Application::getInstance()->getTaggedCache();
            $tag = $input->getOption('tag');
            if (is_string($tag) && $tag !== '') {
                $taggedCache->clearByTag($tag);
            } else {
                $taggedCache->deleteAllTags();
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
