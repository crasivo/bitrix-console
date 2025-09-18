<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Composite\Page as CompositePage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCompositeClearCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'cache:composite:clear';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Clear composite cache');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if (!class_exists(CompositePage::class)) {
                throw new \RuntimeException(sprintf('Class %s does not exist', CompositePage::class));
            }

            CompositePage::getInstance()->deleteAll();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }
}
