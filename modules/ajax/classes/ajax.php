<?php

/**
 * Модуль обрабатывает AJAX запросы.
 * 
 * @package    module
 * @category   xml
 */
class Ajax_Module{
    function script(){
        $path = Module::mod_path("ajax", TRUE);
        $path .= "js/ajax.js";
        
        return "<script src=\"{$path}\"></script>";
    }
    function router($class = NULL, $method = NULL,$param = NULL){
        header('Accept-Ranges: bytes'."\r\n");
        
        $url = trim(stristr(Registry::i()->founds["url"],"/"),"/");
        
        $pars = Model::factory("route","system")->parse_url($url);
        
        if(!$class){
            $class = $pars["module"];
        }
        
        if(!$method){
           $method = $pars["action"];
        }
        
        if(!$param){
           $param = $pars["param"];
        }
        
        $result = array();
        try{
            $result = Model::load($class,"ajax",$method,$param);
        }catch(Exception $e){
            $result["exception"] = $e->getMessage();
        }
        
        echo json_encode($result);
    }
}