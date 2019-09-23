<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Page_Module implements I_Module{

    const VERSION = '1.0.0';
    
    public $date_format = "%Y-%m-%d'";
    
    function index($setting = null){}
    
    function __construct(){}
    
    /**
     * Вывод содержимого страницы
     *
     * @return string контент страницы
     */
    function fetch(){
        $id_table = Registry::i()->founds['id_table'];
        $url =      Registry::i()->founds['url'];
        
        // Получаем страницу
        $page = $this->get_page($id_table);

        $template = Model::factory('template','system');
        
        $path = $template->path($page);

        //Находим стили относящиеся к данной странице.
        $style = Model::factory('style','system');

        $return = $style->init($page);
        $class = "type_";
        if(isset($page["class"])){
            $class .= $page["class"];
        }else{
            $class .= "all";
        }
        $data = array();
        $data["content"] = Model::factory($class,"page",array($page))->fetch();
        
        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,$data);
        
    }
    
    /**
     * Находит и возвращает страницу
     *
     * @param  int   $id id страницы
     * @return array     содержимое страницы
     */
    function get_page($id){
        
        /*                                                        
        $sql = "SELECT p.id, u.nicename, p.date, p.title, p.url, p.meta_title, p.description, p.robots, p.status, p.comment_status, p.modified, p.content, p.annotation, c.name AS file_name, c.ext, c.path, p.css, p.js
            FROM __page p
            INNER JOIN __user u ON p.id_author = u.id
            INNER JOIN __content_type c ON p.content_type = c.id
            WHERE p.id = :id LIMIT 1;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        
        $query->param(':id',$id);
*/      
        try{
            $result = Query::i()->sql("pages.get_page",array(
                                                                ":date_format"=>$this->date_format,
                                                                ":id"=>$id,
                                                                ), NULL, TRUE);
           
            if($result)
                return $result;
            
            throw new Exception("Страница с id = <b>{$id}</b> не найдена в таблице page");
        }catch(Exception $e){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
            Model::factory('error','system')->error();
        }
        
    }
}