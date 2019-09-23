<?php defined('MODPATH') OR exit();


class Model_Svg_System_Admin{
    /**
     * @var array все найденные значки
     */
    static $old_list = array();
    
    /**
     * @var array все найденные значки
     */
    public $svg_list = array();
    
    /**
     * @var string путь к файлу со значками
     */
    public $path = '';
    
    
    /**
     * Загружает список SVG значков, путь должен быть задан до папки svg/svg.php
     *
     * @param   string   $path путь к папке с файлом svg.php
     * @return  void
     */
    function __construct($path = NULL){
        if(empty($path)){
            if(isset(Registry::i()->root))
               $path = Registry::i()->root; 
            else
                throw new Core_Exception("Путь не задан");
        }
        $path = realpath(trim($path,"/\\") . DIRECTORY_SEPARATOR . "svg" . DIRECTORY_SEPARATOR . "svg.xml");
        
        // Если уже существует запуск по пути, не нужно снова считать файл.
        if(isset(self::$old_list[$path])){
           $this->svg_list =  self::$old_list[$path];
           $this->path = $path;
           return TRUE;
        }
        
        if(is_file($path)){
            //$this->svg_list = require($path);
            $this->svg_list = $this->pars($path);
            self::$old_list[$path] = $this->svg_list;
            $this->path = $path;
        }else{
            throw new Core_Exception("Нет файла по пути <b>:path</b>",array(":path"=>$path));
        }
        return TRUE;
    }
    
    /**
     * Выдает значок SVG
     *
     * @param   string  имя значка
     * @return  string
     */
    function get_svg($name){
        if(is_string($name)){
            if(isset($this->svg_list[$name])){
                return $this->svg_list[$name];
            }else{
                throw new Core_Exception("Нет такого значка как <b>:name</b> в <b>:path</b>",array(":name"=>$name,":path"=>$this->path));
            }
        }
        throw new Core_Exception("Не строковые значения не допускаются");
    }
    
    protected function pars($path){
        $return = array();
        $xml = new DOMDocument();
        $xml->load($path);
        
        $root = $xml->documentElement;
        
        if($childs = $root->childNodes){
            foreach($childs AS $child){
                if($child->nodeType == 1){
                    $name = $child->tagName;
                    $return[$name] = '';
                    foreach($child->childNodes AS $c){
                        $return[$name] .= $xml->saveXML($c);
                    }
                }
            }
        }
        return $return;
       
    }
}