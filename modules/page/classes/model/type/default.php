<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Type_Default_Page{

    public $date_format = "%Y-%m-%d'";
    public $page;
    
    function __construct($page){
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        
        $this->page = $page;
    }
    
    /**
     * Вывод содержимого страницы
     *
     * @return string контент страницы
     */
    function fetch(){
        Registry::i()->xsl_header = "page_default_header";
        $xml_pars = "template|default::page_default_content";
        $xsl_pars = "template|default::page_default_content";
        
        $data = array();
        
        $tech = array(
           "root" => Registry::i()->root,
           "site" => Url::site()
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        
        //exit;
    }
}