<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_login_user{

    const VERSION = '1.0.0';
    
    /**
     * @var bool если в TRUE значит авторизация уже была проведена в прошлом
     */
    static $auth = FALSE;
    
    // 1209600  - две недели
    // 2629743  - месяц
    /**
     * @var int сколько живет токен
     */
    static $lifetime = 2629743;
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->method = Controller::factory("method","user");
    }
    
    function login(){
        if(Registry::i()->founds["url"] == "registr"){
            $action = Module::factory("action",TRUE);
            $render = $action->render(Registry::i()->action_list);
            return $action->view($render);
        }
        $data_file = array();
        $data_file["type"] = "user";
        $data_file["file_name"] = "login";
        
        // Подключаем файл
        Registry::i()->data = $data_file;
       
        // Подключаем XML отображение
        $xml_pars = "template|default::user_login";
        $xsl_pars = "template|default::user_login";
        
        $data = array();
        
        if(Request::method("post")){
            $data["email"] = trim(strtolower(Request::post("email")));
            $pass = trim(Request::post("password"));
            $data["password"] = md5($pass);
            if($this->check($data)){
                // Находим пользователя
                if($this->method->token($data)){
                    $this->method->logs_login();
                    Request::redirect(Url::site(NULL,NULL),302);
                }else{
                    $this->error->set("error","danger",array("message"=>"Неверный email или пароль."));
                    
                    $data['error'] = $this->error->output();
                }
            }
            
            $data["password"] = $pass;
        }
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(FALSE),
            "title" => "Войти",
            "regist" => "0",
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    function registr(){
        $data_file = array();
        $data_file["file_name"] = "regist";
        $data_file["style_name"] = "login";
        
        // Подключаем файл
        Registry::i()->data += $data_file;
        
        // Если пришел ключ активации
        if($active = Request::get("active")){
           return $this->active($active);
        }
        
        // Подключаем XML отображение
        $xml_pars = "template|default::user_regist";
        $xsl_pars = "template|default::user_regist";
        
        $data = array();
        
        $data["type"] = Query::i()->sql("user.user_type");
        
        $data["error"] = '';
        
        if(Request::method("post")){
            $data["email"] = trim(strtolower(Request::post("email")));
            $data["password"] = trim(Request::post("password"));
            if($this->check($data)){
                // Проверка на существование
                if($user = $this->method->get_user(array("email" => $data["email"]),FALSE, TRUE)){
                    $this->error->set("error","danger",array("message"=>"Пользователь с таким email уже зарегистрирован."));
                    
                    $data['error'] = $this->error->output();
                }else{
                    // Генерируем ключ активации 
                    $key = md5($data["email"].$data["password"]);
                    
                    $data["site"] = Core::$root_url;
                    $data["key"] = $key;
                    
                    $id_user_type = $this->employer();
                    
                    // Отправляем письмо
                    $mail = Module::factory("mail",TRUE);
                    
                    $mail->driver("smtp");
                    
                    $mail->isHTML(TRUE);
                   
                    $mail->to($data["email"]);
                    $mail->from(NULL,"SEOR");
                    $mail->subject("Регистрация на seor.ua");

                    $mail->view("regist",$data);
                    // Пробуем отправить письмо, если все нормально записываем в базу данных.
                    try{
                        $mail->send();
                        Query::i()->sql("transaction.start");
                        // Добавляем пользователя
                        $id = Query::i()->sql("user.insert",array(
                            ":email" => $data["email"],
                            ":pass" => md5($data["password"]),
                            ":key" => $data["key"],
                            ":id_user_type" => $id_user_type,
                        ));
                        
                        $id = current($id);
                        
                        Query::i()->sql("insert",array(
                            ":table" => "accounts",
                            ":where" => "id_user",
                            ":set" => $this->sql->insert_string((array)$id),
                        ));
                        Query::i()->sql("transaction.commit");
                        Request::redirect(Url::site("success?view=regist",TRUE),302);
                    }catch(Exception $e){
                        Query::i()->sql("transaction.rollback");
                        if(Core::$selected_mode != Core::DEVELOPMENT){
                            $this->error->set("error","warning",array("title"=>"Письмо не отправлено.","message"=>"Попробуйте позже."));
                            $data['error'] = $this->error->output();
                        }else{
                            Core_Exception::handler($e);
                            exit;
                        }
                        
                    }
                }
            }
        }
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(FALSE),
            "employer" => Request::get("employer","int",0),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    protected function check(&$data){
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

        if($t){
            $this->error->set("error","warning",array("message"=>"Неправильно заполнено поле"));

            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>$value["type"]["pattern"]));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Пустое поле не допустимо"));
                }
                
            }  

            $data['error'] = $this->error->output();
            return FALSE;
        }
        return TRUE;
    }
    
    function employer(){
        if(!Request::post("employer")){
            return 1;
        }else{
            if(!Request::post("face")){
                return 4;
            }else{
                return Request::post("company","int");
            }
        }
    }
    function active($active){
        $active = DB::escape($active);
        $where = "u.activation = ".$active . "AND u.status = 1";
        $user = Query::i()->sql("user.get",array(
                            ":where" => $where
                        ), NULL, TRUE);
        if(isset($user["activation"])){
            // Записываем с какого устройства и IP адреса был вход
            $this->method->logs_login($user);
            
            // Записываем ключ активации в лог
            try{
                Query::i()->sql("logs.activation.insert",array(
                    ":id_user" => $user["id"],
                    ":key" => $user["activation"]
                ));
            }catch(Exception $e){
                // Если такой пользователь уже есть, высылаем ошибку.
                Model::factory('exception','system')->set_xml(new Core_Exception("По каким-то причинам этот пользователь уже был активирован. id: <b>:id</b> email: <b>:email</b>",array(
                    ":id" => $user["id"],
                    ":email" => $user["email"]
                    )),array('client'=>'true'));
            }
            // Убираем ключ активации и создаем
            $this->method->token($user);
            
            Query::i()->sql("update",array(
                    ":table" => "user",
                    ":set" => "activation = NULL",
                    ":id" => $user["id"],
            ));
            
            Request::redirect(Url::site("account",TRUE),302);
        }else{
            // Подключаем XML отображение
            $xml_pars = "template|default::user_noactive";
            $xsl_pars = "template|default::user_noactive";

            $where = "activation = $active";
            
            $active = Query::i()->sql("logs.activation.get",array(":where"=>$where));
            
            $tech = array(
                "root" => Registry::i()->root,
                "site" => Url::site(NULL,TRUE),
                "active" => empty($active)?"0":"1",
            );
            
            $data = array();
            return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        }
        
    }
}