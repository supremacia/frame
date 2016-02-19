<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.1.7
 * @package     Application
 * @access      public
 * @since       0.3.0
 *
 */

// Defaults
error_reporting(E_ALL ^ E_STRICT);
setlocale (LC_ALL, 'pt_BR');
mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Sao_Paulo');

// Constants
$base = __DIR__;
define('¢MODE', 'pro'); // options: dev & pro
//Path to WWW
define('¢WWW', str_replace('\\', '/', strpos($base, 'phar://') !== false
                    ? dirname(str_replace('phar://', '', $base)).'/'
                    : $base.'/'));
//Path if PHAR mode or false
define('¢PHAR', (strpos(¢WWW, 'phar://') !== false) ? ¢WWWW : false);
define('¢APP', ¢WWW.'.app/');      	//Path to Application
define('¢CONFIG', ¢APP.'config/'); 	//Path to config files
define('¢LOG', ¢APP.'log/');		//Path to log files
define('¢HTML', ¢APP.'html/');		//Path to HTML files
define('¢JS', ¢WWW.'js/');			//Path to Javascript files
define('¢CSS', ¢WWW.'css/');		//Path to CSS Style files

//Helpers
include ¢APP.'functions.php';

// Error/Exception set
set_error_handler("errorHandler");
set_exception_handler('exceptionHandler');

// Internal autoload
set_include_path(¢APP.PATH_SEPARATOR.get_include_path());
spl_autoload_register(function($class) {
    $class = ¢APP.str_replace('\\', '/', trim(strtolower($class), '\\')).'.php';
    return (($file = _file_exists($class)) !== false ? require_once $file : false);
});

// Composer autoload
if(file_exists(¢APP.'vendor/autoload.php'))
  include ¢APP.'vendor/autoload.php';

// Mount the "App" static dock
class_alias('Limp\App\App', 'App');

//Configurations
include ¢CONFIG.'database.php';

//Cli mode
if(php_sapi_name() === 'cli') {
	include ¢APP.'vendor/limp/cli/limp.php';
	exit();
}

include ¢CONFIG.'router.php';

//Mount Application
App::mount($router);

//...and run
App::run();