<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Worker_User_Workers{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","workers");
        $this->user_method = Controller::factory("method","user");
    }
    
    function fetch($id, $user){
        $this->error->cookie(FALSE);
        
        $data = array();
        
        $xml_pars = "template|default::workers_worker";
        $xsl_pars = "template|default::workers_worker";
        
        // Получаем вакансию, или создаем ошибку 404
        $data = $this->method->get($id,$user);
        
        $data["user"] = $user;
        
        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        $data["phone"] = Query::i()->sql("user.phone",array(":id_user" => $data["worker"]["id"]),"id");
        
        if(Request::method("post")){
            Query::i()->sql("transaction.start");
            $result = FALSE;
            if(FALSE){
                $result = $this->method->pay_ad(key($ad["ads"]), $data["user"]["id"],$data["ads_user"]["id"], 0);
            }
            else{
                
                $cost = 0;
                
                $price = Arr::search(array("type_name" => "user"), $data["price"]);
                $price = Arr::value_key($price,"name");

                // Определяем стоимость
                if($user["days"] == 0){
                    $cost = $price["no_user_account"]["amount"];
                }else{
                    $cost = $price["user"]["amount"];
                }
                
                if($cost > $user["seor"]){
                    $this->error->set("error","warning",array("message" => 'Недостаточно средств, <a href="/account/pay">пополните счет</a>.'));
                }else{
                    $result = $this->method->pay_worker($data["user"]["id"], $data["worker"]["id"], $cost);
                }
                
            }
            if($result){
                Query::i()->sql("transaction.commit");
            }else{
                Query::i()->sql("transaction.rollback");
            }
            $this->error->save_cookie();
            Request::redirect(Url::site(FALSE));
                
        }

        $error = $this->error->output();
        
        // Создаем логотипы
        $image = Module::factory("image",TRUE);
        $logo_path = $this->user_method->media_user_path($data["worker"]["id"]) . "logo";
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
        
        $data["worker"]["logo"] = $image->resize($data["worker"]["logo"],$image_param,$image_settings);
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "prev" => base64_decode(Request::get("prev")),
            array("error" => $error)
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        
        
         /*       
        $data["user"] = $user;

        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        
        
        */
    }
}