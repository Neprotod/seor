<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Default_System_Admin{
    
    //@var string  URL
    protected $url;
    
    function __construct(){
        $this->url = Admin::$url;
    }
    
    /*
     * Вывод стандартной темы
     * 
     * @return string контент
     */
    function fetch(){
        $user = Registry::i()->auth->user;
        
        $data = array();
        $data["content"] = "Добро пожаловать ". $user["display_name"];
        return Admin_Template::factory(Registry::i()->template,'content_default',$data);
    }
}