<?php

// Defaults
error_reporting(E_ALL ^ E_STRICT);
setlocale (LC_ALL, 'pt_BR');
mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Sao_Paulo');

// Constants
$base = dirname(dirname(__DIR__));
define('APP_MODE', 'dev'); // options: dev & pro
define('WEB_PATH', str_replace('\\', '/', strpos($base, 'phar://') !== false
                    ? dirname(str_replace('phar://', '', $base)).'/'
                    : $base.'/'));
define('RPHAR', (strpos(WEB_PATH, 'phar://') !== false) ? WEB_PATH : false);
define('APP_PATH', WEB_PATH.'.app/');
define('CONFIG_PATH', APP_PATH.'config/');
define('LOG_PATH', APP_PATH.'log/');
define('HTML_PATH', APP_PATH.'html/');

//Helpers
include APP_PATH.'lib/functions.php';

// Error/Exception set
set_error_handler("errorHandler");
set_exception_handler('exceptionHandler');

// Internal autoload
set_include_path(APP_PATH.PATH_SEPARATOR.get_include_path());
spl_autoload_register(function($class) {
    $class = APP_PATH.str_replace('\\', '/', trim(strtolower($class), '\\')).'.php';
    return (($file = _file_exists($class)) !== false ? require_once $file : false);
});

// Composer autoload
include APP_PATH.'vendor/autoload.php';

// Mount the "App" static dock
class_alias('Lib\App', 'App');
class_alias('Lib\App', 'dock');