<?php defined('MODPATH') OR exit();
/**
 * Отображение и работа с категориями
 * 
 * @package    module
 * @category   category
 */
class Category_Module implements I_Module{

    const VERSION = '1.0.0';
    
    public $date_format = "%Y-%m-%d'";
    /**
     * @var array дерево категорий
     */
    protected static $tree_categories = array();
    
    /**
     * @var array все категории
     */
    protected static $all_categories = array();
    
    /**
     * @var array есть ли категории
     */
    protected static $categories = FALSE;
    
    function index($setting = null){}
    
    function __construct(){}
    
    /**
     * Вывод содержимого страницы
     *
     * @return string контент категории
     */
    function fetch(){
        $id_table = Registry::i()->founds['id_table'];
        $url =      Registry::i()->founds['url'];
        
        // Получаем категорию
        $category = $this->get_category($id_table);
        
        $template = Model::factory('template','system');
        
        $path = $template->path($category);
        //Находим стили относящиеся к данной странице.
        $style = Model::factory('style','system');
        $return = $style->init($category);
        
        $class = "type_";
        if(isset($category["class"])){
            $class .= $category["class"];
        }else{
            $class .= "all";
        }
        $data = array();
        $data["content"] = Model::factory($class,"category",array($category))->fetch();
        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,$data);
        
/*
        // Файл для подключения отображения.
        $file = $category['file_name'];
        $ext = $category['ext'];
        
        // Отдельный путь если есть.
        $path = $category['path'];
        
        //Определяем файл
        $type = Registry::i()->founds['type'];
        
        $path = $template->template_file(Registry::i()->template,$type,$file,NULL,$path);
        
        //Получаем связанные с категорией посты
        $posts = $this->category_post($category['id']);
        
        //Находим стили темы (стандартные)
        $template_id = (isset(Registry::i()->template['id']))? intval(Registry::i()->template['id']) : Registry::i()->template['name'];
        
        $template_style = $template->template_css($template_id);
        
        //Находим стили относящиеся к данной странице.
        $style = Model::factory('style','system');
        
        $category_style = $style->get_style($category['id'],$type);
        //Проверяем и добавляем  родительские стили
        if(isset($category_style['parent']) AND $parent_style = $style->get_style($category_style['parent'],$type)){
            $category_style = $style->valid_css($category_style,$parent_style);
        }
        
        //Проверяем стандартные стили.
        $category_style = $style->valid_css($category_style,$template_style);
        
        //Создаем пути стилей
        $style_path = $style->style_path($category_style,Registry::i()->root);
        
        //Загружаем строки стиля для дальнейшего вывода
        $style->style_string(array('css'=>$category['css'],'js'=>$category['js']),array('$root'=>Registry::i()->root));
        
        //Соберем Контент на вывод
        Registry::i()->style = $style;
        $template->header($category);

        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,array("category"=>$category,"posts"=>$posts,'root'=>Registry::i()->root));
    */}
    
    /**
     * Создает древо и список категорий
     *
     * @return bool если нет категорий возвращает FALSE;
     */
    function set_categories(){
        //Создаем массив для сохранения древа категорий
        $tree = array();
        $tree['subcategory'] = array();
        
        //Массив для сохранения категорий
        $pointer = array();
        $pointer[0] = &$tree;
        $pointer[0]['level'] = 0;
        
        /*
        $sql = "SELECT c.id, c.parent_id, u.nicename, c.date, c.title, c.url, c.meta_title, c.description, c.robots, c.status, c.static, c.modified, c.content, c.annotation, ct.name AS file_name, ct.ext, ct.path, c.extends_content, c.css, c.js, c.position
                FROM __categories c
                INNER JOIN __user u ON c.id_author = u.id
                INNER JOIN __content_type ct ON c.content_type = ct.id;";
        */
        ////////////////////////////////
        //////Создаем запрос для получения категорий
        ////////////////////////////////
        $sql = "SELECT c.id, c.parent_id, u.nicename, c.date, c.title, c.url AS category_url, url.url, c.meta_title, c.description, c.robots, c.status, c.static, c.modified, c.extends_content, c.position
                FROM __categories c
                INNER JOIN __user u ON c.id_author = u.id
                INNER JOIN __type type ON type.type = 'category'
                LEFT JOIN __url url ON c.id = url.id_table AND url.id_type = type.id;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        
        if(!$categories = $query->execute()){
            return FALSE;
        }
        
        //Создаем древо категорий если они есть
        foreach($categories AS $k=>$category){
            if(isset($category['parent_id'])){
                //Ссылка что бы была подвложенность
                $pointer[$category['parent_id']]['subcategory'][] =& $pointer[$category['id']];
                $pointer[$category['id']] = $category;
                
                $pointer[$category['id']]['level'] = 1+$pointer[$category['parent_id']]['level'];
            }
        }
        unset($categories);
        
        //Берем все ключи категорий
        $ids = array_keys($pointer);
        
        $parent_child = array('parent','children');
        
        //Отберем детей начиная с родителей
        foreach($parent_child AS $key =>$relative){
            //Переворачиваем массив
            if($key > 0)
                $ids = array_reverse($ids);
            //Отберем детей и родителей
            foreach($ids as $id){
                if($id>0){
                    if($pointer[$id]['parent_id'] > 0 OR $relative == 'children')
                        $pointer[$id][$relative][] = ($relative != 'children')? $pointer[$id]['parent_id'] : $id;
                    
                    //Создаем массив родителей
                    if(isset($pointer[$pointer[$id]['parent_id']][$relative])){
                            $pointer[$id][$relative] = array_merge($pointer[$id][$relative],$pointer[$pointer[$id]['parent_id']][$relative]);
                    }else{
                        if($relative == 'children')
                            $pointer[$pointer[$id]['parent_id']][$relative] = $pointer[$id][$relative];
                    }
                }
            }
            
        }
        
        unset($pointer[0],$ids);
        
        //Заполняем переменные
        self::$tree_categories = $tree['subcategory'];
        self::$all_categories = $pointer;
        
        
        return self::$categories = TRUE;
    }
    
    /**
     * Заполняет массив или объект категориями
     *
     * @param  mixed $object object или array
     * @return mixed         вернет array или FALSE;
     */
    function set_array($object){
        //Если нет категорий
        if(!self::$categories)
            return FALSE;
        
        if(is_object($object)){
            $object->tree_categories = self::$tree_categories;
            $object->all_categories = self::$all_categories;
        }
        elseif(is_array($object)){
            $object['tree_categories'] = self::$tree_categories;
            $object['all_categories'] = self::$all_categories;
            //Возвращаем массив, так как в отличие от объекта не переходит по ссылке
            return $object;
        }else{
            return NULL;
        }
    }
    
    /**
     * Получаем категорию по id
     *
     * @param  int   $id  id категории
     * @param  bool  $sql если TRUE создась запрос и дополнит массив
     * @return array      вернет категорию
     */
    function get_category($id){
        try{
            $result = Query::i()->sql("categories.get_category",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":id"=>$id,
                                                                ), NULL, TRUE);
           
            if($result)
                return $result;
            
            throw new Exception("Категория с id = <b>:id</b> не найдена в таблице page",array(":id"=>$id));
        }catch(Exception $e){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
            Model::factory('error','system')->error();
        }
        /*
        if(array_key_exists($id,self::$all_categories)){
            //Если не нужен полный вывод получаем категорию
            if(!$sql){
                return self::$all_categories[$id];
            }else{
                $sql = "SELECT c.content, c.annotation, ct.name AS file_name, ct.ext, ct.path,  c.css, c.js
                FROM __categories c
                INNER JOIN __content_type ct ON c.content_type = ct.id
                WHERE c.id = :id
                LIMIT 1;";
            
                $query = DB::query(Database::SELECT, DB::placehold($sql));
                $query->param(':id',$id);
                //Соединяем массивы
                if($return = $query->execute(NULL,TRUE)){
                    return Arr::merge($return,self::$all_categories[$id]);
                }
            }
        }*/
    }
    
    /**
     * Получаем категорию по id
     *
     * @param  array $ids id всех категорий
     * @return array      вернет найденные категории
     */
    function get_categories($where = NULL){
        if(!empty($where)){
            $where .= " AND c.status = 1";
        }else{
            $where .= "c.status = 1";
        }
        return Query::i()->sql("categories.get_categories",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":where"=>$where,
                                                                ), "id");
           
        
        /*if(!self::$categories){
            $this->set_categories();
        }
        
        $founds = array();

        foreach($ids as $id){
            if(array_key_exists($id,self::$all_categories)){
                $founds[$id] = self::$all_categories[$id];
            }
        }
        
        return $founds;*/
    }
}