💻 Bitrix Console
===

An adapter for the popular [Symfony Console](https://github.com/symfony/console) library in 1C-Bitrix & Bitrix24.

<u>Minimum</u> requirements for installation:

- 1C-Bitrix kernel (main) version: `v20.5.400`
- PHP version: `v7.2`
- Symfony Console version: `v5.0`

# 🚀 Quick Start

To use the library, simply install the [Composer](https://getcomposer.org/) package via the command:

```shell
$ cd /path/to/project
$ composer require crasivo/bitrix-console
```

The library is ready to use. The executable file is located in the `vendor/bin/console` folder.
Below is an example command to get a list of all modules.

```shell
$ php vendor/bin/console list
```

# 🕹️ Usage

## Creating Commands

The process of creating commands for 1C-Bitrix (current adapter) is no different from [Symfony](https://symfony.com/doc/current/console.html).
Command classes can be stored anywhere, including folders in a custom module. The only requirement for operation is visibility for Composer autoload.

## Registering Commands

The library includes the `CommandLocator` class (analogous to the standard [ServiceLocator](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=14032)),
which loads commands from `.settings.php` configuration files.
If your module is installed, the locator will also automatically load commands from it.

To register, simply define the `console.commands` section and specify a list of your commands in it.
Below is an example of registering commands in various ways.

```php
use Your\Awesome\Command;

return [
    'console' => [
        'value' => [
            'commands' => [
                Command::class, // simple (via class name)
                'some.command' => ['className' => Command::class], // classic
                'some.command' => ['constructor' => function () { return new Command(); }] // via constructor
            ],
        ],
        'readonly' => true,
    ],
];
```

---

## 📜 License

This project is distributed under the [MIT](https://en.wikipedia.org/wiki/MIT_License) license.
The full text of the license can be found in the [LICENSE](LICENSE) file.
