<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Worker_Guest_Workers{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->method = Controller::factory("method","workers");
    }
    
    function fetch($id){
        $data = $this->method->get($id,array());
        
        $xml_pars = "template|default::workers_worker";
        $xsl_pars = "template|default::workers_worker";
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "prev" => base64_decode(Request::get("prev")),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}