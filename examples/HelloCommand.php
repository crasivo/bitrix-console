<?php

namespace Your\Module;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('say:hello');
        $this->setDescription('Say hello (example)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World!');
        return self::SUCCESS;
    }
}
