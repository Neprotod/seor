<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Page_Pages_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','pages');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Module::factory('error',TRUE);
        $this->url = Admin_Model::factory('url','system');
        
        $this->xml = Module::factory('xml',TRUE);
        
        //Подключаем редактор
        Registry::i()->editor = TRUE;
        
        /*
        $this->xml->style('default');
        */
    }
    
    function get($id){
        
    }
    
    
    /*
     * Создает страницу
     *
     */
    function create(){
        
    }
    
    /*
     * Создает отображения стилей
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение стилей
     */
    protected function style($template,$param = NULL){
        
    }
    
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение типов
     */
    protected function type($template,$param = NULL){
        
    }
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение типов
     */
    protected function post(&$page,$template){
        
    }
    
}