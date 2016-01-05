<?php

/**
 * @todo Reavaliar a funcão __construct
 *       Realmente usar $params['request'] para chamar a função ou deixar o Router escolher !?
 */

namespace Controller;

use Lib;
use Model;
use Lib\App;

abstract class AppController {

    public $model = null;
    public $key = null;
    public $params = [];

    /** Abstratic Controller constructor
     *  -- Bypass it in your controller
     */
    function __construct($params) {
        //Estancia Model\Zumbi para n o Objeto
        $this->model = new Model\Zumbi;
        //save params
        $this->params = $params;

        if (isset($this->params['request'][1]) && method_exists($this, $this->params['request'][1])) {
            return $this->{$this->params['request'][1]}();
        } else {
            return $this->main();
        }
    }

    /** Default MAIN method
     * -- Bypass it in your controller
     */
    function main() {
        $d = new Lib\Doc('nopage');
        $d->sendCache();
        $d->val('title', 'Zumbi :: 404')
                ->insertStyles(['reset', 'nopage'])
                ->body('nopage')
                ->render()
                ->send();
    }

    // ----------- USER FUNCTIONS --------------

    /** Decodifica entrada via Post
     *
     *
     */
    function _decodePostData() {
        if (!isset($_POST['data']))
            return false;
        $rec = json_decode($_POST['data']);

        //Se não for JSON...
        if (!is_object($rec))
            return false;

        if (isset($rec->enc)) {
            //$zumbi = new Model\Zumbi;
            $this->key = $this->model->getUserKey($rec->id);
            if ($this->key === false)
                return false;

            //Decriptando
            Lib\Aes::size(256);
            return ['data' => $rec, 'dec' => json_decode(Lib\Aes::dec($rec->enc, $this->key))];
        }
        return ['data' => $rec];
    }

    /** Envia dados criptografados para o browser
     *
     *
     */
    function _sendEncriptedData($dt) {
        //Json encoder
        $enc = json_encode($dt);

        //Encriptando
        Lib\Aes::size(256);
        $enc = Lib\Aes::enc($enc, $this->key);

        //Enviando
        exit($enc);
    }

    /** Retorna o diretório para linguagem aceita pelo browser
     * Default = 'lang/en/'
     *
     */
    function _langPath() {
        $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        switch (substr($lang[0], 0, 2)) {
            case 'pt': $l = 'pt';
                break;
            case 'es': $l = 'es';
                break;
            case 'fr': $l = 'fr';
                break;
            default: $l = 'en';
                break;
        }
        return 'lang/' . $l . '/';
    }

}
