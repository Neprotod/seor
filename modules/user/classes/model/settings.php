<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Settings_User{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->account = Controller::factory("account","user");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->error = Module::factory("error",TRUE);
    }
    
    function fetch(){
        $this->error->cookie(FALSE);
        
        $user = Registry::i()->user;
         //Создаем отображение
        $xml_pars = "template|default::user_settings";
        $xsl_pars = "template|default::user_settings";
        
        $data = array();
        $data["user"] = $user;
        if(Request::method("post")){
            $submit = Request::post("submit");

            if($submit == 1){
                $new_email = trim(strtolower(Request::post("new_email")));

                $data["user"]["new_email"] = $new_email;
                if(!$this->test_email($new_email)){
                    $this->error->set("error","danger",array("message" => "Нельзя сменить email"));
                }else{
                    // Проверка пройдена, генерируем ключ.
                    $key = md5($new_email);
                    $data["key"] = $key;
                    $data["id"] = $user["id"];
                    
                    $session = Registry::i()->session;
                    
                    $array = $session->get("action", array());
                   
                    $array["new_email"] = array(
                        "key" => $key,
                        "email" => $new_email,
                        "user_id" => $user["id"]
                    );
                    $session->set("action", $array);
                    
                     // Отправляем письмо.
                    $mail = Module::factory("mail",TRUE);
                    
                    $mail->driver("smtp");
                    
                    $mail->isHTML(TRUE);
                   
                    $mail->to($user["email"]);
                    $mail->from(NULL,"SEOR");
                    $mail->subject("Подтверждение смены email.");

                    $mail->view("change_email",$data);
                    
                    try{
                        $mail->send();
                        $this->error->set("message","success",array("message" => "Письмо с подтверждением смены email было отправлено вам на старую почту, подтвердите его."));
                        
                        $this->error->save_cookie();
                        Request::redirect(Url::root(FALSE));
                    }catch(Exception $e){
                        Core_Exception::handler($e);
                        $this->error->set("error","danger",array("message" => "Смена почты не прошла, попробуйте позже. Тех поддержка уже уведомлена."));
                    }
                    
                }
            }elseif($submit == 2){
                $password        = trim(Request::post("password"));
                $new_password    = trim(Request::post("new_password"));
                $repeat_password = trim(Request::post("repeat_password"));
                
                $data["password"]["password"] = $password;
                $data["password"]["new_password"] = $new_password;
                $data["password"]["repeat_password"] = $repeat_password;
                
                $flag = 0;
                if(md5($password) != $user["pass"]){
                    $flag = 1;
                    $this->error->set("error","danger",array("select" => 1, "role" => "password", "tooltip" => "Неправильный пароль"));
                }else{
                    if(empty($new_password)){
                        $flag = 1;
                        $this->error->set("error","danger",array("select" => 1, "role" => "repeat_password", "tooltip" => "Пароль не совпадает"));
                    }
                    elseif($t = $this->valid(array("password" => $new_password))){
                        $flag = 1;
                        $t = Arr::flatten($t);

                        $this->error->set("error","danger",array("select" => 1, "role" => "new_password", "tooltip" => current($t)));
                    }
                    elseif($new_password != $repeat_password){
                         $flag = 1;
                         $this->error->set("error","danger",array("select" => 1, "role" => "repeat_password", "tooltip" => "Пароль не совпадает"));
                    }else{
                        // Проверка пройдена, генерируем ключ.
                        $key = md5($password.$new_password);
                        $data["key"] = $key;
                        $data["id"] = $user["id"];
                        
                        $session = Registry::i()->session;
                        
                        $array = $session->get("action", array());
                       
                        $array["pass_replace"] = array(
                            "key" => $key,
                            "pass" => $new_password,
                            "user_id" => $user["id"]
                        );
                        $session->set("action", $array);
                        
                        // Отправляем письмо.
                        $mail = Module::factory("mail",TRUE);
                        
                        $mail->driver("smtp");
                        
                        $mail->isHTML(TRUE);
                       
                        $mail->to($user["email"]);
                        $mail->from(NULL,"SEOR");
                        $mail->subject("Подтверждение смены пароля.");

                        $mail->view("change_pass",$data);
                        
                        try{
                            $mail->send();
                            $this->error->set("message","success",array("message" => "Письмо с подтверждением смены пароля было отправлено вам на почту, подтвердите его."));
                            
                            $this->error->save_cookie();
                            Request::redirect(Url::root(FALSE));
                        }catch(Exception $e){
                            Core_Exception::handler($e);
                            $this->error->set("error","danger",array("message" => "Смена пароля не прошла, попробуйте позже. Тех поддержка уже уведомлена."));
                        }
                    }
                }
                
                
                if($flag){
                    $this->error->set("error","danger",array("message" => "Неправильный пароль."));
                }
            }
        }
        // Ошибки
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            array("error" => $error)
        );
        /*echo "<pre>";
        var_dump($data);
        exit;
        echo $this->xml->preg_load($data,$xml_pars,null,$tech);
        exit;*/
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    protected function test_email($new_email){

        if($t = $this->valid(array("email" => $new_email))){
            $t = Arr::flatten($t);

            $this->error->set("error","danger",array("select" => 1, "role" => "new_email", "tooltip" => current($t)));
        }
        
        $email = Query::i()->sql("accounts.settings.email_test",array(":email"=>$new_email));
        
        if($email){
            $this->error->set("error","danger",array("select" => 1, "role" => "new_email", "tooltip" => "Такой пользователь уже зарегистрирован"));
            return FALSE;
        }
        
        return TRUE;
    }
    protected function valid($data){
        $validator = Model::factory("validator","system");
        $valid = array(
                    "email" => array(
                        "required" => TRUE,
                        "pattern" => array("^.{1,}@.{1,}$","Неправильный формат почты")
                    ),
                    "password" => array(
                        "type" => "str",
                        "required" => TRUE,
                        "pattern" => array("^[a-zA-Z0-9_-]{4,}$","Пароль должен быть минимум 4 символа, допускается только латиница и цифры")
                    )
        );
        // Проверяем форму на ошибки.
        $t = $validator->valids($valid,$data);
        
        return $t;
    }
}