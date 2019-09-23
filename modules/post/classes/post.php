<?php defined('MODPATH') OR exit();
/**
 * Отображение постов
 * 
 * @package    module
 * @category   post
 */
class Post_Module implements I_Module{

    const VERSION = '1.0.0';

    /**
     * @var array ссылка на массив с постом
     */
     protected $post = array();
    
    /**
     * @var array ссылка на массив с категорией в посте
     */
     protected $category = array();
    
    function index($setting = null){}
    
    function __construct(){}
    
    /**
     * Вывод содержимого поста
     *
     * @return string контент поста
     */
    function fetch(){
        $template = Model::factory('template','system');
        
        $id_table = Registry::i()->fonds['id_table'];
        
        //Находим пост
        $post = $this->get_post($id_table);
        
        //Передаем пост по ссылке, для заполнение через приватные методы
        $this->post =& $post;
        //Находим категории
        $categories = $this->post_category($post['id']);
        // Получаем категорию
        $category = Module::factory('category',TRUE);
        
        $categories = $category->get_categories($categories);

        //Определяем и заполняем родительскую категорию. 
        $this->category_valid($categories,Registry::i()->fonds['url']);
        
        //Заполняем если нет файла для отрисовки
        $this->post_valid();

        // Файл для подключения отображения.
        $file = $post['file_name'];
        $ext = $post['ext'];
        
        // Отдельный путь если есть.
        $path = $post['path'];
        
        //Определяем файл
        $type = Registry::i()->fonds['type'];
        
        $path = $template->template_file(Registry::i()->template,$type,$file,NULL,$path);

    
        //Находим стили темы (стандартные)
        $template_id = (isset(Registry::i()->template['id']))? intval(Registry::i()->template['id']) : Registry::i()->template['name'];
        
        $template_style = $template->template_css($template_id);
        
        //Находим стили относящиеся к данной странице.
        $style = Model::factory('style','system');
        
        $post_style = $style->get_style($post['id'],$type);
        
        //Сливаем все стили с основной категории и всех статических
        $category_style = $style->get_style($post['category']['style'],'category',TRUE);
        
        //Проводим валидацию стилей категорий
        $post_style = $style->valid_css($category_style,$post_style);
        
        //Проводим валидацию стилей по умолчанию
        $post_style = $style->valid_css($post_style,$template_style);
        
        //Создаем пути стилей
        $style_path = $style->style_path($post_style,Registry::i()->root);
        
        //Загружаем строки стиля для дальнейшего вывода
        $style->style_string(array('css'=>$post['css'],'js'=>$post['js']),array('$root'=>Registry::i()->root));
        
        //Собераем контент на вывод
        Registry::i()->style = $style;
        $template->header($post);

        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,array("post"=>$post,'root'=>Registry::i()->root));
    }
    
    /**
     * Находит и возвращает пост
     *
     * @param  int $id id поста
     * @return array   содержимое поста
     */
    function get_post($id){
        $sql = "SELECT p.id, u.nicename, p.date, p.title, p.url, p.meta_title, p.description, p.robots, p.status, p.comment_status, p.modified, p.content, p.annotation, c.name AS file_name, c.ext, c.path, p.css, p.js
            FROM __post p
            INNER JOIN __user u ON p.id_author = u.id
            LEFT JOIN __content_type c ON p.content_type = c.id
            WHERE p.id = :id LIMIT 1;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        
        $query->param(':id',$id);
        try{
            if($result = $query->execute(NULL,TRUE))
                return $result;
            
            throw new Exception("Пост с id = <b>{$id}</b> не найден в таблице post");
        }catch(Exception $e){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
            Model::factory('error','system')->error();
        }
        
    }
    
    /**
     * Находит и возвращает категории
     *
     * @param  int   $id id поста
     * @return array     категории относящиеся к этому посту
     */
    function post_category($id){
        $sql = "SELECT cp.id_category
                FROM category_post cp
                INNER JOIN __post p ON cp.id_post = p.id
                INNER JOIN __categories c ON cp.id_category = c.id
                WHERE cp.id_post = :id
                ORDER BY c.parent_id DESC";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        
        $query->param(':id',$id);
        try{
            if($result = $query->execute('id_category')){
                return array_keys($result);
            }
            throw new Exception("У поста с id = <b>{$id}</b> нет категорий");
        }catch(Exception $e){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
            Model::factory('error','system')->error();
        }
        
    }
    
    /**
     * Отдает расширение файла
     *
     * @param  int   $id id таблицы content_type
     * @return array     возвращает массив для подключения файла.
     */
    function extends_content($id){
        $sql = "SELECT c.name AS file_name, c.ext, c.path
                FROM __content_type c
                WHERE c.id = :id
                LIMIT 1";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':id',$id);
        
        return $query->execute(NULL,TRUE);
    }
    
    /**
     * Определяем родительскую категорию и id файла для отрисовки
     *
     * @param  array  $categories category
     * @param  string $url        url
     * @return bool               TRUE если все хорошо
     * @uses   Post_Module::$post
     * @uses   Post_Module::$category
     */
    protected function category_valid($categories,$url){
        
        //Создаем правильный URL для поиска по категориям
        $url = explode('/',trim($url,'/'));
        array_pop($url);
        $url = implode('/',(array)$url);

        //Проверяем есть ли родительская категория
        try{
            $extends = '';
            $style_id = array();
            $parent_id = array();
            foreach($categories as $category){
                //Ищем родительскую категорию
                if($category['category_url'] == $url){
                    $parent = $category;
                    if(!empty($category['extends_content'])){
                        $parent_extends = $category['extends_content'];
                        $parent_id[] = $category['id'];
                    }
                }
                elseif(!empty($category['static'])){
                    $style_id[] = $category['id'];
                }
                //Ищем файл отрисовки если у родительской его нет
                if(empty($parent_extends) AND empty($extends) AND !empty($category['extends_content'])){
                    $extends = $category['extends_content'];
                }
            }
            if(isset($parent)){
                //Если есть расширение основной категории заменяем его
                $extends = (isset($parent_extends))? $parent_extends : $extends;
                //Собераем основной массив стилей категории
                $style_id = array_merge($parent_id,$style_id);
                
                //Заполняем нужные переменные
                $parent['extends_content'] = $extends;
                $parent['style'] = $style_id;
                $this->category =& $parent;
                $this->post['category'] = $this->category;
                return TRUE;
            }else{
                throw new Exception("Не найдено родительской категории");
            }
        }catch(Exception $e){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
            Model::factory('error','system')->error();
        }
        
    }
    
    /**
     * Проверяем есть ли в посте файл для отрисовки, если нет ищем его.
     * [!!!] Только с правильной, валидной родительской категорией.
     *
     * @uses   Post_Module::$post;
     * @uses   Post_Module::$category;
     * @return bool TRUE если все хорошо
     */
    protected function post_valid(){
        if(!isset($this->post) OR !isset($this->category))
            throw new Core_Exception('Не правильно запущенная функция заполните массивы $this->post и $this->categories');
        
        if(!empty($this->post['file_name']))
            return TRUE;
        
        if(!empty($this->category['extends_content']) AND $extends = $this->extends_content($this->category['extends_content'])){
            $this->post = Arr::merge($this->post,$extends);
            return TRUE;
        }
        
        throw new Core_Exception('Нет файла отображения, проверьте пост с id = :id',array(":id"=>$this->post['id']));
    }
}