<?php

class Users_Admin implements I_Module{
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
        $this->account = Controller::factory("account","user");
    }
    
    function inside(){
        Registry::i()->active_menu = "users";
        
        $email = trim(Request::post("email"));
        
        $method = Controller::factory("method","user");
        
        $user = Admin_Query::i()->sql("users.get",array(":where" => "u.email = " . DB::escape($email)),NULL,TRUE);
        
        if(!$user){
            return "Неверный адрес";
        }
        
        $result = $method->token($user, NULL, TRUE);
        
        Request::redirect("/account");
        
        exit;
    }
    function fetch($type = "active"){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        // Активное меню
        Registry::i()->active_menu = "users";
        
        Registry::i()->title = "Пользователи";
        
        $data = array();
        
        $menu = Admin_Model::factory("menu","system");
        
        $menu->attach("users_fetch","","Активные","");
        
        $data["menu"] = $menu->get();
        
        
        $data["content"] = $this->$type();
        
        return Admin_Template::factory(Registry::i()->template,"content_users_fetch",$data);
    }
    
    function user($id){
        Registry::i()->active_menu = "users";
        
        Registry::i()->title = "Редактирование пользователя";
        
        //Создаем отображение
        $xml_pars = "admin_template|default::users_user";
        $xsl_pars = "admin_template|default::users_user";
        
        if(Request::method("post")){
            Query::i()->sql("transaction.start");
            
            $name = Request::post("name");
            $birthday = Request::post("birthday");
            
            $to_account = array(
                               "name" => $name,
                               "birthday" => $birthday,
                          );
            
            Query::i()->sql("update_where",array(
                                                    ":table" => "accounts",
                                                    ":set" => $this->sql->update(",",$to_account),
                                                    ":where" => sprintf("id_user = %s",$id),
                                                ));
            
            $fields = Request::post("fields");
            
            foreach($fields AS $key => $val){
                Query::i()->sql("update",array(
                                                ":table" => "fields_user",
                                                ":set" => $this->sql->update(",",$val),
                                                ":id" => $key,
                                            ));
            }
            
            Query::i()->sql("transaction.commit");
        }
        
        $data = array();

        $data["users"] = Admin_Query::i()->sql("users.get",array(":where" => "u.id = " . $id),NULL,TRUE);
        
        $data["fields"] = $this->account->get_fields($data["users"]["id"]);
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    function active(){
        //Создаем отображение
        $xml_pars = "admin_template|default::users_users";
        $xsl_pars = "admin_template|default::users_users";
        
        $data = array();
        
        if(Request::method("post")){
            if(isset($_POST["deactivation"])){
                $id = $_POST["deactivation"];

                Admin_Query::i()->sql("update",array(
                                                      ":table" => "user",
                                                      ":set" => "status = 0",
                                                      ":id" => $id,
                                                    ));
            }
            if(isset($_POST["activation"])){
                $id = $_POST["activation"];

                Admin_Query::i()->sql("update",array(
                                                      ":table" => "user",
                                                      ":set" => "status = 1",
                                                      ":id" => $id,
                                                    ));
            }
            if(isset($_POST["drop"])){
                $id = $_POST["drop"];

                Admin_Query::i()->sql("delete",array(
                                                      ":table" => "user",
                                                      ":where" => "id",
                                                      ":insert" => sprintf("(%s)",$id),
                                                    ));
            }
        }
        
        $data["users"] = Admin_Query::i()->sql("users.get",array(":where" => 1));

        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}