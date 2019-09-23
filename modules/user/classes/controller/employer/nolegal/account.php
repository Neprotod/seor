<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_employer_nolegal_account_user {

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->method = Controller::factory("method","user");
        $this->account = Controller::factory("account","user");
        $this->employer_account = Model::factory("employer_account","user");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
    }
    /*Работа с профилем*/
    function fetch(){
        $this->error->cookie(FALSE);
        
        $user = Registry::i()->user;
        // Парсер файла
        if($user["complete"] == 0 OR Request::get("edit")){
            Request::redirect(Url::site("account/edit", TRUE));
        }else{
            $xml_pars = "template|default::user_account_profile";
            $xsl_pars = "template|default::user_nolegal";
            
            $data = array();
            $data["user"] = $user;
            
            $fields = $this->account->get_fields($user["id"]);
            $fields = $this->account->simplification_fields($fields, FALSE);
            $data["fields"] = $fields;
            
            $data["phone"] = Query::i()->sql("user.phone",array(":id_user" => $user["id"]),"id");
            
            $data["valid"] = Query::i()->sql("accounts.verification.get_id",array(":id_user" => $user["id"]));
            
            // Создаем логотипы
            $image = Module::factory("image",TRUE);
            $logo_path = $this->method->media_user_path() . "logo";
            $logo_resize = $logo_path . "/resize";
            
            $image_settings = array(
                "original" => $logo_path,
                "no_image" => Registry::i()->settings["no_image_user"],
            );
            
            $image_param = array(
                "height" => 100,
                "resizeHeight" => 100,
                "offSetX" => 0,
                "offSetY" => 0,
                "resizeDir" => $logo_resize,
            );
            
            if(!$logo = $user["logo"]){
                $data["user"]["no_logo"] = 1;
            }else{
                $data["user"]["no_logo"] = 0;
            }
            
            // Определяем дату в возврасте
            $date = new DateTime();
            $took_days = new DateTime($data["user"]["birthday"]);
            
            $data["user"]["all_year"] = $took_days->diff($date)->y; 
            
            $lang_id = Registry::i()->user_language["id"];
            
            // Берем страны
            $country = Query::i()->sql("country.get",array(":lang_code"=>$lang_id));
            $data["country"] = $country;
            
            // Ошибки
            $error = $this->error->output();
            
            $tech = array(
                "root" => Registry::i()->root,
                "site" => Core::$root_url,
                "action" => Url::root(NULL),
                array("error" => $error)
            );
            
            $data["user"]["logo"] = $image->resize($logo,$image_param,$image_settings);
            
            return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        }
    }
    
    function edit(){
        Registry::i()->data["file_name"] = "edit";
        $this->error->cookie(FALSE);
        
        $user = Registry::i()->user;
        // Парсер файла
        $xml_pars = "template|default::user_account_profile";
        $xsl_pars = "template|default::user_account_profile_nolegal";
        
        $data = array();
        $data["user"] = $user;
        
        $fields = $this->account->get_fields($user["id"]);
        $fields = $this->account->simplification_fields($fields, FALSE);
        $data["fields"] = $fields;
        
        $data["phone"] = Query::i()->sql("user.phone",array(":id_user" => $user["id"]),"id");
        

        if(Request::method("post")){
            $post = array();
            
            $birthday = Request::post("birthday"); 
            
            $error = 'Профиль не сохранен, в связи с техническими проблемами. Тех поддержка уже уведомлена.';
            $flag = TRUE;
            try{
                Query::i()->sql("transaction.start");
                if(!$this->account->phone_post($data)){
                    $flag = FALSE;
                }

                // Остальная информация о пользователи
                if(!$this->account->user_info_post($data)){
                    $flag = FALSE;
                }

                if($flag){
                    Query::i()->sql("transaction.commit");
                    
                    $this->error->set("message","info",array("message"=>"Профиль сохранен."));
                    $this->error->save_cookie();
                    
                    Request::redirect(Url::site("account", TRUE));
                }else{
                    Query::i()->sql("transaction.rollback");
                    $this->error->set("error","warning",array("message"=>"Профиль не сохранен"));
                }
            }catch(Exception $e){
                Query::i()->sql("transaction.rollback");
                $this->error->set("error","warning",array("message"=>"Профиль не сохранен, в связи с техническими проблемами. Тех поддержка уже уведомлена"));
                Core_Exception::client($e);
            }
            
            $data["birthday"] = $birthday;
            
            
            //$this->account->user_post($data);
        }
        // Создаем логотипы
        $image = Module::factory("image",TRUE);
        $logo_path = $this->method->media_user_path() . "logo";
        $logo_resize = $logo_path . "/resize";
        
        $image_settings = array(
            "original" => $logo_path,
            "no_image" => Registry::i()->settings["no_image_user"],
        );
        
        $image_param = array(
            "height" => 100,
            "resizeHeight" => 100,
            "offSetX" => 0,
            "offSetY" => 0,
            "resizeDir" => $logo_resize,
        );
        
        if(!$logo = $user["logo"]){
            $data["user"]["no_logo"] = 1;
        }else{
            $data["user"]["no_logo"] = 0;
        }
        
        
        $lang_id = Registry::i()->user_language["id"];
        
        // Берем страны
        $country = Query::i()->sql("country.get",array(":lang_code"=>$lang_id));
        $data["country"] = $country;
        
        // Ошибки
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            array("error" => $error)
        );
        
        $data["user"]["logo"] = $image->resize($logo,$image_param,$image_settings);

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    function ads(){
        return $this->employer_account->ads();
    }
    
    protected function create_ads($id){
       return $this->employer_account->create_ads($id);
    }
    
    
    function support($method = "fetch"){
        return $this->employer_account->support($method);
    }
    
    
    // Пишем настройки.
    function settings($method = "fetch"){
        return $this->employer_account->settings($method);
    }
}