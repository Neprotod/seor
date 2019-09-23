<?php

class Posts_Admin{
    
    //@var object контроллер method 
    protected $method;
    
    function index(){}
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','posts');
    }
    
    /*
     * Модуль предназначен для отображения всех страниц
     */
    function fetch(){
        $template = Registry::i()->root_template;

        //Загружаем все посты
        $posts = $this->method->get_posts($template['id']);

        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,array('posts'=>$posts));
    }
    
    /*
     * Модуль предназначен для отображения одной страницы
     */
    function post($type,$id,$category = NULL){
        
        $result = Admin_Model::factory(Registry::i()->fonds['action'],Registry::i()->fonds['module'])->$type($id,$category);

        $view = Registry::i()->template_view . ((!isset($result['view']))?"_{$type}":"_{$result['view']}");

        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,$view,$result['date']);
    }
    
    /***************************
     * Дополнительные функции
     ***************************/
    function status($id,$status){
        $this->method->update_page($id,array('status'=>$status));
        header("Location: /admin/pages");
        exit();
    }
}