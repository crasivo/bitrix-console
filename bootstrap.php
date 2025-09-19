<?php
// define bitrix constants
@define('BX_NO_ACCELERATOR_RESET', true);
@define('DisableEventsCheck', true);
@define('LANGUAGE_ID', 'ru');
@define('LOG_FILENAME', 'php://stdout');
@define('NO_AGENT_CHECK', true);
@define('NO_AGENT_STATISTIC', 'Y');
@define('NOT_CHECK_PERMISSIONS', true);
@define('NO_KEEP_STATISTIC', true);
@define('STOP_STATISTICS', true);

// define php options
@ini_set('error_log', 'php://stdout');
@set_time_limit(0);
@error_reporting(E_ERROR);

// define app root
if (!isset($_SERVER['APP_ROOT']) || !is_dir((string)$_SERVER['APP_ROOT'])) {
    $appRoot = __DIR__;
    while (($appRoot = dirname($appRoot)) !== '/') {
        if (file_exists($appRoot . '/composer.lock')) {
            $_SERVER['APP_ROOT'] = $appRoot;
            break;
        }
        if (file_exists($appRoot . '/index.php')) {
            $_SERVER['APP_ROOT'] = $appRoot;
            break;
        }
    }
}

// define document root
if (!isset($_SERVER['DOCUMENT_ROOT']) || !is_dir((string)$_SERVER['DOCUMENT_ROOT'])) {
    foreach (['/index.php', '/public/index.php', '/../index.php'] as $index) {
        $index = $_SERVER['APP_ROOT'] . $index;
        if (file_exists($index)) {
            $_SERVER['DOCUMENT_ROOT'] = dirname(realpath($index));
        }
    }
}

// require bitrix prolog
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
}

// define additional runtime constants
defined('BX_DIR_PERMISSIONS') || define('BX_DIR_PERMISSIONS', 0755);
defined('BX_FILE_PERMISSION') || define('BX_FILE_PERMISSION', 0664);
defined('SM_VERSION') || define('SM_VERSION', '0.0.0');
defined('SM_VERSION_DATE') || define('SM_VERSION_DATE', '1970-01-01 00:00:00');
defined('START_EXEC_TIME') || define('START_EXEC_TIME', microtime(true));

// require composer
if (!class_exists('\\Crasivo\\Bitrix\\Console\\Application') && file_exists($_SERVER['APP_ROOT'] . '/vendor/autoload.php')) {
    require $_SERVER['APP_ROOT'] . '/vendor/autoload.php';
}
