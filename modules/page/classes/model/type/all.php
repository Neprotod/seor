<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Type_All_Page{

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
        $file_name = $this->page['file_name'];
        $xml_pars = "template|default::page_all";
        $xsl_pars = "template|default::page_all";

        $data["page"] = $this->page;
        
        $tech = array(
           "root" => Registry::i()->root,
           "site" => Core::$root_url,
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        
        //exit;
    }
}