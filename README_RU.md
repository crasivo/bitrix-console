💻 Bitrix Console
===

Адаптер популярной библиотеки [Symfony Console](https://github.com/symfony/console) в 1C-Bitrix & Bitrix24.

<u>Минимальные</u> требования для установки:

- Версия ядра 1C-Bitrix (main): `v20.5.400`
- Версия PHP: `v7.2`
- Версия Symfony Console: `v5.0`

# 🚀 Быстрый старт

Для работы библиотеки достаточно установить [Composer](https://getcomposer.org/) пакет через команду:

```shell
$ cd /path/to/project
$ composer require crasivo/bitrix-console
```

Библиотека готова к использованию. Исполняемый файл лежит в папке `vendor/bin/console`.
Ниже представлен пример команды для получения списка всех модулей.

```shell
$ php vendor/bin/console list
```

# 🕹️ Эксплуатация

## Создание команд

Процесс создания команд для 1C-Bitrix (текущий адаптер) ничем не отличается от [Symfony](https://symfony.com/doc/current/console.html).
Классы команд можно хранить где угодно, включая папки в кастомном модуле. Единственное условие для работы — видимость для Composer autoload.

## Регистрация команд

В библиотеке присутствует класс `CommandLocator` (аналог штатного [ServiceLocator](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=14032)), 
который загружает команды из конфигурационных файлов `.settings.php`.
Если ваш модуль установлен, то локатор также автоматически подгрузит команды из него.

Для регистрации достаточно определить секцию `console.commands` и указать в неё список ваших команд.
Ниже представлен пример регистрации команд различными способами.

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

## 📜 Лицензия

Данный проект распространяется по лицензии [MIT](https://en.wikipedia.org/wiki/MIT_License).
Полный текст лицензии можно прочитать в файле [LICENSE](LICENSE).
