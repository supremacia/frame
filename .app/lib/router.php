<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Lib;

/**
 * Description of router
 *
 * @author Bill
 */
class Router 
{

    private $url = '';
    private $http = '';
    private $base = '';
    private $request = '';
    private $routers = [];
    private $all = [];
    private $method = 'GET';

    function __construct($routers = [], $request = null, $url = null) 
    {
        if ($request !== null)
            define('¢RQST', $request);
        if ($url !== null)
            define('¢URL', $url);
        $this->routers = $routers;
        $this->method = $this->requestMethod();
        $this->mount();
    }

    /**
     * Insert routers
     *
     */
    function respond($method = 'all', $request = '', $controller = 'home', $action = 'main') 
    {
        $method = strtoupper(trim($method));
        if ($method == 'ALL')
            $this->all[] = ['request' => trim($request, '/'), 'controller' => $controller, 'action' => $action];
        else {
            foreach (explode('|', $method) as $mtd) {
                $this->routers[$mtd][] = ['request' => trim($request, '/'), 'controller' => $controller, 'action' => $action];
            }
        }
        return $this;
    }

    /**
     * Resolve 
     * 
     */
    function resolve() 
    {
        //first: serach in ALL
        $route = $this->searchRouter($this->all);

        //now: search for access method
        if ($route === false && isset($this->routers[$this->method])) {
            $route = $this->searchRouter($this->routers[$this->method]);
        }

        //not match...
        if ($route === false) {
            $route['controller'] = $route['action'] = $route['params'] = null;
        } elseif (is_callable($route['controller'])) {
            //call the handling function with the URL parameters
            $route = call_user_func_array($route['controller'], $route['params']);
        }
        //out with decoded router OR false
        return $route;
    }

    //Gets
    function getUrl() 
    {
        return $this->url;
    }

    function getHttp() 
    {
        return $this->http;
    }

    function getBase() 
    {
        return $this->base;
    }

    function getRequest() 
    {
        return $this->request;
    }

    function getRouters() 
    {
        return $this->routers;
    }

    function getAll() 
    {
        return $this->all;
    }

    function getMethod() 
    {
        return $this->method;
    }

    /**
     * Mount 
     */
    private function mount() 
    {
        //Detect SSL access
        if (!isset($_SERVER['SERVER_PORT']))
            $_SERVER['SERVER_PORT'] = 80;
        $http = (isset($_SERVER['HTTPS']) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1 || $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';

        //What's base??!
        $base = isset($_SERVER['PHAR_SCRIPT_NAME']) ? dirname($_SERVER['PHAR_SCRIPT_NAME']) : rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), ' /');

        if ($_SERVER['SERVER_PORT'] != 80)
            $base .= ':' . $_SERVER['SERVER_PORT'];

        //URL & REQST Constants:
        defined('¢RQST') || define('¢RQST', urldecode(isset($_SERVER['REQUEST_URI']) ? urldecode(trim(str_replace($base, '', trim($_SERVER['REQUEST_URI'])), ' /')) : ''));

        defined('¢URL') || define('¢URL', isset($_SERVER['SERVER_NAME']) ? $http . $_SERVER['SERVER_NAME'] . $base . '/' : '');

        $this->request = ¢RQST;
        $this->url = ¢URL;
        $this->base = $base;
        $this->http = $http;
    }

    /**
     * Search for valide router
     *
     * @params
     */
    private function searchRouter($routes) 
    {
        foreach ($routes as $route) {
            if (!preg_match_all('#^' . $route['request'] . '$#', $this->request, $matches, PREG_OFFSET_CAPTURE))
                continue;
            // Rework matches to only contain the matches, not the orig string
            $matches = array_slice($matches, 1);

            // Extract the matched URL parameters (and only the parameters)
            $params = array_map(function ($match, $index) use ($matches) {

                // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                } // We have no following parameters: return the whole lot
                else {
                    return (isset($match[0][0]) ? trim($match[0][0], '/') : null);
                }
            }, $matches, array_keys($matches));

            $route['params'] = $params;
            return $route;
        }
        //not exists ...
        return false;
    }

    /**
     * Get all request headers
     * @return array The request headers
     */
    private function requestHeaders() 
    {
        // getallheaders available, use that
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // getallheaders not available: manually extract 'm
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get the request method used, taking overrides into account
     * @return string The Request method to handle
     */
    private function requestMethod() 
    {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];

        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        } // If it's a POST request, check for a method override header
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->requestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }

}
