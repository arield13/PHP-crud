<?php
    //require_once 'src/StatusAPI.php';
    // Esto sería por ejemplo el index.php
    function __autoload($name) {
        // En $name llega el texto 'DB' y 'Noticias'
        $fullpath = 'web/src/'.$name.'.php';
        if(file_exists($fullpath)) require_once($fullpath);
    }
    $peopleAPI = StatusAPI::API(); 
?>