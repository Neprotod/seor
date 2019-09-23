<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_User_Action{

    const VERSION = '1.0.0';
    
    public $data = array();
    public $path = '';
    public $action = array();
    
    function __construct(){
        $this->user = Controller::factory("method","user");
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        
        
        // Определяем стили.
        $data_file = array();
        $data_file["type"] = "action";
        $data_file["file_name"] = "change";
        
        if(Registry::i()->founds["id_content_type"]){
            $data_file["content_type"] = Registry::i()->founds["id_content_type"];
        }
        
                // Определяем подключаемый файл
        $template = Model::factory('template','system');
        $this->path = $template->path($data_file);
        
        $style = Model::factory('style','system');
        $style->init($data_file);
    }

    function change(){
        $xml_pars = "template|default::action_change";
        $xsl_pars = "template|default::action_change";
        
        $key = Request::get("key");
        $id = Request::get("id");
        
        if($id){
            $action = Query::i()->sql("accounts.settings.session_action",array(":id_user" => $id),NULL, TRUE);
            if($action){
                $this->action = json_decode($action["value"], true);
                
                if($found = Arr::search(array("key" => $key),$this->action)){
                    $method = key($found);
                    unset($this->action[$method]);
                    
                    $current = current($found);
                    
                    $this->$method($current);
                    
                    $this->action = json_encode($this->action);
                    
                    Query::i()->sql("update_where",array(
                                        ":table" => "session",
                                        ":set" => sprintf("value = %s",DB::escape($this->action)),
                                        ":where" => sprintf("id_user = %s AND name = 'action'",DB::escape($id)),
                                    ));
                }else{
                   $this->data["error"] = 1; 
                   $this->data["title"] = Str::__("Событие не найдено.");
                   $this->data["message"] = Str::__("Ничего не произошло, возможно событие уже произашло, или ссылка слишком старая.");
                }
            }
        }
        
        
        $data = array();

        $data["content"] = $this->xml->preg_load($this->data,$xml_pars,$xsl_pars);
        
        return  Template::factory(Registry::i()->template['name'],$this->path,$data);
    }
    
    protected function pass_replace($found){
        $id = $found["user_id"];
        $pass = $found["pass"];
        
        $this->data["title"] = Str::__("Изменение пароля пользователя.");
        $this->data["message"] = Str::__("Пароль изменён. Вам нужно заново <a href='/account'>авторизироваться</a>.");
        
        try{
            Query::i()->sql("transaction.start");
            // Удаляем токен
            Query::i()->sql("delete",array(
                                        ":table" => "tokens",
                                        ":where" => "id_user",
                                        ":insert" => sprintf("(%s)",DB::escape($id)),
                                    ));
            Query::i()->sql("update",array(
                                        ":table" => "user",
                                        ":set" => sprintf("pass = %s",DB::escape(md5($pass))),
                                        ":id" => $id,
                                    ));

            Query::i()->sql("transaction.commit");
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $this->data["error"] = 1;
            $this->data["message"] = Str::__("Пароль не смог изменится, тех поддержка уже уведомлена, желательно напиши в <a href='/account/support'>тех поддержку аккаунта.</a>",array(":email" => $email));
        }
    }    
    protected function drop_user($found){
        $id = $found["user_id"];
        
        $this->data["title"] = Str::__("Удаление аккаунта.");
        $this->data["message"] = Str::__("Аккаунт удален, письмо с активацией аккаунта выслано вам на почту.");

        try{
            $data = array();
            $user = $this->user->get_user(array("id" => $id), TRUE);
            
            $data["user"] = $user;
            
            Query::i()->sql("transaction.start");
            
            Query::i()->sql("update",array(
                                            ":table" => "user",
                                            ":set"   => "status = 0",
                                            ":id" => $user["id"],
                                        ));
                                        
            Query::i()->sql("update_where",array(
                                        ":table" => "accounts",
                                        ":set"   => "seor = 0, days = 0, clicks = 0, ads = 0, took_days = NULL, expiration = NULL",
                                        ":where" => sprintf("id_user = %s",DB::escape($user["id"])),
                                    ));
            Query::i()->sql("delete",array(
                                    ":table" => "tokens",
                                    ":where" => "id_user",
                                    ":insert" => sprintf("(%s)",DB::escape($user["id"])),
                                ));
            
            $insert = array(
                            "id_user" =>$user["id"],
                            "seor" =>$user["seor"],
                            "days" =>$user["days"],
                            "clicks" =>$user["clicks"],
                            "ads" =>$user["ads"],
                        );
            
            $set = $this->sql->insert_string($insert);
            
            Query::i()->sql("insert",array(
                                    ":table" => "logs_drop",
                                    ":where" => "id_user, seor, days, clicks, ads",
                                    ":set" => $set,
                                ));
            

            $key = md5($user["id"].time());
            $data["key"] = $key;
            $data["id"] = $user["id"];
            
            $array = $this->action;             
            $array["activation_user"] = array(
                                "key" => $key,
                                "user_id" => $user["id"]
                            );
            $this->action = $array;
            
            $mail = Module::factory("mail",TRUE);
            
            $mail->driver("smtp");
            
            $mail->isHTML(TRUE);
           
            $mail->to($user["email"]);
            $mail->from(NULL,"SEOR");
            $mail->subject("Восстановление аккаунта.");

            $mail->view("change_activation",$data);
            
            $mail->send();

            Query::i()->sql("transaction.commit");
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $this->data["error"] = 1;
            $this->data["message"] = Str::__("Аккаунт не удален, тех поддержка уже уведомлена, желательно напиши в <a href='/account/support'>тех поддержку аккаунта.</a>",array(":email" => $email));
        }
    }
    protected function activation_user($found){
        $id = $found["user_id"];

        $this->data["title"] = Str::__("Аккаунт активирован.");
        $this->data["message"] = Str::__("Вам нужно заново <a href='/account'>авторизироваться</a>.");
        
        try{
            $data = array();
            $user = $this->user->get_user(array("id" => $id), TRUE);
            
            $data["user"] = $user;
            
            Query::i()->sql("transaction.start");
            
            Query::i()->sql("update",array(
                                            ":table" => "user",
                                            ":set"   => "status = 1",
                                            ":id" => $id,
                                        ));

            Query::i()->sql("transaction.commit");
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $this->data["error"] = 1;
            $this->data["message"] = Str::__("Не удалось активировать аккаунт, тех поддержка уже уведомлена, желательно напиши в <a href='/account/support'>тех поддержку аккаунта.</a>",array(":email" => $email));
        }
        
    }
    protected function new_email($found){
        $id = $found["user_id"];
        $email = $found["email"];
        
        $this->data["title"] = Str::__("Изменение почты пользователя.");
        $this->data["message"] = Str::__("Почта изменена на <b>:email</b>. Вам нужно заново <a href='/account'>авторизироваться</a>.",array(":email" => $email));
        try{
            Query::i()->sql("transaction.start");
            // Удаляем токен
            Query::i()->sql("delete",array(
                                        ":table" => "tokens",
                                        ":where" => "id_user",
                                        ":insert" => sprintf("(%s)",DB::escape($id)),
                                    ));
            Query::i()->sql("update",array(
                                        ":table" => "user",
                                        ":set" => sprintf("email = %s",DB::escape($email)),
                                        ":id" => $id,
                                    ));

            Query::i()->sql("transaction.commit");
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $this->data["error"] = 1;
            $this->data["message"] = Str::__("Почта не смогла изменится, тех поддержка уже уведомлена, желательно напиши в <a href='/account/support'>тех поддержку аккаунта.</a>",array(":email" => $email));
        }
    }
}