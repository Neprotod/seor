<?php

class Pages_Admin{
    
    //@var object контроллер method 
    protected $method;
    
    function index(){}
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','pages');
    }
    
    /*
     * Модуль предназначен для отображения всех страниц
     */
    function fetch(){
        $template = Registry::i()->root_template;

        //Загружаем все страницы
        $pages = $this->method->get_pages($template['id']);

        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,array('pages'=>$pages));
    }
    
    /*
     * Модуль предназначен для отображения какого - то отпределенного количества страниц
     *
     * @param int страница, по умочанию 1
     */
    function pages($page = 1){
        $template = Registry::i()->root_template;

        //Загружаем все страницы
        $pages = $this->method->get_pages($template['id']);
        
        exit();
    }
    
    /*
     * Модуль предназначен для создания страниц
     *
     * @param int страница, по умолчанию 1
     */
    function create(){
        $result = Admin_Model::factory('page',Registry::i()->fonds['module'])->create();
        
        $view = Registry::i()->template_view ;
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,$view,$result['date']);
    }
    
    
    /*
     * Модуль предназначен для отображения одной страницы
     */
    function page($type,$id){
        
        $result = Admin_Model::factory(Registry::i()->fonds['action'],Registry::i()->fonds['module'])->$type($id);

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

/*
        //Значения по умолчанию
        $orders = array(
            'order'=> 'position',
            'sort'=> 'ASC'
        );
        
        //Если есть значения переназначем и создаем строку для сортировки
        if(!empty($sort) AND is_array($sort)){
            $orders = Arr::merge($orders,$sort);
        }
        $order = "ORDER BY {$orders['order']} {$orders['sort']}";
        
        //Определяем лимит
        $limit = '';
        if($get_num !=== NULL){
            $limit = "LIMIT {$get_num}";
        }
        if($start_num !=== NULL AND $get_num !== NULL){
            $limit .= " OFFSET {$start_num}";
        }
        
*/