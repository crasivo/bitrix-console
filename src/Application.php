<?php

namespace Crasivo\Bitrix\Console;

use Bitrix\Main\Application as BitrixApplication;

class Application extends \Symfony\Component\Console\Application
{
    /** @var BitrixApplication|null */
    private $bitrixApplication = null;

    /**
     * Application constructor.
     *
     * @param string|null $name
     * @param string|null $version
     */
    public function __construct(?string $name = null, ?string $version = null) {
        if (\php_sapi_name() !== 'cli') {
            throw new \RuntimeException('This application can only be run from the command line.');
        }
        if (!$name && !empty($_ENV['APP_NAME'])) {
            $name = (string)$_ENV['APP_NAME'];
        }
        if (!$version && !empty($_ENV['APP_VERSION'])) {
            $version = (string)$_ENV['APP_VERSION'];
        }

        parent::__construct($name ?? '1C-Bitrix', $version ?? constant('SM_VERSION'));
        if (class_exists(BitrixApplication::class)) {
            $this->bitrixApplication = BitrixApplication::getInstance();
        }
    }

    /**
     * Initialize application.
     *
     * @return void
     * @throws \Throwable
     */
    public function init(): void
    {
        // load general commands
        $commandLocator = CommandLocator::getInstance();
        $commandLocator->load();
        $this->addCommands($commandLocator->all());
    }

    /**
     * @param int $status
     * @return void
     */
    public function finish(int $status = 0): void
    {
        if (is_callable([$this->bitrixApplication, 'terminate'])) {
            $this->bitrixApplication->terminate($status);
        }

        die($status);
    }
}
