<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Type_All_Pages_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','pages');
        $this->category = Admin_Controller::factory('method','categories');
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
        $this->auxiliary = Admin_Model::factory("auxiliary","system");
    }
    
    
    function new_page(){
        $xml_pars = "admin_template|default::pages_page";
        $xsl_pars = "admin_template|default::pages_all";
        // Файл подключения
        $xsl_include = "admin_template|default::pages_include_meta";
        
        $url = Url::root(NULL);
        
        $date = array();
        
        $page = array();
        if(Request::method("post")){
            $this->post($page);
        }
        
        $content_type = $this->auxiliary->get_content_type("page","AND ct.name <> 'default'");

        $page["content_type_arr"] = $content_type;
        
        if(!isset($page["id_category"])){
            if($id_category = Request::get("category")){
                $page["id_category"] = $id_category;
                $page["category"] = $this->category->get_category($page["id_category"]);
                $page["category"]["url"] = $this->category->get_url($page["id_category"]);
            }
        }
        
        $robots = array();
        if(isset($page["robots_name"])){
            if($robots = explode(",",$page["robots_name"])){
                $robots["index"] = $robots[0];
                $robots["follow"] = $robots[1];
                unset($robots[0],$robots[1]);
            }
        }else{
            $robots["index"] = "index";
            $robots["follow"] = "follow";
        }
        
        
        $tech = array(
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus")),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "action" => $url,
            "robots" => $robots,
            "end_url" => substr($url,0,strrpos($url,"/")).Url::query(array(),"auto")
        );
        
        $static = array(
            "::media::" => str_replace("\\","/",$this->xml->preg_file_path($xsl_include,"xsl"))
        );
        
        $date["content"] = $this->xml->preg_load($page,$xml_pars,$xsl_pars,$tech,$static);
        
        return Admin_Template::factory(Registry::i()->template,"content_pages_page",$date);
    }
    
    function fetch($page){
        $xml_pars = "admin_template|default::pages_page";
        $xsl_pars = "admin_template|default::pages_all";
        // Файл подключения
        $xsl_include = "admin_template|default::pages_include_meta";
        
        $date = array();

        $url = Url::root(NULL);
        
        
        //$id_category = Request::get("category");
        
        $robots = array();
        if($robots = explode(",",$page["robots_name"])){
            $robots["index"] = $robots[0];
            $robots["follow"] = $robots[1];
            unset($robots[0],$robots[1]);
        }
        
        // Определяем категорию.
        $category_page = $this->method->get_category($page["id"]);
        if($category_page){
            $page["id_category"] = $category_page["id_category"];
            $page["category"] = $this->category->get_category($page["id_category"]);
            $page["category"]["url"] = $this->category->get_url($page["id_category"]);
        }
        
       
        $content_type = $this->auxiliary->get_content_type("page","AND ct.name <> 'default'");

        $page["content_type_arr"] = $content_type;
        
        $page["fields"] = $this->auxiliary->get_fields($page["id"], "page");
        
        if(Request::method("post")){
            $this->post($page);
        }
        
        
        if($page["id_category"]){
            $page["parent_fields"] = $this->auxiliary->get_fields($page["category"]["id"], "category");
        }
        
        $tech = array(
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus")),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "action" => $url,
            "end_url" => substr($url,0,strrpos($url,"/")).Url::query(array(),"auto"),
            "robots" => $robots
        );
        
        $static = array(
            "::media::" => str_replace("\\","/",$this->xml->preg_file_path($xsl_include,"xsl"))
        );
        
        $date["content"] = $this->xml->preg_load($page,$xml_pars,$xsl_pars,$tech,$static);
        
        return Admin_Template::factory(Registry::i()->template,"content_pages_page",$date);
    }
    
    function post(&$page){
        $new_page = Request::post("page");
        
        $new_page = Arr::replace_value($new_page,"",NULL);
        
        // Флаг, было ли обновление.
        $reset = FALSE;
        
        if(!isset($new_page["status"])){
            $new_page["static"] = 0;
        }
       
        
        
        $robots = implode(",",Request::post("robots"));
        
        $new_page["robots_name"] = $robots;
        
        $table_f = array_keys($new_page);
        
        $update = array();
        
        foreach($table_f AS $value){
            if(!isset($page[$value])){
                $page[$value] = NULL;
            }
            if($new_page[$value] != $page[$value]){
                $update[$value] = $new_page[$value];
            }
        }
        
        if(isset($update["id_category"])){
             $page["category"] = $this->category->get_category($update["id_category"]);
             $page["category"]["url"] = $this->category->get_url($update["id_category"]);
             $reset = TRUE;
        }
       
        
        if($update){
            $reset = ($test = $this->method->update_page($update, $page))
                    ?$test
                    :$reset; 
        }
        
        if($reset){
            $message[] = "Страница обновлена";
            Cookie::set("success",serialize($message));
            Request::redirect(Url::root()."/pages/page/".$page["id"].Url::query(array(),"auto"));
        }
        
        $page = Arr::merge($page, $new_page);
    }
    
}