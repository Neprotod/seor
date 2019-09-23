<?php defined('MODPATH') OR exit();

Admin_Module::implement('controller','auth');

class Controller_Native_Auth_Admin implements I_Auth_Controller_Admin{


    protected $error;
    
    protected $result = NULL;
    
    function __construct(){
        $this->error = Module::factory('error',TRUE);
    }
    
    /**
     * Через что происходит логин
     *
     * @return void 
     */
    function login(){
       ///////////////////////////////////////////////////////////
       if(!$session_admin = Cookie::get('session_admin',FALSE)){
            if(Request::method("post")){
                if($this->check()){
                    unset($this->result["pass"]);
                    Session::instance();
                    Cookie::set('session_admin',Session::instance()->id(),time()+86400);
                    
                    // Устанавливаем пользователя
                    Session::instance()->set("user",$this->result);
                    
                    header("Location: /".Url::instance());
                }
            }
            $root = Admin_Module::mod_path("auth",TRUE);
            echo Admin_View::factory("login","auth",array("root"=>$root,"error"=>$this->error));
            exit;
        }else{
            $session = Session::instance(NULL,$session_admin);
            $this->result = $session->get("user");
            
            if(!isset($this->result["login"]) OR !$this->check_login($this->result["login"])){
                $this->_logout();
            }
            
            return $this->result;
        }
      
    }
    /**
     * 
     *
     * @return void 
     */
    protected function check(){

        $login  = Request::post("login","string");
        $pass  = Request::post("pass","string");
        $holdme  = Request::post("holdme","bool");
        
        $this->check_login($login);
        
        if($this->check_pass($pass)){
            return TRUE;
        }
        return FALSE;
    }
    
    protected function check_login($login){
        if(empty($login)){
            $this->error->set("error","warning",array("tooltip"=>"Не заполнено поле Login","role"=>"login","valid"=>"form_error"));
        }else{
            if($result = Admin_Query::i()->sql("login.get_user",array(":login"=>Utf8::strtolower($login)))){
                $this->result = current($result);
                // С логином все в порядке
                $this->error->set("message","success",array("role"=>"login"));
                
                return TRUE;
            }else{
                 $this->error->set("error","warning",array("title"=>"Логина не существует","role"=>"login","valid"=>"form_error"));
            }
        }
        return FALSE;
    }
    
    protected function check_pass($pass){
        if(empty($pass)){
            $this->error->set("error","warning",array("tooltip"=>"Не заполнено поле пароля","role"=>"pass","valid"=>"form_error"));
        }else{
            if($this->result){
                $pass = md5($pass);
                if($this->result["pass"] != $pass){
                    $this->error->set("error","danger",array("tooltip"=>"Не верный пароль","role"=>"pass","valid"=>"form_error"));
                }else{
                    Registry::i()->admin_user = $this->result;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    function _logout(){
        Cookie::delete('session_admin');
        header("Location: /");
    }
}