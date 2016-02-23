<?php
/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Limp
 * @access      public
 * @since       0.3.0
 *
 * The MIT License
 *
 * Copyright 2015 http://google.com/+BillRocha.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Defaults
error_reporting(E_ALL ^ E_STRICT);
setlocale (LC_ALL, 'pt_BR');
mb_internal_encoding('UTF-8');
date_default_timezone_set('America/Sao_Paulo');

// Constants
$base = __DIR__;
define('_MODE', 'pro'); // options: dev & pro
//Path to WWW
define('_WWW', str_replace('\\', '/', strpos($base, 'phar://') !== false
                    ? dirname(str_replace('phar://', '', $base)).'/'
                    : $base.'/'));
//Path if PHAR mode or false
define('_PHAR', (strpos(_WWW, 'phar://') !== false) ? _WWW : false);
define('_APP', _WWW.'.app/');       //Path to Application
define('_CONFIG', _APP.'Config/');  //Path to config files
define('_LOG', _APP.'Log/');        //Path to log files
define('_HTML', _APP.'Html/');      //Path to HTML files
define('_JS', _WWW.'js/');          //Path to Javascript files
define('_CSS', _WWW.'css/');        //Path to CSS Style files

// Composer autoload
if(file_exists(_APP.'vendor/autoload.php'))
    include _APP.'vendor/autoload.php';

// ------- optional - replace with your favorite libraries/solutions 

// Error/Exception
set_error_handler(['Limp\App\Debug','errorHandler']);
set_exception_handler(['Limp\App\Debug', 'exceptionHandler']);

exit('<pre>'.print_r(get_defined_constants(), true).'</pre>');

//Cli mode
if(php_sapi_name() === 'cli') return new Limp\Cli\Limp($argv);

//Mounting Application
Limp\App\App::mount();

//...and run
Limp\App\App::run();