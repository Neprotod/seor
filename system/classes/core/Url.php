<?php defined('SYSPATH') OR exit();
/**
 * Возвращает URL и делает всевозможные проверки
 * 
 * @package   Tree
 * @category  Core
 */
class Core_Url{
    /**
     * @var  string  Содержит в себе путь адреса
     */
    public $url;
    
    /**
     * @var  string  Содержит в себе оригинальный путь адреса, сам $_SERVER['REQUEST_URI']
     */
    public $url_original;
    
    /**
     * @var  string  Содержит в "?query string"
     */
    public $query;
    
    /**
     * @var  array  запросы функции query
     * @static
     */
    static $query_function = array();
    
    /**
     * @var  string  без начального слеша
     */
    public $out;
    
    /**
     * @var  object образец этого класса
     * @static 
     */
    private static $_instance;
    /*********methods***********/
    
    
    /**
     * Создаем класс и заполняем переменные
     *
     * @return  void
     */
    private function __construct(){
        $url = $this->url_original = $_SERVER['REQUEST_URI'];
        $query = $_SERVER['QUERY_STRING'];
        // Если есть строка подзапроса 
        if(!empty($query)){
            $str = explode('?' . $query,$url);
            $url = $str[0];
            //Заполняем если есть строка подзапроса
            $this->query = $query;
        }

        // Уберем последний и первый слеш
        $url = trim($url, '/');
        
        //Заполняем
        $this->url = $url;

    }
    
    /**
     * Создает или возвращает себя
     *
     * @return  object self
     */
    static function instance(){
        if(isset(self::$_instance)){
            return self::$_instance;
        }
        return self::$_instance = new Url();
        
    }
    /**
     * Псевдоним instance() 
     *
     * @return  object self
     */
    static function i(){
        return self::instance();
        
    }
    
    /**
     * Добавить к базовому
     *
     *  Если $bool если TRUE использовать Core::$base_url
     *  FALSE использовать $url->url
     *  NULL  вернет ссылку без изменений с QueryStrng
     *
     * @param  bool    $bool   (TRUE|FALSE|NULL) 
     * @return string
     */
     
    static function site($link = NULL,$bool = FALSE){
        $url = self::instance();
        $return = Core::$root_url . $url->root($bool) . "/" . $link;
        $return = rtrim($return,"/");
        return $return;
    }
    /**
     * Базовый URL
     *
     *  Если $bool если TRUE использовать Core::$base_url
     *  FALSE использовать $url->url
     *  NULL  вернет ссылку без изменений с QueryStrng
     *
     * @param  bool    $bool   (TRUE|FALSE|NULL) 
     * @return string
     */
     
    static function root($bool = TRUE){
        $url = self::instance();
        if($bool === TRUE){
            $root = '/' . Core::$base_url;
        }
        elseif($bool === FALSE){
            $root = '/' . $url->url;    
        }
        elseif($bool === NULL){
            $root = '/' . $url;
        }
        
        // Проверка на начальную страницу.
        if($root != '/')
            return rtrim($root, '/');
        
        return $root;
    }
    /**
     * Для запросов GET
     *
     * Когда $char стоит в 'auto' то берется весь запрос с $_GET и сливается с $gets
     *
     * @param   array   $gets
     * @param   mixed   $char [NULL|'auto'|'&']каким символом разделять 
     * @return  string
     */
    static function query($gets = array(),$char = NULL){
        // для сохранения запроса
        $query = '';
        
        // Если auto автоматически определяем
        if($char == 'auto'){
            if(!empty($_GET)){
                $char = '?';
                $gets = Arr::merge($_GET, $gets);
            }else{
                $char = '?';
            }
        }

        if($char === NULL){
            $char = '?';
        }
        
        
        if(!is_array($gets))
            return '';
            
    
        
        foreach($gets as $var => $value){
            if((is_scalar($value) AND !is_bool($value))){
                if(!empty($value))
                    $query .= $char.$var.'='.$value;
                else
                    $query .= $char.$var;
                $char = '&';
            }
        }
        return $query;
    }
    
    /**
     * Сливает query запрос с основной ссылкой
     *
     * Когда $char стоит в 'auto' то берется весь запрос с $_GET и сливается с $gets
     *
     * @param   array   $gets
     * @param   bool    $bool 
     * @param   mixed   $char 
     * @return  string
     */
    static function query_root($gets = array(), $bool = TRUE, $char = NULL){
        $query_root = self::root($bool);
        $query_root .= self::query($gets,$char);
        return $query_root;
    }
    /**
     * Добавление URL
     *
     * @return  string
     */
    static function update($id, $url, $id_canonical = NULL){
        
        $sql = Admin_Model::factory("sql","system");
        
        $urls = array(
            "url" => $url,
            "id_canonical" => $id_canonical
        );
        $update_table = $sql->update(',', $urls);
        
        $old = Admin_Query::i()->sql("url.id",array(":id"=>$id),NULL, TRUE);
        
        Admin_Query::i()->sql("update",array(
                                                ":set"=>$update_table,
                                                ":id"=>$id,
                                                ":table"=>"url"
                                            ));
        
        Admin_Query::i()->sql("url.insert_url_drop",array(
                                                        ":url_reset" => $old["url"],
                                                        ":id_url" => $id
                                                    ));
        return TRUE;
    }
    /**
     * Добавление URL
     *
     * @return  string
     */
    static function delete($id){
        $sql = Admin_Model::factory("sql","system");
        
        $id = $sql->insert_string(array($id));

        return Admin_Query::i()->sql("delete",array(
                                            ":table"=>"url",
                                            ":where"=>"id",
                                            ":insert"=>$id,
                                            ));
    }
    /**
     * Выводит всю строку
     *
     * @return  string
     */
    function __toString(){
        
        $q = '';
        $query = '';
        
        if(isset($this->query)){
            $q = '?';
            $query = $this->query;
        }
        
        return $this->url.$q.$this->query;
    }
}
