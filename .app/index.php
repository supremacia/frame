<?php
//Deny From ALL!!
$url = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http');
$url .= '://'.(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'google.com/+BillRocha');
header('Location: '.$url, TRUE, 301);