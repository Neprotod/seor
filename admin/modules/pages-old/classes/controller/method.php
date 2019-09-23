<?php defined('MODPATH') OR exit();

/*
 * ћодель определ¤ет к какому типу относитс¤ URL  
 */
class Controller_Method_Pages_Admin{
    
    public $date_format = "%Y-%m-%d";
    
    function __construct(){
        if(isset(Registry::i()->date_format))
            $this->date_format = Registry::i()->date_format;
    }
    
    /*
     * Находит и возвращает страницы
     *
     * @return array содержимое постов
     */
    function get_pages($template_id = NULL,$limit = array()){
        
        //Определяем тему если она не пришла
        if(empty($template_id)){
            if(isset(Request::i()->root_template['id']))
                $template_id = Request::i()->root_template['id'];
            else
                throw new Core_Exception('Нет темы для отображения страниц');
        }
        
        $limit = '';
        if(!empty($limit) AND is_array($limit)){
            if(isset($limit['page']) AND isset($limit['limit'])){
                $limit = "LIMIT {$limit['page']},{$limit['limit']}";
            }
            elseif(isset($limit['limit'])){
                $limit = "LIMIT {$limit['limit']}";
            }
        }
        
        $sql = "SELECT p.id, u.nicename, DATE_FORMAT(p.date,'{$this->date_format}') AS date, p.title, p.url, p.meta_title, p.description, p.robots, p.status, p.comment_status, DATE_FORMAT(p.modified,'{$this->date_format}') AS modified, ct.name AS file_name, ct.ext, ct.path
            FROM __page p
            INNER JOIN __user u ON p.id_author = u.id
            INNER JOIN __type t ON t.type = 'page'
            LEFT JOIN __type_content tc ON tc.id_table = p.id 
                            AND tc.id_type = t.id
            LEFT JOIN __content_type ct ON id_template = :template_id AND tc.content_type = ct.id
            $limit;";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':template_id',$template_id);
        return (array)$query->execute();
    }    
    
    /*
     * Получаем страницу
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_page($id = NULL,$template_id = NULL){
        if(empty($id))
            return array();
        
        //Определяем тему если она не пришла
        if(empty($template_id)){
            if(isset(Request::i()->root_template['id']))
                $template_id = Request::i()->root_template['id'];
            else
                throw new Core_Exception('Нет темы для отображения страниц');
        }
        
        $sql = "SELECT p.id, u.nicename, DATE_FORMAT(p.date,'%Y-%m-%d') AS date, p.title, p.url, p.meta_title, p.description, p.robots, p.status, p.comment_status, DATE_FORMAT(p.modified,'%Y-%m-%d') AS modified,ct.id AS content_type,p.content, p.css, p.js, ct.name AS file_name, ct.ext, ct.path, tc.id AS type_content
            FROM __page p
            INNER JOIN __user u ON p.id_author = u.id
            INNER JOIN __type t ON t.type = 'page'
            LEFT JOIN __type_content tc ON tc.id_table = p.id 
                            AND tc.id_type = t.id
            LEFT JOIN __content_type ct ON id_template = :template_id AND tc.content_type = ct.id
            WHERE p.id = :id
            LIMIT 1";
            
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        $query->param(':id',$id);
        $query->param(':template_id',$template_id);
        return (array)$query->execute(NULL, TRUE);
    }        
    /*
     * Обнавляем страницу
     *
     * @param mixed либо массив либо одно значение id page
     * @param array значения для вставки таблица => значение
     * @return mixed вернет все id
     */
    function update_page($id,array $update){
        $update = Str::key_value($update);
        
        if(!empty($update))
            $update .= ", modified = NOW()";
        
        $sql = Str::__('UPDATE __page SET :update WHERE id IN(:id)', array(':update'=>$update,':id'=>implode(',',(array)$id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();

        if(empty($result))
            return FALSE;
        return $id;
    }    
}