<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Type_Vis_Categories_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','categories');
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
        $this->auxiliary = Admin_Model::factory("auxiliary","system");
    }
    
    function fetch($category){
        $xml_pars = "admin_template|default::categories_category";
        $xsl_pars = "admin_template|default::categories_vis";
        
        $url = Url::root(NULL);

        $category["fields"] = $this->auxiliary->get_fields($category["id"], "category");
        // Работа с родительскими категориями
        if($category["parent_id"]){
            //$parent = $this->method->get_category($category["parent_id"]);
            $category["parent_fields"] = $this->auxiliary->get_fields($category["parent_id"], "category");
        }
        
        if(Request::method("post")){
            $this->post($category);
        }
        
        
        $robots = array();
        if($robots = explode(",",$category["robots_name"])){
            $robots["index"] = $robots[0];
            $robots["follow"] = $robots[1];
            unset($robots[0],$robots[1]);
        }
        
        $tech = array(
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus")),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "action" => $url,
            "end_url" => substr($url,0,strrpos($url,"/")),
            "robots" => $robots,
        );
        $date = array();
        $date["content"] = $this->xml->preg_load($category,$xml_pars,$xsl_pars,$tech);
        
        return Admin_Template::factory(Registry::i()->template,"content_categories_category",$date);
    }
    
    function post(&$category){
        $new_category = Request::post("category");
        
        $new_category = Arr::replace_value($new_category,"",NULL);
        
        if(!isset($new_category["static"])){
            $new_category["static"] = 0;
        }
        if(!isset($new_category["status"])){
            $new_category["static"] = 0;
        }
        
        $fields = Request::post("fields");
        
        $robots = implode(",",Request::post("robots"));
        
        $new_category["robots_name"] = $robots;
        
        if(isset($fields['parent'])){
            $f_parent = $fields['parent'];
            unset($fields['parent']);
            $flaten = Arr::flatten($fields);
            array_unshift($flaten,$f_parent);
            $flaten = implode("/",$flaten);
            $flaten = trim($flaten,"/");
            $fields = Arr::fill_recurs($fields,$flaten,"var");
        }
        
        $table_f = array_keys($new_category);
        
        $new_category["fields"] = $fields;
        
        $update = array();

        foreach($table_f AS $value){
            if($new_category[$value] != $category[$value]){
                $update[$value] = $new_category[$value];
            }
        }
        
        $old_fields = array();
        if(isset($category["fields"])){
            $old_fields = $category["fields"];
        }
        
        $key_fields = key($fields);
        $insert_fields = array();
        $update_fields = array();
        $reset = FALSE;
        
        if($key_fields == "new"){
            $insert_fields = current($fields);
            $insert_fields["name"] = "image_path";
            $insert_fields["id_table"] = $category["id"];
            $insert_fields["id_type"] = $category["id_type"];
            $insert_fields["position"] = 0;
            $reset = ($test = $this->auxiliary->insert_fields($insert_fields))
                ?$test
                :$reset;
        }else{
            $test = Arr::search("var",$old_fields);
            if($test != $fields){
                $update_fields = current($fields);
                $reset = ($test = $this->auxiliary->update_fields($update_fields, $key_fields))
                    ?$test
                    :$reset;
            }
        }

        if($update){
            $reset = ($test = $this->method->update_category($update, $category))
                    ?$test
                    :$reset; 
        }
        
        
        if($reset){
            $message[] = "Категория обновлена";
            Cookie::set("success",serialize($message));
            Request::redirect(Url::root(NULL));
        }
        
        $category = Arr::merge($category, $new_category);

    }
        
}