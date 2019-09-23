<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Type_Vis_Pages_Admin{
    
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
    
    function fetch($page){
        $xml_pars = "admin_template|default::pages_page";
        $xsl_pars = "admin_template|default::pages_vis";
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
        
        $pred_param = array(
            "no_field" => array(
                "children" => array(
                                    array(
                                      "Срок оформления визы 7-14 дней.", 
                                      "Полный пакет документов.", 
                                      "Личная подача в консульстве.", 
                                      "Гарантия получения результата.", 
                                    )
                                )
            ),
            "no_params" => array(
                "children" =>   array(
                                    array(
                                      "Тип визы", 
                                      "Срок действия", 
                                      "Стоимость визы", 
                                      "Что нужно для получения", 
                                    ),
                                    array(
                                      "Разовая", 
                                      "30/14", 
                                      "", 
                                      "В пустой паспорт", 
                                    ),
                                    array(
                                      "Мультивиза", 
                                      "180/90", 
                                      "", 
                                      "Катанный паспорт", 
                                    ),
                                    array(
                                      "Мультивиза", 
                                      "365/90", 
                                      "", 
                                      "Катанный паспорт", 
                                    )
                                ),
            )
        );
        
        if($page["fields"]){
            if(!$params = Arr::search(array("name"=>"params"), $page["fields"])){
                $page["fields"]["no_params"] = $pred_param["no_params"];
                $page["fields"]["no_params"]["name"] = "params"; 
            }
            if(!$f = Arr::search(array("name"=>"field"), $page["fields"])){
                $page["fields"]["no_field"] = $pred_param["no_field"];
                $page["fields"]["no_field"]["name"] = "field";
            }
            $params += $f;
            if($params){
                foreach($params AS $key => $value){
                    $param_string =  $value["text"];
                    $param_string = explode("&&",$param_string);
                    $children = array();
                    $params[$key]["children"] = &$children;
                    foreach($param_string AS $key => $value){
                        if(!empty($value)){
                            $children[] = explode("||", $value);
                        }
                    }
                    unset($children);
                }
                $page["fields"] = Arr::merge($page["fields"], $params);
            }else{
                $page["fields"]["no_field"] = $pred_param["no_field"];
                $page["fields"]["no_field"]["name"] = "field";
                $page["fields"]["no_params"] = $pred_param["no_params"];
                $page["fields"]["no_params"]["name"] = "params";
            }
        }else{
            $page["fields"]["no_field"] = $pred_param["no_field"];
            $page["fields"]["no_field"]["name"] = "field";
            $page["fields"]["no_params"] = $pred_param["no_params"];
            $page["fields"]["no_params"]["name"] = "params";
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
       
        // Работаем с полями
        if($fields = Request::post("fields")){
            if(isset($fields['parent'])){
                $f_parent = $fields['parent'];
                unset($fields['parent']);
            }
            if(isset($fields['new'])){
                $for_new = $fields['new'];
                unset($fields['new']);
                $new_field = array();
                foreach($for_new AS $name => $var){
                    $f = array();
                    $new_field[] = &$f;
                    $f["name"] = $name;
                    $f["var"]  = $var;
                    unset($f);
                }
            }
            foreach($fields AS $key => $val){
                unset($fields[$key]);
                $fields[$key]["id"] = $key;
                $fields[$key]["name"] = key($val);
                $fields[$key]["var"] = current($val);
            }
           
            
            $new_page["fields"] = $fields;
            $old_fields = array();
            if(isset($page["fields"])){
                $old_fields = $page["fields"];
            }
            
            $insert_fields = array();
            $update_fields = array();

            if(isset($new_field)){
                foreach($new_field AS $key => $value){
                    $insert_fields = $value;
                    $insert_fields["id_table"] = $page["id"];
                    $insert_fields["id_type"] = $page["id_type"];
                    $insert_fields["position"] = 0;
                    $reset = ($id = $this->auxiliary->insert_fields($insert_fields))
                    ?TRUE
                    :$reset;
                    $value["id"] = $id[0];
                    $new_page["fields"][$id[0]] = $value;
                }
            }
            if(!empty($fields)){
                if(isset($f_parent)){
                    foreach($f_parent AS $name => $val){
                        $search = Arr::search(array("name" => $name), $fields);
                        $var = Arr::search('var', $search);
                        $var = Arr::flatten($var);
                        array_unshift($var,$val);
                        $var = implode("/",$var);
                        $search = Arr::fill_recurs($search,$var,"var");
                        $fields = Arr::merge($fields, $search);
                    }
                }
                
                foreach($fields AS $key => $value){
                    if($old_fields[$key]["var"] != $value["var"]){
                        $reset = ($test = $this->auxiliary->update_fields($value, $value["id"]))
                        ?TRUE
                        :$reset;
                        $new_page["fields"][$value["id"]] = $value;
                    }
                }
                
            }
        }
        
        $insert_fields = array();
        $update_fields = array();
        // Специфические поля
        if($params = Request::post("params")){
            $string = "";
            foreach($params AS $param){
                if(is_array($param)){
                    foreach($param AS $value){
                         $drop = TRUE;
                        foreach($value AS $val){
                            if(!empty($val)){
                                $drop = FALSE;
                            }
                        }
                        if(!$drop){
                            $string .= implode("||",$value);
                            $string .= "&&";
                        }
                    }
                    
                    
                }
            }
            $string = trim($string,"&&");
            $this->dop_field($string,"params",$old_fields,$page,$insert_fields,$update_fields);
        }
        if($params = Request::post("dop_field")){
            $string = implode("||",$params);
            $string = rtrim($string,"||");
            $this->dop_field($string,"field",$old_fields,$page,$insert_fields,$update_fields);
        }
        if($insert_fields){
            foreach($insert_fields AS $name => $value){
                $reset = ($id = $this->auxiliary->insert_fields($value))
                ?TRUE
                :$reset;
                $new_page["fields"][$id[0]] = array(
                    "id" => $id[0],
                    "name" => $value["name"],
                    "text" => $value["var"],
                );
            }
        }
        if($update_fields){
            foreach($update_fields AS $name => $value){
                $reset = ($test = $this->auxiliary->update_fields(array("text"=>$value["text"]), $value["id"]))
                        ?TRUE
                        :$reset;
                $new_page["fields"][$value{"id"}] = array(
                    "id" => $value{"id"},
                    "name" => $name,
                    "text" => $value["text"],
                );
            }
        }
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
            Request::redirect(Url::root(NULL));
        }
        
        $page = Arr::merge($page, $new_page);

    }
    
    function dop_field($string,$f,$old_fields,$page,&$insert,&$update){
        if($old_params = Arr::search(array("name"=>$f),$old_fields)){
            $id = key($old_params);
            if($old_params[$id]["text"] != $string){
                $update[$f]["id"] = $id;
                $update[$f]["text"] = $string;
            }
        }else{
            $insert[$f]["var"] = $string;
            $insert[$f]["where"] = "text";
            $insert[$f]["name"] = $f;
            $insert[$f]["id_table"] = $page["id"];
            $insert[$f]["id_type"] = $page["id_type"];
            $insert[$f]["position"] = 0;
        }
    }
}