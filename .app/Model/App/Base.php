<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Model
 * @access      public
 * @since       0.3.0
 *
 */

namespace Model\App;

use Limp\Data;
use Config\Database;

class Base {

    public $db = null;

    function __construct(){
        $this->db = new Data\Db(Database::get());
    }

}