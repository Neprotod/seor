<?php

class Categories_Admin{
    
    public $date_format = "%Y-%m-%d";
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
        $this->auxiliary = Admin_Model::factory("auxiliary","system");
        $this->methods = Admin_Controller::factory("method","categories");
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function category($id = NULL){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        if(!$id){
            Registry::i()->title = "Категории";
            return $this->all_category();
        }else{
            Registry::i()->title = "Изменение категории";
            // Берем категорию
            if($category = $this->methods->get_category($id)){
                $class = (empty($category["class"]))
                            ? "default"
                            : strtolower($category["class"]);
                            
                $class = "type_" . $class;
                
                $category["url_parent"] = $this->methods->get_url($category["parent_id"]);
                
                return Admin_Model::factory($class,"categories")->fetch($category);
            }else{
                $this->error->set("error","danger",array("message"=>"Нет такой категории."));
                Registry::i()->errors = $this->error->output();
            }
            
        }
    }
    
    function all_category(){
        $categories = $this->methods->get_categories();
        
        $tech = array(
            array(
                "svg"=>array(
                    "page" => $this->svg->get_svg("page"),
                    "category" => $this->svg->get_svg("category")
                )
            ),
            "link" => Url::root(NULL)
        );
        
        $xml_pars = "admin_template|default::categories_categories";
        
        $date = array();
        
        $date["content"] = $this->xml->preg_load($categories,$xml_pars,$xml_pars,$tech);
        
        return Admin_Template::factory(Registry::i()->template,"content_categories_categories",$date);
    }
}