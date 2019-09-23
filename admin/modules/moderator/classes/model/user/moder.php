<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 */
class Model_User_Moder_Moderator_Admin{
    public $error;
    public $xml;
    public $validator;
    public $sql;
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
    }
    /**
     * Перенаправляет, на список модераторов или на модератора
     *
     * @return  string
     */
    function get(){
        if($id = Request::get("user")){
            return $this->user($id);
        }else{
            return $this->get_all();
        }
    }
    
    /**
     * Перенаправляет, на список модераторов
     *
     * @return  string
     */
    function get_all(){
        
        // Запоминать сортировку
        $sort = Request::get("sort",NULL,"id");
        
        $moder = array();
        $moder = Admin_Query::i()->sql("moderator.all_user", array(":order"=>$sort));
        

        
        $xml_pars = "admin_template|default::moderator_moder_allUser";
        
        $tech = array(
            "url" => Url::root(FALSE),
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus"))
        );

        return $this->xml->preg_load($moder,$xml_pars,$xml_pars,$tech);
    }
    
    /**
     * Перенаправляет, на изменение модератора или создание модератора
     *
     * @param   string  ID группы или "new" 
     * @return  string
     */
    function user($id){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
   
        $moder = array();
        $moder["moder"] = array();
        $to_active = NULL;
        if($id != "new"){
            $moder["moder"] = Admin_Query::i()->sql("moderator.user", array(
                                                                           ":id"=>$id,
                                                                           ":where"=>"id"
                                                                            ),"id", TRUE);
            $to_active = $moder["moder"]["activation_key"];
            unset($moder["moder"]["pass"]);
            unset($moder["moder"]["activation_key"]);
        }
        $moder["type"] = Admin_Query::i()->sql("moderator.all_type",NULL,"id");
        
        $xml_pars = "admin_template|default::moderator_moder_user";
        
        $tech = array(
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "action" => Url::root(NULL),
            "activation_key" => $to_active,
        );
        
        if(Request::method("post")){
            $this->post($moder,$to_active);
        }

        return $this->xml->preg_load($moder,$xml_pars,$xml_pars,$tech);
    }
    /**
     * Обрабатывает Post запрос
     *
     * @param   array  отобранные данные которые будут дополнятся
     * @param   array  ключ активации
     * @return  void
     */
    protected function post(&$found,$to_active){
        $moder = Request::post("moder");
        $moder = Arr::replace_value($moder,"",NULL);
        $original_moder = $found["moder"];
        
        $pass = array();
       
        $test = array("pass","pass_check");
        
        // Убираем пароль из основного массива и добавляем
        // для дальнейшей проверки
        foreach($moder AS $key => $value){
            if(in_array($key, $test)){
                $pass[$key] = $value;
                unset($moder[$key]);
            }
        }
        
        $update = array();
        $update = Arr::array_diff_assoc($moder, $original_moder);
        
        // Проверка пароля.
        if(!empty($pass["pass"])){
            
            // Если пароль не совпадает, это уже ошибка.
            if($pass["pass"] === $pass["pass_check"]){
                // Если совпадает, то отправляем на вывод в форме.
                $moder += $pass;
                unset($pass["pass_check"]);

                $valid = array(
                        "pass" => array(
                            "type" => "str",
                            "pattern" => "^[a-zA-Z0-9]{5,}$",
                        )
                );
                // Пропускаем через валидатор, что бы проверить по регулярке.
                if($this->validator->valids($valid,$pass)){
                    $this->error->set("error","danger",array("role"=>"pass","select"=>1,"tooltip"=>"Пароль должен быть минимум 5 символов, латинскими буквами и цифрами"));
                    $this->error->set("error","danger",array("role"=>"pass_check","select"=>1));
                }else{
                    // Если все нормально, то тогда хешируем и отправляем на обновление.
                    $pass["pass"] = md5($pass["pass"]);
                    $update = Arr::merge($update,$pass);
                }
            }else{
                $this->error->set("error","danger",array("role"=>"pass","select"=>1,"tooltip"=>"Пароли не совпадают"));
                $this->error->set("error","danger",array("role"=>"pass_check","select"=>1));
            }
        } 
        
        // Сохраняем для вывода в форме.
        $found["moder"] = $moder;
        
        $valid = array(
                "email" => array(
                    "required" => TRUE,
                    "pattern" => "^.{0,}@.{0,}\..{0,}$",
                ),
                "login" => array(
                    "type" => "str",
                    "required" => TRUE,
                    "pattern" => "^[a-zA-Z0-9]{3,}$",
                ),
                "display_name" => array(
                    "type" => "str",
                    "required" => TRUE,
                    "pattern" => "^[а-яА-Яa-zA-Z0-9 _-]{3,}$",
                ),
                "id_type" => array(
                    "required" => TRUE,
                )
        ); 

        // Проверяем на ошибки.
        $t = $this->validator->valids($valid,$moder);
        
        // Заготовки сообщений.
        $to_message = array();
        $to_message["active"] = array(
                            "title"=>"Был добавлен код активации.",
                            "message"=>"Пользователь становится неактивным, ему на почту выслан ключ активации, не включайте его, он автоматически включится когда пройдет активацию"
                            );
        
        // Если есть ошибки, то не добавляем в базу.
        if($t){
            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Неправильно заполненное поле"));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }
            }
        }else{
            // Если есть id значит это обновление
            if(isset($moder["id"])){
                if(!empty($update)){
                    try{
                        if(isset($update["active"])){
                            if(!isset($update["pass"])){
                                throw new Exception();
                            }else{
                                $update["status"] = 0;
                                
                                $login = $moder["login"];
                                
                                $activation_key = $update["pass"] . $login;
                                
                                $activation_key = md5($activation_key);
                               
                                $update["activation_key"] = $activation_key;
                                $message[] = $to_message["active"];
                                unset($update["active"]);
                            }
                            
                        }else{
                            if(!empty($to_active) AND (isset($update["status"]) AND $update["status"])){
                                $update["activation_key"] = NULL;
                            }
                        }
                        $set = $this->sql->update(",",$update);
                        Admin_Query::i()->sql("update",array(
                                                              ":set" => $set,
                                                              ":id" => $moder["id"],
                                                              ":table" => "admin_user"
                                                            ));
                        
                        $message[] = "Модератор был обновлен.";
                    }catch(Exception $e){
                        echo $e->getMessage();
                        exit;
                        $this->error->set("error","danger",array("message"=>"Нельзя назначать ключ активации без смены пароля.","role"=>"pass","select"=>1));
                         
                        $this->error->set("error","danger",array("role"=>"pass_check","select"=>1));
                    }
                }
            }else{
                // Если не id значит добавляем
                
                // Нужно проверить существует ли пароль в update
                if(isset($update["pass"])){
                    $message[] = "Модератор был добавлен.";
                    
                    if(isset($update["active"])){
                        $update["status"] = 0;
                        $activation_key = $update["pass"] . $update["login"];
                        $activation_key = md5($activation_key);
                        $update["activation_key"] = $activation_key;
                        $message[] = $to_message["active"];
                    }else{
                        $update["activation_key"] = NULL;
                    }
                    
                    $id = Admin_Query::i()->sql("moderator.insert_user",array(
                                                          ":login" => $update["login"],
                                                          ":pass" => $update["pass"],
                                                          ":email" => $update["email"],
                                                          ":activation_key" => $update["activation_key"],
                                                          ":status" => $update["status"],
                                                          ":display_name" => $update["display_name"],
                                                          ":id_type" => $update["id_type"],
                                                        ));
                    $moder["id"] = current($id);
                    
                }else{
                     $this->error->set("error","danger",array("message"=>"Нельзя создавать пользователя без пароля.","role"=>"pass","select"=>1,"tooltip"=>"Пароль должен быть минимум 5 символов, латинскими буквами и цифрами"));
                     
                     $this->error->set("error","danger",array("role"=>"pass_check","select"=>1));
                }
                //exit;
                
            }
            
            //////////////////
            // КЛЮЧ АКТИВАЦИИ, отправка на почту должна быть где-то тут.
            /////////////////
            
            if(isset($message) AND !Arr::emptys($message)){
                $query = array(
                    "user" => $moder["id"]
                );
                Cookie::set("success",serialize($message));
                Request::redirect(Url::query_root($query,FALSE));
            }
        }
        Registry::i()->errors = $this->error->output();
    }
    /**
     * Написать функцию активации, одна должна удалять ключ из базы и менять статус на 1.
     */
    function activate_user(){
        
    }
}