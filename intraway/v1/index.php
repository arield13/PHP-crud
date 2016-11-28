<?php
    error_reporting(0);
    function __autoload($name) {
        // En $name llega el texto 'DB' y 'Noticias'
        $fullpath = 'web/src/'.$name.'.php';
        if(file_exists($fullpath)) require_once($fullpath);
    }
    $peopleAPI = StatusAPI::API(); 
?>
