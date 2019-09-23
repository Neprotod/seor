<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Support_User{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->support = Controller::factory("support","user");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
    }
    
    function fetch(){
        $user = Registry::i()->user;
         //Создаем отображение
        $xml_pars = "template|default::user_support";
        $xsl_pars = "template|default::user_support";
        
        $data = array();
        // Находим список обращений
        $where = (Request::get("type") == "arch")
            ? "id_user = :id_user AND (NOW() > last_activ + INTERVAL 7 DAY OR status = 0)"
            : "id_user = :id_user AND (NOW() < last_activ + INTERVAL 7 DAY AND status = 1)";
        
        $data["appeal"] = Query::i()->sql("support.get",array(
                                                         ":where" => Str::__($where,array(":id_user" => $user["id"]))
                                                      ),"id");

        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    function appeal($id = NULL){
        if(empty($id)){
            Request::redirect(Url::site("account/support",TRUE),302);
        }
        
        $user = Registry::i()->user;
        
        if(Request::method("post")){
            $this->support->message_input($id,Request::post("message"));
            Request::redirect(Url::site(NULL,FALSE),302);
        }
        
        
        //Создаем отображение
        $xml_pars = "template|default::user_support";
        $xsl_pars = "template|default::user_support_appeal";
        
        $data = array();
        
        $data["appeal"] = Query::i()->sql("support.get",array(
                                                         ":where" => sprintf("id = %s",DB::escape($id))
                                                      ),"id");                                         
        if(empty($data["appeal"])){
            Request::redirect(Url::site("account/support",TRUE),302);
        }                                              
        $data["message"] = Query::i()->sql("support.message",array(
                                                                ":id_accounts_support" => $id
                                                            ));
          

        // Обнуляем просмотры у пользователя
        Query::i()->sql("update",array(
                                    ":table" => "accounts_support",
                                    ":set" => "new_user = 0",
                                    ":id" => $id,
                                ));
                                
        $data["user"] = $user;
        
        // Создаем логотип
        $image = Module::factory("image",TRUE);
        $logo_path = Controller::factory("method","user")->media_user_path() . "logo";
        $logo_resize = $logo_path . "/resize";
            
        $image_settings = array(
            "original" => $logo_path,
            "no_image" => Registry::i()->settings["no_image_user"],
        );
        
        $image_param = array(
            "height" => 50,
            "resizeHeight" => 50,
            "offSetX" => 0,
            "offSetY" => 0,
            "resizeDir" => $logo_resize,
        );
        
        
        $data["user"]["logo"] = $image->resize($user["logo"],$image_param,$image_settings);
        $data["user"]["admin_logo"] = "/".Registry::i()->settings["no_image_user"];
        
        $back = Core::$root_url.Url::root(FALSE);
        $back_ind = strrpos($back,'/');
        $length = strlen($back);
        $back_ind = strrpos($back,'/',-($length - $back_ind + 1));
        
        $back = substr($back,0,$back_ind);
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "back" => $back
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    function create(){
        //Создаем отображение
        $xml_pars = "template|default::user_support";
        $xsl_pars = "template|default::user_support_create";
        
        $data = array();
        
        if(Request::method("post")){
            
            $user = Registry::i()->user;
            
            // Обработка обращений.
            $for_support = array();
            $for_message = array();
            
            $for_support["id_user"] = $user["id"];
            $for_support["title"]   = Request::post("title");

            $message = Request::post("message");
            
            try{
                Query::i()->sql("transaction.start");
                
                // Записываем обращение
                $id = Query::i()->sql("insert",array(
                                        ":table" => "accounts_support",
                                        ":where" => "id_user, title",
                                        ":set" => $this->sql->insert_string($for_support)
                                    ));
                $id = current($id);
                
                // Записываем сообщение
                $for_message["id_accounts_support"] = $id;
                $for_message["message"] = $message;
                
                $table = implode(",",array_keys($for_message));
                $set = $this->sql->insert_string($for_message);
                
                Query::i()->sql("insert",array(
                                        ":table" => "support_message",
                                        ":where" => $table,
                                        ":set" => $set
                                    ));
                
                
                Query::i()->sql("transaction.commit");
                Request::redirect(Url::site("account/support",TRUE),302);
            }catch(Exception $e){
                Query::i()->sql("transaction.rollback");
            
                // Обрабатываем ошибку
                Core_Exception::client($e);
            }
        }
        
        $back = Core::$root_url.Url::root(FALSE);
        $back_ind = strrpos($back,'/');
        
        $back = substr($back,0,$back_ind);
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "back" => $back,
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}