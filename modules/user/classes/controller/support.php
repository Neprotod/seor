<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Support_User{

    const VERSION = '1.0.0';
    
    /**
     * @var bool если в TRUE значит авторизация уже была проведена в прошлом
     */
    static $auth = FALSE;
    
    function __construct(){
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
    }
    
    function fetch(){
    }
    
    function message_input($id_accounts_support, $message, $id_admin_user = NULL){
        $for_message = array();
        
        $for_message["id_accounts_support"] = $id_accounts_support;
        $for_message["message"] = $message;
        if(isset($id_admin_user)){
            $for_message["id_admin_user"] = $id_admin_user;
            $for_message["seen"] = "2";
        }
        
        $table = implode(",",array_keys($for_message));
        $set = $this->sql->insert_string($for_message);
        
        return Query::i()->sql("insert",array(
                                ":table" => "support_message",
                                ":where" => $table,
                                ":set" => $set
                            ));
    }
}