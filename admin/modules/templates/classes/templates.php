<?php

class Templates_Admin{
    
    //@var object экземпляр класса методов
    public $method;
    //@var object экземпляр класса type
    public $type;
    //@var object экземпляр класса style
    public $style;
    
    function index(){}
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','templates');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
    }
    
    /*
     * Модель для отображения всех тем
     */
    function fetch(){
        //Загружаем все страницы
        $templates = $this->method->get_templates();
        
        //Пункты меню
        Registry::i()->menu = 'templates';
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,array('templates'=>$templates));
    }
    /*
     * Модель для отображения всех тем
     */
    function template($id = NULL){
        
        //Загружаем тему
        $template = $this->method->get_template(intval($id));
        
        //Берем путь к теме
        /*$settings =  $this->method->template_settings($templates['name']);
        $this->method->template_file($templates['name']);*/
        
        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $id,
                    );
        
        
        /*Собераем основные данные темы*/
        
        //Картинка темы
        $image = $this->method->template_image($template['name']);
        
        
        //Все используемые типы данных
        $types = $this->type->get_types($template['name']);
        
        //Создаем нужный для нас массив
        //$types = $this->type->type_sort($types);
        
        //Все используемые стили
        $styles = $this->style->get_styles($template['name']);

        //Создаем нужный для нас массив
        //$styles = $this->style->style_sort($styles);

        $data = array();
        $data['template'] = $template;
        $data['image'] = $image;
        $data['types'] = $types;
        $data['styles'] = $styles;
        $data['style'] = Admin_Model::factory('style',Registry::i()->fonds['module']);
        $data['type'] = Admin_Model::factory('type',Registry::i()->fonds['module']);
        
        Registry::i()->title = $template['name'];
        Registry::i()->meta_title = $template['name'];
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view,$data);
    }
    
    /*
     * Вывод одного стиливого файла
     *
     * @param int id стиля
     */
    function style($type,$id_template,$type_style = NULL){
        
        $result = Admin_Model::factory(Registry::i()->fonds['action'],Registry::i()->fonds['module'])->$type($id_template,$type_style);

        $view = Registry::i()->template_view . ((!isset($result['view']))?"_{$type}":"_{$result['view']}");
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,$view,$result['date']);
    }
    
    /*
     * Вывод одного типа данных
     *
     * @param int id стиля
     */
    function type($type,$id_template,$types = NULL){
        
        $result = Admin_Model::factory(Registry::i()->fonds['action'],Registry::i()->fonds['module'])->$type($id_template,$types);

        $view = Registry::i()->template_view . ((!isset($result['view']))?"_{$type}":"_{$result['view']}");
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,$view,$result['date']);
        
        if(Request::method('post')){
            if($content = Request::post('file')){
                $type = $this->type->get_type($id);
                if(File::set_content($type['path'],$content,TRUE))
                    $this->error->set('Файл обновлен',TRUE);
                else
                    $this->error->set('Ошибка обновления файла');
            }
        }
        
        //Загружаем стиль
        $type = $this->type->get_type($id);
        $content = '';

        if(!empty($type)){
            $content = (!empty($type['path']))? File::get_content($type['path'],TRUE) : FALSE;
            if($content === FALSE){
                $this->error->set('Файла не существует');
            }
        }else{
            $this->error->set('Типа не существует');
        }
        
        $template = $this->method->get_template($type['template']);
        
        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        Registry::i()->fonds['action'] = 'template template_file '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['file'] = $type;
        $date['content'] = $content;
        $date['messages'] = $this->error->get();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_file',$date);
    }
    
}