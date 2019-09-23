<?php defined('MODPATH') OR exit();

/*
 * ћодель определ¤ет к какому типу относитс¤ URL  
 */
class Controller_Method_Categories_Admin{
    
    public $date_format = "%Y-%m-%d";
    
    function __construct(){
        if(isset(Registry::i()->date_format))
            $this->date_format = Registry::i()->date_format;
        
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->validator = Model::factory("validator","system");
        $this->sql = Admin_Model::factory("sql","system");
    }
    
    /*
     * Находит и возвращает категории
     *
     * @return array содержимое постов
     */
    function get_categories(){
        return Admin_Query::i()->sql("categories.get_categories",array(
                                                                ":date_format"=>$this->date_format), "id");
    }    
    
    /*
     * Получаем категорию
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_category($id){
        return Admin_Query::i()->sql("categories.get_category",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":id"=>$id,
                                                                ), NULL, TRUE);
    }
    /*
     * Получаем дополнительные поля категории
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function update_category($update, $category){
        $table_category = array(
            "title" => NULL,
            "meta_title" => NULL,
            "meta_keywords" => NULL,
            "description" => NULL,
            "status" => NULL,
            "static" => NULL,
            "robots" => NULL,
            "url_name" => NULL,
            "id_url" => array(
                'col_name'=> 'url'
            )
        );
        $table_robots = array(
            "robots_name" => array(
                'col_name'=> 'robots'
            ),
        );
        if($update_robots = $this->sql->intersect($update, $table_robots)){
            $update_robots = $this->sql->where(',', $update_robots);
            $update_robots = Admin_Query::i()->sql("auxiliary.robots",array(":where"=>$update_robots),NULL,TRUE);
            $update["robots"] = $update_robots["id"];
        }
        
        if(isset($update["url_name"])){
            $parent_url = $this->get_url($category["parent_id"]);
            $url = $parent_url . "/" . $update["url_name"];
            $url = trim($url,"/");
            if(empty($category["id_url"])){
                // Добавляем
                $insert_url = array(
                    ":url" => $url,
                    ":id_type" => $category["id_type"],
                    ":id_table" => $category["id"],
                    ":id_canonical" => NULL
                );
                
                $id_url = Admin_Query::i()->sql("url.insert_url",$insert_url);
                
                $id_url = current($id_url);
                $update["id_url"] = $id_url;
            }else{
                // Обновляем
                Url::update($category["id_url"],$url);
            }
        }
        
        $drop_url = FALSE;
        if(isset($update["static"]) AND !empty($update["static"])){
            if(!empty($category["id_url"])){
                $drop_url = TRUE;
                $update["id_url"] = NULL;
                $update["url_name"] = NULL;
                
                
            }
        }

        $update_table = $this->sql->intersect($update, $table_category);
        $update_table = $this->sql->update(',', $update_table);        
        
        if($update_table){
            $update_table .= ",modified = NOW()";
            $result = Admin_Query::i()->sql("update",array(":set"=>$update_table,":id"=>$category['id'],":table"=>"categories"));
            
            if($drop_url){
                // Удаляем URL
                Url::delete($category["id_url"]);
            }
            
            return $result;
        }
        
        return FALSE;
    }
    
    /*
     * Получить цепочку URL
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_url($id){
        $url = '';
        if($id){
            $category = Admin_Query::i()->sql("categories.get_url",array(":id"=>$id),NULL,TRUE);
            $url = $category["url_name"];
            if($category["parent_id"]){
               $url = $this->get_url($category["parent_id"]) . "/" . $url;
            }
        }
        return trim($url,"/");
    }
    
    /*
    *********************************************
    *********************************************
    *********** Переписать ВСЕ
    *********************************************
    */
    protected function init_categories($categories = NULL){
        // Дерево категорий
        $tree = new stdClass();
        $tree->subcategories = array();
        
        // Указатели на узлы дерева
        $pointers = array();
        $pointers[0] = &$tree;
        $pointers[0]->path = array();
        $pointers[0]->level = 0;
        
        if(!$categories){
            $categories = $this->get_categories();
        }

        $categories = Arr::fill_recurs_value($categories, "parent_id", NULL, 0);

        $tmp = array();
        
        // Создаем из массива объект
        foreach($categories as $category){
            $tmp[] = (object)$category;
        }
        $categories = $tmp;
        

        $finish = false;
        while(!empty($categories)  && !$finish){
            $flag = false;
            // Проходим все выбранные категории
            foreach($categories as $k=>$category){
                
                if(isset($pointers[$category->parent_id])){
                    // В дерево категорий (через указатель) добавляем текущую категорию
                    $pointers[$category->id] = $pointers[$category->parent_id]->subcategories[] = $category;
                    
                    // Путь к текущей категории
                    //$curr = $pointers[$category->id];
                    //$pointers[$category->id]->path = array_merge((array)$pointers[$category->parent_id]->path, array($curr));
                    
                    // Уровень вложенности категории
                    $pointers[$category->id]->level = 1+$pointers[$category->parent_id]->level;

                    // Убираем использованную категорию из массива категорий
                    unset($categories[$k]);
                    $flag = true;
                }
            }
            if(!$flag) $finish = true;
        }
        
        
                // Для каждой категории id всех ее детей найдем
        $ids = array_reverse(array_keys($pointers));
        foreach($ids as $id){
            if($id>0){
                $pointers[$id]->children[] = $id;
                
                if(isset($pointers[$pointers[$id]->parent_id]->children))
                    $pointers[$pointers[$id]->parent_id]->children = array_merge($pointers[$id]->children, $pointers[$pointers[$id]->parent_id]->children);
                else
                    $pointers[$pointers[$id]->parent_id]->children = $pointers[$id]->children;
            }
        }
        unset($pointers[0]);
        unset($ids);

        //$this->categories_tree = $tree->subcategories;
        unset($pointers[0]);
        
        Registry::i()->categories_tree = $this->grinding_tree($tree->subcategories);
        Registry::i()->all_categories = $pointers;

        //print_r($this->categories_array);
    }
    

    //Для того, что бы сделать массив из обьекта
    protected function grinding_tree($objects){
        $grand = array();
        if(!empty($objects)){
            $objects = (array)$objects;
            $res = reset($objects);
            if(!is_array($res) AND !is_object($res)){
                $res = array();
                $res[] = $objects;
                $objects = $res;
            }
            unset($res);
            foreach($objects as $k=>$value){
                if(is_object($objects[$k])){
                    $grand[$k] = (array)$objects[$k];
                }else{
                    $grand[$k] = $objects[$k];
                }
                if(isset($grand[$k]['subcategories'])){
                    $grand[$k]['subcategories'] = $this->grinding_tree($grand[$k]['subcategories']);
                }
            }
        }
        return $grand;
    }
    
    // Функция возвращает дерево категорий
    function get_categories_tree($categories = NULL){
        if(!isset($this->categories_tree))
            $this->init_categories($categories);
            
        return Registry::i()->categories_tree;
    }
    
    /*
     * Обнавляем страницу
     *
     * @param mixed либо массив либо одно значение id category
     * @param array значения для вставки таблица => значение
     * @return mixed вернет все id
     */
    function update_categories($id,array $update){
        $update = Str::key_value($update);
        
        if(!empty($update))
            $update .= ", modified = NOW()";
        
        $sql = Str::__('UPDATE __categories SET :update WHERE id IN(:id)', array(':update'=>$update,':id'=>implode(',',(array)$id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();

        if(empty($result))
            return FALSE;
        return $id;
    }
}