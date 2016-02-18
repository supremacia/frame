<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Application
 * @access      public
 * @since       0.3.0
 *
 */

//Configurations
include __DIR__.'/.app/limp';
include ¢CONFIG.'router.php';
include ¢CONFIG.'database.php';

//Mount Application
App::mount($router);

//...and run
App::run();