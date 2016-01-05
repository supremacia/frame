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

namespace Model;
use Lib\App;

class Access {

    private $server = [];

    /**
     * Get data from $_SERVER
     *
     */
    function getServer(){
        // Pegando dados de ACESSO do cliente
        $this->server = [':req'=>$_SERVER['REQUEST_URI'],
                         ':met'=>$_SERVER['REQUEST_METHOD'],
                         ':rem'=>$_SERVER['REMOTE_ADDR'],
                         ':age'=>$_SERVER['HTTP_USER_AGENT'],
                         ':acc'=>$_SERVER['HTTP_ACCEPT'],
                         ':enc'=>$_SERVER['HTTP_ACCEPT_ENCODING'],
                         ':lan'=>$_SERVER['HTTP_ACCEPT_LANGUAGE'],
                         ':idate'=>date('Y-m-d H:I:s')];
        return $this;
    }

    /**
     *  Save data in Mysql
     *
     */
    function save(){
        App::db()->query('INSERT INTO access (REQUEST,METHOD,REMOTE,AGENT,ACCEPT,ENCODING,LANGUAGE,IDATE)
                            VALUES (:req,:met,:rem,:age,:acc,:enc,:lan,:idate)',
                            $this->server);
        return $this;
    }

    /**
     *  Deleta gravações anteriores a 30 dias
     *
     */
    function clear(){
        App::db()->query('DELETE FROM access WHERE DATE(access.IDATE) <= DATE(DATE(NOW())-30)');
        return $this;
    }


}