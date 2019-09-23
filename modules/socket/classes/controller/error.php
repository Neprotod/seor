<?php defined('MODPATH') OR exit();

class Controller_Error_Socket{

    public $xml;
    
    function __construct(){
        if(empty(Registry::i()->urlArray)){
            Registry::i()->urlArray[] = 'xml';
        }
        $this->xml = Model::factory('error_xml','socket');
    }
    
    function fetch(){
        $action = reset(Registry::i()->urlArray);
        if($action == 'xml'){
            $this->xml->fetch();
        }
    }
}