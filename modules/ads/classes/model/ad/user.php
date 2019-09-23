<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Ad_User_Ads{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","ads");
        $this->user_method = Controller::factory("method","user");
    }
    
    function fetch($id, $user){
        $this->error->cookie(FALSE);
        // Получаем вакансию, или создаем ошибку 404
        $ad = $this->method->get($id,$user);
        
        $xml_pars = "template|default::ads_ad";
        $xsl_pars = "template|default::ads_ad";


        $data = $ad;
        
        $data["user"] = $user;

        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        $data["phone"] = Query::i()->sql("user.phone",array(":id_user" => $data["ads_user"]["id"]),"id");
        
        if(Request::method("post")){
            Query::i()->sql("transaction.start");
            $result = FALSE;
            if($user["clicks"]){
                $result = $this->method->pay_ad(key($ad["ads"]), $data["user"]["id"],$data["ads_user"]["id"], 0);
            }
            else{
                $cost = 0;
                
                $price = Arr::search(array("type_name" => "ads"), $data["price"]);
                $price = Arr::value_key($price,"name");

                // Определяем стоимость
                if($user["days"] == 0){
                    $cost = $price["no_account"]["amount"];
                }else{
                    $cost = $price["ads"]["amount"];
                }

                if($cost > $user["seor"]){
                    $this->error->set("error","warning",array("message" => 'Недостаточно средств, <a href="/account/pay">пополните счет</a>.'));
                }else{
                    $result = $this->method->pay_ad(key($ad["ads"]), $data["user"]["id"], $data["ads_user"]["id"], $cost);
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
        $logo_path = $this->user_method->media_user_path($data["ads_user"]["id"]) . "logo";
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
        
        $data["ads_user"]["logo"] = $image->resize($data["ads_user"]["logo"],$image_param,$image_settings);
        
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
    }
}