<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Notification_User{

    const VERSION = '1.0.0';
    
    public $notification_type;
    public $preset = array(
                        "lowdays" => array(
                                    "title" => "Продлите аккаунт.",
                                    "type" => "finance",
                        ),
                        "verylowdays" => array(
                                    "title" => "Аккаунт не активен.",
                                    "type" => "finance",
                        ),
                        "rejected" => array(
                                    "title" => "Ваше объявление не прошло модерацию.",
                                    "type" => "default",
                        ),
                        "verification" => array(
                                    "title" => "Ваши данные не прошли модерацию.",
                                    "type" => "default",
                        ),
                    );
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        
        $this->notification_type = Query::i()->sql("user.notification.get_type",NULL,"id");
        
        foreach($this->notification_type AS &$value){
            $value = $value["type"];
        }
    }
    
    function set_note($user, $title, $content, $type){
        if(!isset($user["id"])){
            throw new Core_Exception("Нет id в массиве пользователя, отправка уведомления невозможна.");
        }
        
        if(is_int($type)){
            if(!isset($this->notification_type[$type]))
                throw new Core_Exception("Нет такого типа уведомлений <b>:type</b>",array(":type"=>$type));
        }else{
            if(!$id = array_search($type, $this->notification_type)){
                throw new Core_Exception("Нет такого типа уведомлений <b>:type</b>",array(":type"=>$type));
            }
            $type = $id;
        }
        
        $send = array();
        
        $send["id_user"] = $user["id"];
        $send["title"] = $title;
        $send["content"] = $content;
        $send["id_type"] = $type;
        
        $set = $this->sql->insert_string($send);
        try{
            // Начинаем транзакцию
            Query::i()->sql("transaction.start");
            
            Query::i()->sql("insert",array(
                                            ":table" => "notification",
                                            ":where" => implode(",",array_keys($send)),
                                            ":set"   => $set,
                                        ));
            // Завершаем транзакцию
            Query::i()->sql("transaction.commit");
        }catch(Exception $e){
            // Что-то пошло не так, возвращает все
            Query::i()->sql("transaction.rollback");
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
            return FALSE;
        }
        return TRUE;
    }
    
    function presets($user, $preset, $data = array()){
        if(!isset($this->preset[$preset])){
            throw new Core_Exception("Нет такого прессета как :preset",array(":preset"=>$preset));
        }
        if(isset($data["title"]))
            $title = $data["title"];
        else
            $title = $this->preset[$preset]["title"];
        $type = $this->preset[$preset]["type"];
        $message = View::factory("notification_".$preset,"user",$data);
        
        $this->set_note($user, $title, $message, $type);
    }
}