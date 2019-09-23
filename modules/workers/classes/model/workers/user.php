<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Workers_User_Workers{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","workers");
    }
    
    function fetch($user){
        $get = $this->method->pars_get();
        
        $xml_pars = "template|default::workers_workers";
        $xsl_pars = "template|default::workers_workers";
        
        $data = $this->method->get_workers(NULL, $user);
        
        $data["all_user_language"] = Query::i()->sql("workers.all_user_language");
        $data["all_user_country"] = Query::i()->sql("workers.all_user_country");
        
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