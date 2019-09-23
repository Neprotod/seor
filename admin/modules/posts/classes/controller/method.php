<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относитс¤ URL  
 */
class Controller_Method_Posts_Admin{
    
    public $date_format = "%Y-%m-%d";
    
    function __construct(){
        if(isset(Registry::i()->date_format))
            $this->date_format = Registry::i()->date_format;
    }
    
    /*
     * Находит и возвращает категории
     *
     * @return array содержимое постов
     */
    function get_posts($template_id = NULL,$category = NULL,$limit = array()){
        
        //Определяем тему если она не пришла
        if(empty($template_id)){
            if(isset(Request::i()->root_template['id']))
                $template_id = Request::i()->root_template['id'];
            else
                throw new Core_Exception('Нет темы для отображения страниц');
        }
            
        
        $sql = "SELECT c.id, u.nicename, DATE_FORMAT(c.date,'%Y-%m-%d') AS date, c.title, c.url, c.meta_title, c.description, c.robots, c.status, DATE_FORMAT(c.modified,'%Y-%m-%d') AS modified, ct.name AS file_name, ct.ext, ct.path, ext.name AS extends_file_name, ext.ext AS extends_ext, ext.path AS extends_path
            FROM __post c
            INNER JOIN __user u ON c.id_author = u.id
            INNER JOIN __type t ON t.type = 'post'
            LEFT JOIN __type_content tc ON tc.id_table = c.id 
                                AND tc.id_type = t.id
            LEFT JOIN __content_type ct ON ct.id_template = :template_id AND tc.content_type = ct.id
            LEFT JOIN __content_type ext ON tc.extends_content = ext.id;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':template_id',$template_id);
        return (array)$query->execute();
    }    
    
    /*
     * Получаем категорию
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_post($id = NULL,$template_id = NULL,$category = NULL){
        if(empty($id))
            return array();
        
        //Определяем тему если она не пришла
        if(empty($template_id)){
            if(isset(Request::i()->root_template['id']))
                $template_id = Request::i()->root_template['id'];
            else
                throw new Core_Exception('Нет темы для отображения страниц');
        }
        
        $sql = "SELECT c.id, u.nicename, DATE_FORMAT(c.date,'%Y-%m-%d') AS date, c.title, c.url, c.meta_title, c.description, c.robots, c.status, DATE_FORMAT(c.modified,'%Y-%m-%d') AS modified, c.content, c.annotation, ct.id AS content_type, ct.name AS file_name, ct.ext, ct.path, ext.id AS extends_content, ext.name AS extends_file_name, ext.ext AS extends_ext, ext.path AS extends_path, tc.id AS type_content, c.css, c.js
            FROM __post c
            INNER JOIN __user u ON c.id_author = u.id
            INNER JOIN __type t ON t.type = 'post'
            LEFT JOIN __type_content tc ON tc.id_table = c.id 
                                AND tc.id_type = t.id
            LEFT JOIN __content_type ct ON ct.id_template = :template_id AND tc.content_type = ct.id
            LEFT JOIN __content_type ext ON tc.extends_content = ext.id
            WHERE c.id = :id
            LIMIT 1;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':id',$id);
        $query->param(':template_id',$template_id);
        return (array)$query->execute(NULL, TRUE);
    }
    
    /*
     * Обнавляем страницу
     *
     * @param mixed либо массив либо одно значение id category
     * @param array значения для вставки таблица => значение
     * @return mixed вернет все id
     */
    function get_category_post($id){
        
        $sql = "SELECT id = :id
            LIMIT 1;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':id',$id);
        return (array)$query->execute();
    }
}