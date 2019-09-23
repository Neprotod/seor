<?php

class Pages_Admin{
    
    public $date_format = "%Y-%m-%d'";
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
        $this->methods = Admin_Controller::factory("method","pages");
        $this->category = Admin_Controller::factory("method","categories");
        $this->svg = Admin_Model::factory('svg','system');
        $this->auxiliary = Admin_Model::factory("auxiliary","system");
    }
    
    function page($id = NULL){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        // Активное меню
        Registry::i()->active_menu = "page";
        
        if($id)
            return $this->rout($id);
        
        Registry::i()->title = "Страницы";
        
        $default = $this->methods->get_default();
        $pages = array();
        if($id_category = Request::get("category")){
            $for_ids = Admin_Query::i()->sql("category_page.get",array(":where"=>"id_category =".$id_category),"id_page");
            $ids = array_keys($for_ids);
            $ids = $this->sql->insert_string($ids);
            if($ids)
                $pages = $this->methods->get_pages("p.id IN" . $ids);
        }else{
            $for_ids = Admin_Query::i()->sql("category_page.get",array(":where"=>"id_page"),"id_page");
            $ids = array_keys($for_ids);
            $ids = $this->sql->insert_string($ids);
            if($ids)
                $pages = $this->methods->get_pages("p.id NOT IN" . $ids);
            else{
                $pages = $this->methods->get_pages();
            }
        }
        
        $found = array();
        $found["default"] = $default;
        $found["pages"] = $pages;
        
        // Берем готовый HTML категорий
        $categories = $this->get_categories();
        
        $tech = array(
            array("svg"=>array(
                "page" => $this->svg->get_svg("page"),
                "category" => $this->svg->get_svg("category")
            )),
            array("categories"=>$categories),
            "get" => Url::query(array(),"auto"),
            "link" => Url::root(FALSE)
        );

        $xml_pars = "admin_template|default::pages_pages";
        
        $date["content"] = $this->xml->preg_load($found,$xml_pars,$xml_pars,$tech);
        
        return Admin_Template::factory(Registry::i()->template,"content_pages_pages",$date);
    }
    
    function rout($id){
        $page = array();
        $action = "fetch";
        if($id != 'new'){
            Registry::i()->title = "Изменение страницы";
            $page = $this->methods->get_page($id);
           
            $class = (empty($page["class"]))
                            ? "all"
                            : strtolower($page["class"]);
        }else{
            Registry::i()->title = "Создание страницы";
            $class = "all";
            $action = "new_page";
        }
        $class = "type_" . $class;
            
        return Admin_Model::factory($class,"pages")->$action($page);
    }
    
    function get_categories(){
        // Достаем категории из базы
        $categories = $this->category->get_categories();
        $for_count = Admin_Query::i()->sql("category_page.get",array(":where"=>"id"));

        foreach($for_count AS $value){
            $id = $value["id_category"];
            if(isset($categories[$id])){
                if(!isset($categories[$id]["count"]))
                    $categories[$id]["count"] = 0;
                $categories[$id]["count"]++;
            }
        }
        
        $tech = array(
            array(
                "svg"=>array(
                    "page" => $this->svg->get_svg("page"),
                    "category" => $this->svg->get_svg("category")
                )
            ),
            "current_id" => Request::get("category"),
            "link" => Url::root(FALSE)
        );
        
        $xml_pars = "admin_template|default::pages_categories";
        
        $date = array();
        
        return $this->xml->preg_load($categories,$xml_pars,$xml_pars,$tech);
    }
    function drop($id){
        $page = $this->methods->get_page($id);
       
        $id = $page["id"];
        $id_url = $page["id_url"];
        $id_type = $page["id_type"];
        $type = $page["type"];
        
        //
        
        $fields = $this->auxiliary->get_fields($id,$type);
        $this->auxiliary->drop_fields(array_keys($fields));
        
        Admin_Query::i()->sql("delete",array(
                                                ":table"=>"page",
                                                ":where"=>"id",
                                                ":insert"=>$this->sql->insert_string(array($id)),
                                            ));

        Url::delete($id_url);
    }
}