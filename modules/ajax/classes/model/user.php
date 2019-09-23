<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_User_Ajax{
    
    function __construct(){
        $this->method = Controller::factory("method","ads");
        $this->account = Controller::factory("account","user");
        $this->user = Module::factory("user",TRUE);
        $this->error = Module::factory("error",TRUE);
    }
    function company_valid_save(){
        $this->method = Controller::factory("method","user");
        $this->dir = Model::factory("filemanager_dir","filesystem");
        
        $user = $this->user->get();
        
        $to_insert = array();
        $to_insert["number"] = Request::post("number");
        
        if(empty($_FILES)){
            $this->error->set("error","warning",array("message"=>"Вы должны загрузить хотя бы один скан."));
        }
        if(empty($to_insert["number"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали регистрацонный номер."));
        }
        
        $data = array();
        
        $data["error"] = $this->error->output();
        if(!empty($data["error"])){
            return $data;
        }else{
            unset($data["error"]);
        }
        // Проверка данных пройдена, теперь  сохраняем результат
        try{
            $path = $this->method->media_user_path()."verification";
            if(is_dir($path))
                $this->dir->unlink_directory($path);
            $this->dir->create_dir($path);
            
            $to_insert["path"] = $path;
            
            foreach($_FILES AS $file){
                if(is_array($file))
                    foreach($file["name"] AS $key => $name){
                        $file_name = $name;
                        $tmp_name = $file["tmp_name"][$key];
                        move_uploaded_file($tmp_name,$path.'/'.$file_name);
                        $to_insert["file"][] = $file_name;
                    }
            }
            // Записываем в базу данных
            Query::i()->sql("transaction.start");
            Query::i()->sql("accounts.verification.insert",array(
                                                                ":id_user" => $user["id"],
                                                                ":detail" => json_encode($to_insert),
                                                           ));
            Query::i()->sql("transaction.commit");
            
            $data["complete"] = 1;
            
            $this->error->set("message","info",array("message"=>"Профиль передан на подтверждение."));
            $this->error->save_cookie();
            return $data;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            $this->error->set("error","warning",array("message"=>"Техническая ошибка. Тех поддержка уже уведомлена."));
            Core_Exception::client($e);
            $data["error"] = $this->error->output();
  
            if(is_dir($path))
                $this->dir->unlink_directory($path);
            
            return $data;
        }
        
    }
    function user_valid_save(){
        $this->method = Controller::factory("method","user");
        $this->dir = Model::factory("filemanager_dir","filesystem");
        
        $user = $this->user->get();
        
        $to_insert = array();
        $to_insert["name"] = Request::post("name");
        $to_insert["serial"] = Request::post("serial");
        $to_insert["number"] = Request::post("number");
        $to_insert["date"] = Request::post("date");
        $to_insert["issued"] = Request::post("issued");
        
        if(empty($_FILES)){
            $this->error->set("error","warning",array("message"=>"Вы должны загрузить хотя бы один скан."));
        }
        if(empty($to_insert["name"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали имя."));
        }
        if(empty($to_insert["serial"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали серею паспорта."));
        }
        if(empty($to_insert["number"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали номер паспорта."));
        }
        if(empty($to_insert["date"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали дату выдачи."));
        }
        if(empty($to_insert["issued"])){
            $this->error->set("error","warning",array("message"=>"Вы не указали кем выдан."));
        }
        
        $data = array();
        
        $data["error"] = $this->error->output();
        if(!empty($data["error"])){
            return $data;
        }else{
            unset($data["error"]);
        }
        // Проверка данных пройдена, теперь  сохраняем результат
        try{
            $path = $this->method->media_user_path()."verification";
            if(is_dir($path))
                $this->dir->unlink_directory($path);
            $this->dir->create_dir($path);
            
            $to_insert["path"] = $path;
            
            foreach($_FILES AS $file){
                if(is_array($file))
                    foreach($file["name"] AS $key => $name){
                        $file_name = $name;
                        $tmp_name = $file["tmp_name"][$key];
                        move_uploaded_file($tmp_name,$path.'/'.$file_name);
                        $to_insert["file"][] = $file_name;
                    }
            }
            // Записываем в базу данных
            Query::i()->sql("transaction.start");
            Query::i()->sql("accounts.verification.insert",array(
                                                                ":id_user" => $user["id"],
                                                                ":detail" => json_encode($to_insert),
                                                           ));
            Query::i()->sql("transaction.commit");
            
            $data["complete"] = 1;
            
            $this->error->set("message","info",array("message"=>"Профиль передан на подтверждение."));
            $this->error->save_cookie();
            return $data;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            $this->error->set("error","warning",array("message"=>"Техническая ошибка. Тех поддержка уже уведомлена."));
            Core_Exception::client($e);
            $data["error"] = $this->error->output();
  
            if(is_dir($path))
                $this->dir->unlink_directory($path);
            
            return $data;
        }
        
    }
    function verification(){
        $data = array();
        $data["title"] = Str::__("Подтверждение данных");
        $data["content"] = Str::__("Вы хотите удалить аккаунт?.");
        
        $user = $this->user->get();
        
        $id_type = $user["id_user_type"];
        
        $content_data = array();
        $content_data["user"] = $user;
        
        
        if($id_type == 1 OR $id_type == 4){
            $data["content"] = View::factory("user_verification_nolegal","ajax", $content_data);
        }else{
            $data["content"] = View::factory("user_verification_legal","ajax", $content_data);
        }
        
        return $data;
    }
    function drop(){
        
        $data = array();
        $drop = Request::post("drop","int", false);
        
        
        $data["title"] = Str::__("Удаление аккаунта");
        $data["content"] = Str::__("Вы хотите удалить аккаунт?.");
        
        $user = $this->user->get();
        
        if($drop){
            // Проверка пройдена, генерируем ключ.
            $key = md5($user["id"]);
            $data["key"] = $key;
            $data["id"] = $user["id"];
            $data["user"] = $user;
            
            $session = Registry::i()->session;
            
            $array = $session->get("action", array());
            $array["drop_user"] = array(
                                "key" => $key,
                                "user_id" => $user["id"]
                            );
            $session->set("action", $array);
            
            // Отправляем письмо.
            $mail = Module::factory("mail",TRUE);
            
            $mail->driver("smtp");
            
            $mail->isHTML(TRUE);
           
            $mail->to($user["email"]);
            $mail->from(NULL,"SEOR");
            $mail->subject("Подтверждение удаления аккаунта.");

            $mail->view("change_drop",$data);
            
            try{
                $mail->send();
                $this->error->set("message","success",array("message" => "Письмо с подтверждением удаления аккаунта было отправлено вам на почту, подтвердите его."));
                
                $this->error->save_cookie();
            }catch(Exception $e){
                Core_Exception::handler($e);
                $this->error->set("error","danger",array("message" => "Удаление аккаунта не прошла, попробуйте позже. Тех поддержка уже уведомлена."));
                $this->error->save_cookie();
            }
            
        }
        return $data;
    }
}