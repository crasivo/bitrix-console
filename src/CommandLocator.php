<?php

namespace Crasivo\Bitrix\Console;

use Bitrix\Main\Config\Configuration as BitrixConfiguration;
use Bitrix\Main\ModuleManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLocator implements ContainerInterface
{
    /** @var self|null */
    private static $instance = null;

    /** @var Command[] */
    private $symfonyCommands = [];

    /** @var string */
    private $settingsName = 'console';

    /**
     * Singleton initializer.
     *
     * @return static
     */
    public static function getInstance()
    {
        return self::$instance ?? (self::$instance = new static());
    }

    /**
     * @return Command[]
     */
    public function all()
    {
        return $this->symfonyCommands;
    }

    /**
     * @param string $id
     * @return Command
     */
    public function get(string $id)
    {
        if (!isset($this->symfonyCommands[$id])) {
            throw new CommandNotFoundException(sprintf('Command "%s" not found.', $id));
        }

        return $this->symfonyCommands[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->symfonyCommands[$id]);
    }

    /**
     * Load all commands.
     *
     * @return void
     */
    public function load(): void
    {
        $this->loadInternal();
        if ($GLOBALS['APPLICATION']) {
            $this->loadFromConfig();
            $this->loadFromInstalledModules();
        }
    }

    /**
     * @param Command $command
     * @return void
     */
    public function put(Command $command)
    {
        $this->symfonyCommands[$command->getName()] = $command;
    }

    /**
     * @param string|null $moduleId
     * @return void
     */
    protected function loadFromConfig(?string $moduleId = null): void
    {
        try {
            $configuration = BitrixConfiguration::getInstance($moduleId)->get($this->settingsName) ?? [];
            $commands = is_array($configuration) && is_array($configuration['commands']) ? $configuration['commands'] : [];
            if ($commands === []) {
                return;
            }

            foreach ($commands as $cmd) {
                if (is_string($cmd)) {
                    $cmd = ['className' => $cmd];
                }
                if (is_array($cmd)) {
                    $this->initializeCommandFromSettings($cmd);
                }
            }
        } catch (\Throwable $exception) {
            if (is_string($moduleId) && $moduleId !== '') {
                echo sprintf('Failed load commands from module "%s": %s', $moduleId, $exception->getMessage());
            } else {
                echo 'Failed load commands: ' . $exception->getMessage();
            }

            echo $exception->getTraceAsString();
        }
    }

    /**
     * Load commands from installed modules.
     *
     * @internal
     * @return void
     */
    public function loadFromInstalledModules(): void
    {
        foreach (ModuleManager::getInstalledModules() as $id => $module) {
            $this->loadFromConfig($id);
        }
    }

    /**
     * Load internal commands.
     *
     * @internal
     * @return void
     */
    public function loadInternal(): void
    {
        foreach (glob(__DIR__ . '/Command/*/*.php') as $filepath) {
            $namespace = __NAMESPACE__ . '\\Command\\' . basename(dirname($filepath));
            $this->initializeCommandFromSettings([
                'className' => $namespace . '\\'. basename($filepath, '.php'),
            ]);
        }
    }

    /**
     * @param array $settings
     * @return mixed|void
     */
    protected function initializeCommandFromSettings(array $settings)
    {
        try {
            $command = null;
            if (isset($settings['className']) && class_exists($settings['className'])) {
                $command = new $settings['className']();
            } elseif (isset($settings['constructor']) && is_callable($settings['constructor'])) {
                $command = call_user_func($settings['constructor']);
            }
            if ($command instanceof Command) {
                $this->put($command);
            }
        } catch (\Throwable $exception) {
            // nothing
        }
    }
}
