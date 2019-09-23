<?php
    
/*
 * Возвращает URL и делает всевозможные проверки
 * 
 * @package   Tree
 * @category  Base
 */
class Url{
    /**
     * @var  string  Содержит в себе путь адреса
     */
    public $url;
    
    /**
     * @var  string  Содержит в "?query string"
     */
    public $query;
    /**
     * @var  array  запросы функции query 
     */
     
    static $query_function = array();
    /**
     * @var  string  без начального слеша
     */
    public $out;
    
    private static $_instance;
    /*********methods***********/
    
    
    /*
     * Создаем класс и заполняем переменные
     * @return  void
     */
    private function __construct(){
        $url = $_SERVER['REQUEST_URI'];
        $query = $_SERVER['QUERY_STRING'];
        // Если есть строка подзапроса 
        if(!empty($query)){
            $str = explode('?' . $query,$url);
            $url = $str[0];
            //Заполняем если есть строка подзапроса
            $this->query = $query;
        }

        // Убераем последний и первый слеш
        $url = trim($url, '/');
        
        //Заполняем
        $this->url = $url;

    }
    static function instance(){
        if(isset(self::$_instance)){
            return self::$_instance;
        }
        return self::$_instance = new Url();
    }
    
    /*
     * Базовый урл
     *
     * @return  string
     */
     
    static function root($bool = TRUE){
        $url = self::instance();
            
        if($bool === TRUE){
            $root = '/' . '';
        }
        elseif($bool === FALSE){
            $root = '/' . $url->url;    
        }
        elseif($bool === NULL){
            $root = '/' . $url;    
        }
        return rtrim($root, '/');
    }
    /*
     * Для запросов GET
     *
     * @return  string
     */
     
    static function query($gets = array(),$char = NULL){
        // для сохранения запроса
        $query;
        
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
            if(!empty($value)){
                $query .= $char.$var.'='.$value;
                $char = '&';
            }
        }
        return $query;
    }
    
    static function query_root($gets = array(), $bool = TRUE, $char = NULL){
        $query_root = self::root($bool);
        $query_root .= self::query($gets,$char);
        return $query_root;
    }
    /*
     * Выводит всю строку
     *
     * @return  string
     */
    function __toString(){
        if($this->query)
            $q = '?';
        return $this->url.$q.$this->query;
    }
}
