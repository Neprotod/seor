<?php

class Contacts_Admin{
    
    /**
     * @var object методы для обработки
     */
    public $method;
    /**
     * @var object для работы с XML
     */
    public $xml;
    /**
     * @var object обработки ошибок
     */
    public $error;
    /**
     * @var string путь к XML обращениям
     */
    public $xml_contacts;
    /**
     * @var string путь к модулю
     */
    public $path;
    /**
     * @var string путь к стилям
     */
    public $css;
    function __construct(){
        $this->method = Admin_Controller::factory('method','contacts');
        $this->xml = Module::factory('xml',TRUE);
        $this->error = Module::factory('error',TRUE);
        
        $this->path = Admin_Module::mod_path('contacts',TRUE);
        $this->css = Url::i()->root().$this->path.'css/contacts.css';
        
        $this->xml_contacts = Admin_Module::mod_path('contacts').'xml'.DIRECTORY_SEPARATOR.'contacts.xml';
    }
    
    function fetch(){
        Registry::i()->title = "Обращение в тех. поддержку";
        
        $data = array();
        $data['xml'] = $this->method->get_appeal($this->xml_contacts);
        $data['header'] = $this->header();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,$data);
    }
    
    function app(){
        if(Request::method('post')){
            if($return = $this->method->add_appeal($this->xml_contacts)){
                header("Location: ".Url::i()->root()."/contacts/get/{$return['id']}?appeal=complete");
                exit();
            }else{
                $this->error->set('error','error',array('message'=>'Ошибка вставки обращения.'));
            }
        }
        
        $data = array();
        $data['error'] = $this->error->output();
        $data['header'] = $this->header();
        
        Registry::i()->title = "Обращение";
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,$data);
    }

    function get($id){
        
        if(Request::method('post')){
            if($return = $this->method->add_message($this->xml_contacts,$id)){
                header("Location: ".Url::i()->root()."/contacts/get/{$return['id']}?post=complete");
                exit();
            }else{
                $this->error->set('error','error',array('title'=>'Ошибка вставки сообщения.','message'=>'Возможно обращение удалено.'));
            }
        }
        if(Request::get('post') == 'complete'){
            $this->error->set('message','success',array('message'=>'Обращение отправлено'));
        }
        if(Request::get('appeal') == 'complete'){
            $this->error->set('message','success',array('title'=>'Обращение создано','message'=>'Наши специалисты решат ваш вопрос в ближайшее время. Вы получите уведомление по почте.'));
        }
        $data = array();
        $data['xml'] = $this->method->get($this->xml_contacts,$id);
        $data['error'] = $this->error->output();
        $data['header'] = $this->header();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,$data);
    }
    
    function header(){
        $header = '<link type="text/css" rel="stylesheet" href="'.$this->css.'" />';
        
        //Загружаем тему
        return $header;
    }
    
}