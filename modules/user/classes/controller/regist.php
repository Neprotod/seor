<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Regist_user{

    const VERSION = '1.0.0';
    
    /**
     * @var bool если в TRUE значит авторизация уже была проведена в прошлом
     */
    static $auth = FALSE;
    
    function __construct(){
        /*$this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->method = Controller::factory("method","user");*/
    }
    
    function fetch(){
        /*$data_file = array();
        $data_file["file_name"] = "regist";
        $data_file["style_name"] = "login";
        
        // Подключаем файл
        Registry::i()->data += $data_file;
        
        // Подключаем XML отображение
        $xml_pars = "template|default::user_regist";
        $xsl_pars = "template|default::user_regist";
        
        
        $data = array();
        
        $type = Query::i()->sql("user.user_type");
        
        $data["type"] = $type;
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(FALSE),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);*/
        Request::redirect(Url::site("account",TRUE),302);
    }
}