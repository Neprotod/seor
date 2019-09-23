<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Ads_Guest_Ads{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->method = Controller::factory("method","ads");
    }
    
    function fetch($id){
        $get = $this->method->pars_get();
        
        
        $xml_pars = "template|default::ads_ads";
        $xsl_pars = "template|default::ads_ads";
        
        
        $data = $this->method->get_ads();
        
        if(Request::method("post")){
            // Для ajax запроса
            if(Request::post("get_ads")){
                return $data["content"];
            }
        }
        
        $data["all_ads_language"] = Query::i()->sql("ads.all_ads_language");
        $data["all_ads_country"] = Query::i()->sql("ads.all_ads_country");
        
        $data["get"] = $get;
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "return" => base64_encode(Url::query(array(),'auto')),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}