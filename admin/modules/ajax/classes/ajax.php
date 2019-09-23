<?php

/**
 * Модуль обрабатывает AJAX запросы.
 * 
 * @package    module
 * @category   xml
 */
class Ajax_Admin{
    function script(){
        $path = Admin_Module::mod_path("ajax", TRUE);
        $path .= "js/ajax.js";
        
        return "<script src=\"{$path}\"></script>";
    }
    function router($class, $method = NULL,$param = NULL){
        if(!$method){
           $method = "fetch";
        }
        $result = array();
        try{
            $class = Admin_Model::factory($class,"ajax");
            
            $result = $class->$method($param);
        }catch(Exception $e){
            $result["exception"] = $e->getMessage();
        }
        
        echo json_encode($result);
    }
}