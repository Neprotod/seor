<?php

class Support_Admin{
    
    public $date_format = "%Y-%m-%d'";
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function fetch($type = "active"){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        // Активное меню
        Registry::i()->active_menu = "support";
        
        Registry::i()->title = "Тех поддержка";
        
        $data = array();
        
        $menu = Admin_Model::factory("menu","system");
        
        $menu->attach("support_fetch","","Активные","");
        $menu->attach("support_fetch","arch","Отвеченные","");
        
        $data["menu"] = $menu->get();
        
        
        $data["content"] = $this->$type();
        
        return Admin_Template::factory(Registry::i()->template,"content_support_fetch",$data);
    }
    
    protected function active(){
        // Получаем все не отвеченные обращения, которые еще не видел администратор.
        $appeal = Admin_Query::i()->sql("support.get_all",array(":where"=>">"));
        
        $count = count($appeal);
        //Создаем отображение
        $xml_pars = "admin_template|default::support_support";
        $xsl_pars = "admin_template|default::support_support";
        
        $data = array();
        $data["appeal"] = $appeal;
        $tech = array(
            "count" => $count,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    protected function arch(){
        // Получаем все не отвеченные обращения, которые еще не видел администратор.
        $appeal = Admin_Query::i()->sql("support.get_all",array(":where"=>"="));
        
        $count = count($appeal);
        //Создаем отображение
        $xml_pars = "admin_template|default::support_support";
        $xsl_pars = "admin_template|default::support_support";
        
        $data = array();
        $data["appeal"] = $appeal;
        $tech = array(
            "count" => $count,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "acrh" => 1,
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    protected function get(){
        $id = Request::get("id");
        
        //Создаем отображение
        $xml_pars = "admin_template|default::support_support";
        $xsl_pars = "admin_template|default::support_appeal";
        
        $data = array();
        
        $data["appeal"] = Query::i()->sql("support.get",array(
                                                         ":where" => sprintf("id = %s",DB::escape($id))
                                                      ),"id");                       
        
        $appeal = current($data["appeal"]);

        $user_id = $appeal["id_user"];
        
        // Получаем пользователя
        $user = Controller::factory("method","user")->get_user(array("id"=>$user_id),TRUE);
        
        // Обрабатываем пост запрос.
        if(Request::method("post")){
            Controller::factory("support","user")->message_input($id,Request::post("message"),Registry::i()->auth->user["id"]);
            
            // Отправляем письмо
            $mail = Module::factory("mail",TRUE);
            
            $mail->driver("smtp");
            
            $mail->isHTML(TRUE);
            $mail->to($user["email"]);
            $mail->from(NULL,"SEOR");
            $mail->subject("Ответ службы поддержки");
            
            $mail->view("support",array(
                    "id"=>$id
            ));
            
            $mail->send();
            
            Request::redirect(Url::site(NULL,NULL),302);
        }
        
        $data["message"] = Query::i()->sql("support.message",array(
                                                                ":id_accounts_support" => $id
                                                            ));
        // Обнуляем просмотры у админа
        Query::i()->sql("update",array(
                                    ":table" => "accounts_support",
                                    ":set" => "new_admin = 0",
                                    ":id" => $id,
                                ));
        
        $data["user"] = $user;
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}