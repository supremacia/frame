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

namespace Controller;

use Controller\App\Base;
use Model;
use Limp\App\App as Dock;
use Limp\Doc;
use Limp\Data;

/**
 * Description of home
 *
 * @author Bill
 */
class Home extends Base 
{


    function __construct($params) {
        $this->params = $params;
    }

    //All not 
    function main() {
        //parent::main();


        /* Exemplo de uso do método push/pull da classe App
         */
        Dock::push('list', function($txt){return ' -- '.$txt.' -- ';});
        $h = Dock::pull('list');
        echo $h('olá');

        Dock::push('can', new Data\Can(null, true));
        echo '<br>Código CAN para o número "108788293834878" : '.Dock::pull('can')->encode(108788293834878);
        $t = intval(microtime(true));
        echo '<br>Código CAN para o time "'.$t.'" : '.($d = Dock::pull('can')->encode($t));
        echo '<br>Decodificando: '.Dock::pull('can')->decode($d);

        //mostrando a chve CAN
        Dock::p(file_get_contents(_CONFIG.'keys/can.key'), true);


        //Teste de saída do método MAIN
        exit('<br> -- Controller\Home\main : ' . Dock::p($this->params, true));
    }

    function index() {
        exit('<br> -- Controller\Home\index : ' . Dock::p($this->params, true));
    }

    function other() {
        //Salvando no banco de dados com o Model\Access
        (new Model\Access)->getUserData()->save();

        //Output
        exit('<br> -- Controller\Home\other : ' . Dock::p($this->params, true));
    }

    function login(){ 
        $lp = $this->langPath();
        $key = str_replace(["\r", 
                             "\n", 
                             '-----BEGIN PUBLIC KEY-----', 
                             '-----END PUBLIC KEY-----'], 
                             '', 
                             file_get_contents(_CONFIG . 'keys/public.key'));

        $d = new Doc\Html('login');
        $d->val('title', 'Zumbi :: Login')
                ->jsvar('key', $key)
                ->jsvar('wsUri', 'ws://127.0.0.1:8080')
                ->insertStyles(['reset', 'style'])
                ->insertScripts([$lp.'msg', 
                                'lib/aes', 
                                'lib/jszip', 
                                'lib/lib', 
                                'lib/rsa', 
                                'lib/jsbn', 
                                'lib/xhat', 
                                'main'])                          
                ->body($lp.'body')
                ->render()
                ->send();
    }

}
