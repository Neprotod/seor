<?php defined('MODPATH') OR exit();
/**
 * Отображение и работа с категориями
 * 
 * @package    module
 * @category   category
 */
class Model_Type_Vis_Category {
    
    public $date_format = "%Y-%m-%d'";
    
    public $category;
    
    function __construct($category){
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->category = $category;
    }
    
    /**
     * Вывод содержимого страницы
     *
     * @return string контент категории
     */
    function fetch(){
        $file_name = $this->category['file_name'];
        $xml_pars = "template|default::category_".$file_name;
        $xsl_pars = "template|default::category_".$file_name;
        
        $module = Module::factory("category",TRUE);
        $categories = $module->get_categories("c.parent_id =".$this->category["id"]);
        
        $category_ids = array_keys($categories);
        $category_ids = $this->sql->insert_string($category_ids);
        $where = "id_category IN " . $category_ids;
        
        $category_page = Query::i()->sql("category_page.get",array(
                                                                ":where"=>$where
                                                                ));

        $pages_ids = array();
        foreach($category_page AS $value){
            $pages_ids[$value["id_page"]] = $value["id_page"];
        }
        
        $pages_ids = $this->sql->insert_string($pages_ids);
        $pages = Query::i()->sql("vis.pages",array(
                                                    ":set"=>$pages_ids
                                                    ),"id");
        
        
        $data = array();
        foreach($category_page AS $value){
            $id_category = $value["id_category"];
            $id_page = $value["id_page"];
            if(!isset($data[$id_category]))
                $data[$id_category] = $categories[$id_category];
            $data[$id_category]["page"][$pages[$id_page]["title"]] = $pages[$id_page];
        }
        ksort($data[$id_category]["page"]);
        
        $tech = array(
           "title" => $this->category['title'],
           "root" => Registry::i()->root,
           "site" => Core::$root_url
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    function get_pages(){
        
    }
}