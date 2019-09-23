<?php defined('MODPATH') OR exit();

/*
 * ћодель определ¤ет к какому типу относитс¤ URL  
 */
class Controller_Method_Pages_Admin{
    
    public $date_format = "%Y-%m-%d";
    public $id_template ;
    public $sql ;
    
    function __construct(){
        $this->sql = Admin_Model::factory("sql","system");
        
        $this->id_template = Registry::i()->root_template["id"];
        if(isset(Registry::i()->date_format))
            $this->date_format = Registry::i()->date_format;
    }
    
    /*
     * Находит и возвращает страницы
     *
     * @return array содержимое постов
     */
    function get_default(){
        $default = Admin_Query::i()->sql("pages.get_pages",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":where"=>"ct.name = 'default'",
                                                                ), "id");
        
        return $default;
    }    
    /*
     * Находит и возвращает страницы
     *
     * @return array содержимое постов
     */
    function get_pages($where = NULL){
        if(!$where)
            $where = "ct.name <> 'default'";
        else 
            $where .= "AND ct.name <> 'default'";
        return Admin_Query::i()->sql("pages.get_pages",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":where"=>$where,
                                                                ), "id");
    }    
    
    /*
     * Получаем страницу
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_page($id = NULL){
        return Admin_Query::i()->sql("pages.get_page",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":id"=>$id,
                                                                ), NULL, TRUE);
    }        
    /*
     * Получаем связанную категорию
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_category($id_page = NULL){
        if(!empty($id_page)){
            return Admin_Query::i()->sql("category_page.get",array(
                                                                ":where"=>"id_page = {$id_page}",
                                                                ), NULL, TRUE);
        }else{
            return FALSE;
        }
    }        
    /*
     * Обнавляем страницу
     *
     * @param mixed либо массив либо одно значение id page
     * @param array значения для вставки таблица => значение
     * @return mixed вернет все id
     */
    function update_page($update, &$page){
        $table_page = array(
            "id_admin_user" => NULL,
            "title" => NULL,
            "meta_title" => NULL,
            "meta_keywords" => NULL,
            "description" => NULL,
            "status" => NULL,
            "robots" => NULL,
            "url_name" => NULL,
            "content" => NULL,
            "content_type" => NULL,
            "id_url" => array(
                'col_name'=> 'url'
            )
        );

        if(!isset($update["id_admin_user"]))
            $update["id_admin_user"] = Registry::i()->auth->user["id"];
        
        $table_robots = array(
            "robots_name" => array(
                'col_name'=> 'robots'
            ),
        );
        if($update_robots = $this->sql->intersect($update, $table_robots)){
            $update_robots = $this->sql->update(',', $update_robots);
            $update_robots = Admin_Query::i()->sql("auxiliary.robots",array(":where"=>$update_robots),NULL,TRUE);
            $update["robots"] = $update_robots["id"];
        }
        
        $insert = FALSE;
        if(!isset($page["id"]) OR empty($page["id"])){
            $to_insert = $this->sql->intersect($update, $table_page);
            
            $where = implode(",",array_keys($to_insert));

            $set = $this->sql->insert_string(Arr::flatten_key($to_insert));

            $id = Admin_Query::i()->sql("insert",array(
                    ":where"=>$where,
                    ":set"=>$set,
                    ":table"=>"page"));
            
            $insert = TRUE;
            
            $page = $this->get_page($id[0]);
        }
        
        if(isset($update["id_category"])){
            if(isset($page["id"]) AND $category_page = $this->get_category($page["id"]) 
                AND $category_page["id_category"] != $update["id_category"]){
                $id_category = array(
                        "id_category" => NULL,
                );
                $id_category = $this->sql->intersect($update, $id_category);
                $id_category = $this->sql->update(',', $id_category);
                
                $result = Admin_Query::i()->sql("update",array(":set"=>$id_category,":id"=>$category_page["id"],":table"=>"categories"));
            }else{
                Admin_Query::i()->sql("category_page.insert",array(
                                ":id_category"=>$update["id_category"],
                                ":id_page"=>$page["id"],
                                ":position"=>0));
            }
        }
       
        if(isset($update["url_name"])){
            $parent_url = '';
            
            if(isset($page["category"]["url"]))
                $parent_url = $page["category"]["url"] . "/";
            $url = $parent_url . $update["url_name"];
            $url = trim($url,"/");

            if(empty($page["id_url"])){
                // Добавляем
                $insert_url = array(
                    ":url" => $url,
                    ":id_type" => $page["id_type"],
                    ":id_table" => $page["id"],
                    ":id_canonical" => NULL
                );
                
                $id_url = Admin_Query::i()->sql("url.insert_url",$insert_url);
                
                $id_url = current($id_url);
                $update["id_url"] = $id_url;
            }else{
                // Обновляем
                Url::update($page["id_url"],$url);
            }
        }
        
        $update_table = $this->sql->intersect($update, $table_page);
        $update_table = $this->sql->update(',', $update_table);        
        
        if($update_table){
            $update_table .= ",modified = NOW()";
            $result = Admin_Query::i()->sql("update",array(":set"=>$update_table,":id"=>$page['id'],":table"=>"page"));
            
            return $result;
        }
        
        return FALSE;
        
    }    
}