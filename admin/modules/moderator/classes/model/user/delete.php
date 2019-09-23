<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_User_Delete_Moderator_Admin{
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
    
    function get(){
        if(Request::get("group")){
            return $this->group();
        }
        elseif(Request::get("moder")){
            return $this->moder();
        }else{
            $this->error->set("error","danger",array("message"=>"Нет такой страницы"));
            Registry::i()->errors = $this->error->output();
        }
    }
    
    function moder(){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        if(Request::method("post")){
            if($delete = Request::post("delete")){
                 // Удаляем.
                $ids = array_keys($delete);
                $ids = $this->sql->insert_string($ids);
                $t = Admin_Query::i()->sql("delete", array(
                                                       ":table"=>"admin_user",
                                                       ":where"=>"id",
                                                       ":insert"=>$ids,
                                                        ));
                $message["del"] = "Группы удалены.";
            }
            if(isset($message) AND !empty($message)){
                Cookie::set("success",serialize($message));
                Request::redirect(Url::root(NULL));
            }
            Registry::i()->errors = $this->error->output();
        }
        
        $sort = Request::get("sort",NULL,"id_type");

        $moder = array();
        $moder = Admin_Query::i()->sql("moderator.all_user", array(":order"=>$sort));
        
        $xml_pars = "admin_template|default::moderator_moder_allUser";
        $xsl_pars = "admin_template|default::moderator_delete_moder";
        
        $tech = array(
            "url" => Url::root(FALSE),
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus")),
            "session_id" => Session::i()->id(),
            "action" => Url::root(NULL),
            "site_url" => Url::site("?moder='all'"),
        );
        
        return $this->xml->preg_load($moder,$xml_pars,$xsl_pars,$tech);
    }
    
    function group(){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        $group = Admin_Query::i()->sql("moderator.all_type",NULL,"id");
 
        if(Request::method("post")){
           
            if($delete = Request::post("delete")){
                $error = '';
                $message = array();
                foreach($delete AS $key => $value){
                    $test = Admin_Query::i()->sql("moderator.user", array(
                                                           ":id"=>$value,
                                                           ":where"=>"id_type"
                                                            ));
                    if(!empty($test)){
                        unset($delete[$key]);
                        $group_name = $group[$value]["type"];
                        $group_title = $group[$value]["title"];
                        $error .= "C группкой <b>$group_name ($group_title)</b> связаны модераторы:<br/>";
                        foreach($test AS $user){
                            $login = $user["login"];
                            $display_name = $user["display_name"];
                            $error .= "Модератор <b>$login ($display_name)</b> <br/>";
                        }
                    }
                }
                if(!empty($delete)){
                    
                    // Удаляем.
                    $ids = array_keys($delete);
                    $ids = $this->sql->insert_string($ids); 
                    $t = Admin_Query::i()->sql("delete", array(
                                                           ":table"=>"admin_type",
                                                           ":where"=>"id",
                                                           ":insert"=>$ids,
                                                            ));
                    
                    $message["del"] = "Группы удалены.";
                    
                    
                }
                
                if(!empty($error)){
                    if(!empty($message))
                        $this->error->set("message","success",array("message"=>$message["del"]));
                    $this->error->set("error","danger",array("message"=>rtrim($error,"<br/>")));
                }else{

                    if(isset($message) AND !Arr::emptys($message)){
                        Cookie::set("success",serialize($message));
                        Request::redirect(Url::root(NULL));
                    }
                }
            }
            Registry::i()->errors = $this->error->output();
        }
        
        $xml_pars = "admin_template|default::moderator_group_allGroup";
        $xsl_pars = "admin_template|default::moderator_delete_group";
        
        $tech = array(
            "url" => Url::root(FALSE),
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus")),
            "session_id" => Session::i()->id(),
            "action" => Url::root(NULL),
        );
        
        return $this->xml->preg_load($group,$xml_pars,$xsl_pars,$tech);
    }
}