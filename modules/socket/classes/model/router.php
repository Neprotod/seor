<?php defined('MODPATH') OR exit();

class Model_Router_Socket{
    
    public $url;
    
    function __construct(){
        Registry::i()->urlArray = array();
        Registry::i()->url = '';
    }
    
    function init(){
        $this->url = URL::instance();

        $url = $this->url->url;
        
        $url = explode('/', $url);
        
        Registry::i()->host = array_shift($url);
        
        
        if(empty($url))
            return Registry::i()->action = NULL;
            
        $action = array_shift($url);
        
        Registry::i()->class_link = Registry::i()->host . '/' . $action;
        
        if(!empty($url)){
            Registry::i()->urlArray = $url;
            Registry::i()->url = implode('/', $url);
        }
        // Проверяем и находим модел или контроллер с которой будем работать
        if(in_array($action,(array)($model = $this->scandir('model')))){
            return Registry::i()->action['model'] = $action;
        }
        elseif(in_array($action,(array)($controller = $this->scandir('controller')))){
            return Registry::i()->action['controller'] = $action;
        }

        return Registry::i()->action = FALSE;
    }
    
    private function scandir($dir){
        $path = Module::mod_path('socket').'classes'.DIRECTORY_SEPARATOR.$dir;
        $fonds = scandir($path);
        foreach($fonds as $key => &$fong){
            if($fong == '.' OR $fong == '..'){
                unset($fonds[$key]);
            }
            elseif(!empty($fong) AND !is_array($fong)){
                $fong = substr($fong,0,strpos($fong,'.'));
            }
        }
        return $fonds;
    }
}