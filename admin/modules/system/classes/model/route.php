<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Route_System_Admin{
    
    //@var string  URL
    protected $url;
    
    //@var string  имя метода по умолчанию
    protected $default_action = 'fetch';
    
    //@var array  Найденные значения
    public $fonds = array();
    
    function __construct(){}
    
    /**
     * Определяем модуль и схему повещения, работаем по схеме MVC
     * 
     * @return array возвращает модуль подключения его метод и аргументы
     */
    function init(){
        $this->url = Admin::$url;
        //Определяем URL 
        return $this->parse_url($this->url);
    }
    
    /*
     * Определяем модуль и схему повещения, работаем по схеме MVC
     * 
     * @return array возвращает модуль подключения его метод и аргументы
     */
    function default_fonds(){
        if(!isset($this->fonds))
            $this->init();
        
        $fonds = $this->fonds;
        if($fonds['action'] == $this->default_action)
            $fonds['action'] = NULL;
        return $fonds;
    }
    
    /*
     * Соберем путь к отображению из имени модуля и темы
     * 
     * @return string возвращает путь к отображению темы
     */
    function template_view(){
        if(!isset($this->fonds))
            $this->init();
        
        //Найденная строка темы 
        $fond = '';
        
        //Начальный путь к отображению темы
        $content = 'content';
        
        if($this->fonds['action'] != $this->default_action){
            $fond = "{$content}_"."{$this->fonds['module']}_{$this->fonds['action']}";
        }else{
            $fond = "{$content}_"."{$this->fonds['module']}_{$this->fonds['module']}";
        }
        return $fond;
    }
    
    /*
     * преобразует строку в нужный нам массив модуль, метод, значение
     * 
     * @param  string  
     * @return array  массив содержащий нужные параметры
     */
    protected function parse_url($url){
       
        //Массив содержащий     нужные значения для заполнения
        $fonds = array();
        $fonds['module'] = NULL;
        $fonds['action'] = $this->default_action;
        $fonds['param'] = array();
        
        $url = explode('/',trim($url,'/'));
        
        foreach($fonds as $key => $fond){
            if(!empty($url) AND $shift = array_shift($url)){
                if(!is_array($fond)){
                    $fonds[$key] = $shift;
                }else{
                    $shift = array('shift'=>$shift);
                    $fonds[$key] = Arr::merge($shift , $url);
                    break;
                }
            }else{
                break;
            }
        }
        return $this->fonds = $fonds;
    }
}