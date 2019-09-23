<?php defined('MODPATH') OR exit();

class Model_permission_Socket{
    
    function __construct(){
        
    }
    
    function fetch(){
        $dir = utf8_decode($_POST['dir']);
        $encode_name = utf8_decode($_POST['encode_name']);
        $path = $dir.'/'.$encode_name;
        if(!empty($path) AND (is_file($path) OR is_dir($path)) )
            echo substr(sprintf('%o', fileperms($path)), -3);;
        exit();
    }
}