<?php
/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Controller
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

namespace Controller\App;

use Model;
use Limp\App;
use Limp\Doc;
use Limp\Data;

abstract class Base 
{
    public $model = null;
    public $key = null;
    public $params = [];

    /** Abstratic Controller constructor
     *  -- Bypass it in your controller
     */
    function __construct($params) 
    {
        //Estancia Model\Zumbi para n o Objeto
        $this->model = new Model\Zumbi;
        //save params
        $this->params = $params;
    }

    /** Default MAIN method
     * -- Bypass it in your controller
     */
    function main() 
    {
        $d = new Doc\Html('nopage');
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
    final function decodePostData() 
    {
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
            Data\Aes::size(256);
            return ['data' => $rec, 'dec' => json_decode(Data\Aes::dec($rec->enc, $this->key))];
        }
        return ['data' => $rec];
    }

    /** Envia dados criptografados para o browser
     *
     *
     */
    final function sendEncriptedData($dt) 
    {
        //Json encoder
        $enc = json_encode($dt);

        //Encriptando
        Data\Aes::size(256);
        $enc = Data\Aes::enc($enc, $this->key);

        //Enviando
        exit($enc);
    }

    /** Retorna o diretório para linguagem aceita pelo browser
     * Default = 'lang/en/'
     *
     */
    final function langPath() 
    {
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
