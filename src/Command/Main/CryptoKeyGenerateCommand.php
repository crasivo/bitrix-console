<?php

namespace Crasivo\Bitrix\Console\Command\Main;

use Bitrix\Main\Config\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CryptoKeyGenerateCommand extends Command
{
    /** @var string */
    public const COMMAND_NAME = 'crypto:key:generate';

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Generate crypto key');
        $this->addOption('env', null, InputOption::VALUE_NONE, 'Save .env file');
        $this->addOption('settings', null, InputOption::VALUE_NONE, 'Save .settings.php file');
        $this->addOption('replace', null, InputOption::VALUE_NONE, 'Replace exists configuration');
        $this->addOption('quiet', 'q', InputOption::VALUE_NONE, '');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $randomKey = bin2hex(random_bytes(16));
            if (!$input->getOption('quiet')) {
                $output->writeln(sprintf('<info>Crypto key: %s</info>', $randomKey));
            }
            if ($input->getOption('env')) {
                $this->saveProjectEnv($randomKey, $input->getOption('replace'));
            }
            if ($input->getOption('settings')) {
                $this->saveProjectSettings($randomKey, $input->getOption('replace'));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');

            return self::FAILURE;
        }
    }

    /**
     * @param string $key
     * @param bool $replace
     * @return void
     * @throws \Throwable
     */
    protected function saveProjectEnv(string $key, bool $replace): void
    {
        $envFile = $_SERVER['APP_ROOT'] . '/.env';
        $envContent = file_get_contents($envFile);
        $defined = false !== preg_match('/^APP\_CRYPTO\_KEY\=(.*)$/m', $envContent, $matches);

        // check var exists
        if (!$defined) {
            $envContent = PHP_EOL . 'APP_CRYPTO_KEY=' . $key . PHP_EOL;
            if (false === file_put_contents($envFile, $envContent)) {
                throw new \Exception('Cannot write to crypto key.');
            }

            return;
        }

        // check empty value
        if (!$replace && trim((string)$matches[1]) !== '') {
            return;
        }

        $envContent = preg_replace('/^APP\_CRYPTO\_KEY\=(.*)$/m', 'APP_CRYPTO_KEY=' . $key, $envContent);
        if (!is_string($envContent)) {
            throw new \Exception('Failed replace exists crypto key.');
        }
        if (false === file_put_contents($envFile, $envContent)) {
            throw new \Exception('Failed replace env file.');
        }
    }

    /**
     * @param string $key
     * @param bool $replace
     * @return void
     * @throws \Throwable
     */
    protected function saveProjectSettings(string $key, bool $replace): void
    {
        $configuration = Configuration::getInstance();
        $crypto = $configuration->get('crypto');
        if (!is_array($crypto) || empty($crypto['crypto_key'])) {
            $crypto = ['crypto_key' => $key];
        }
        if ($replace) {
            $crypto['crypto_key'] = $key;
        }

        $configuration->addReadonly('crypto', $crypto);
        $configuration->saveConfiguration();
    }
}
