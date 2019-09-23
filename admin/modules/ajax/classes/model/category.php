<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Category_Ajax_Admin{
    
    public $dir;
    public $xml;
    public $sql;
    public $svg;
    
    function __construct(){
        $this->category = Admin_Controller::factory("method","categories");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function fetch(){
         // Достаем категории из базы
        $categories = $this->category->get_categories();
        
        $tech = array(
            array(
                "svg"=>array(
                    "page" => $this->svg->get_svg("page"),
                    "category" => $this->svg->get_svg("category")
                )
            ),
            "current_id" => Request::post("id"),
            "link" => Url::root(FALSE)
        );

        $xml_pars = "admin_template|default::pages_categories";
        $xsl_pars = "admin_module|ajax::categories";
        $date = array();
        
        $data["content"] = $this->xml->preg_load($categories,$xml_pars,$xsl_pars,$tech);
        return $data;
    }
    function parent_category($id){
        return $this->category->get_url($id);
    }
}