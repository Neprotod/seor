<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Workers_Ajax{
    
    function __construct(){
        $this->method = Controller::factory("method","workers");
        $this->account = Controller::factory("account","user");
        $this->user = Module::factory("user",TRUE);
    }
    
    function fetch(){
        Registry::i()->founds["url"] = "ads";
        
        $user = $this->user->get();
        
        $data = $this->method->get_workers(NULL, $user);
        
        $return = array("content" => $data["content"]);
        
        return $return;
    }
}