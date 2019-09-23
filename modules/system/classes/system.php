<?php defined('MODPATH') OR exit();
/**
 * Системный модуль, запускает сесию и все такое.
 * 
 * @package    module
 * @category   system
 */
class System_Module implements I_Module{
    
    const VERSION = '1.0.0';
    
    /**
     * @var object хранит сессию
     */
    public $session;
    
    /**
     * @var object модель роутера
     */
    public $route;
    
    /**
     * @var object модель темы
     */
    public $template;
    
    function index($setting = null){}
    
    function __construct(){
        $this->xml = Module::factory("xml",TRUE);
    }
    
    /**
     * Инициализирует и запускает все основные функции такие как сессия, определяет тему итп.
     * Выводит тему
     *
     * @return void 
     */
    function init(){
        //$this->session = Session::instance();
        
        //Настройки
        $this->settings();
        
        //Подключаем системный роутер.
        $this->route = Model::factory('route','system');
        
        // Определяем тип адреса
        $founds = Registry::i()->founds = $this->route->init();
        Registry::i()->founds = &$founds;

        // Действия на сайте
        Registry::i()->action_list = $this->route->action_list();

        //Определяем тему.
        $this->template = Model::factory('template','system');
        $this->template->init();
        
        /*
        //Загружаем категории
        $category = Module::factory('category',TRUE);
        $category->set_categories();
        //Регистрируем значения.
        $category->set_array(Registry::i());
        */

        // Регистрируем сессию.
        $user = Module::factory("user",TRUE);
        // Проверка авторизации, сессия устанавливается сразу при авторизации
        if($user->auth()){
            $this->session = Registry::i()->session;
        }else{
            Registry::i()->session = Session::instance();
            
            // Определяем часовой пояс пользователя
            $user->time_zone();
        }

        // Определяем язык пользователя.
        $this->user_language();
        
        //Загружаем необходимый модуль для отрисовки.
        $class = Module::factory($founds['type'],TRUE);
        $content = $class->fetch();
        
        //Запрос для AJAX, только контент
        if(Request::in_ajax() OR isset($_GET['return'])){
            if(isset($_GET['ajax']) AND $_GET['ajax'] == "json"){
                echo json_encode($content);
                return TRUE;
            }else{
                echo $content;
                return TRUE;
            }
        }
        
        if($user->auth()){
            // Создает шапку сайта
            Registry::i()->user_header = $user->header();
        }
        
        //Собираем все данные
        $data = array();
        $data['content'] = $content;
        $data['root'] = Registry::i()->root;
        $data['site'] = Core::$root_url;
        $data['system_error'] = Controller::factory('system',"error")->output();
        $data['founds'] = $founds;
        $data['head'] = (isset(Registry::i()->header))
            ? Registry::i()->header
            :"header";
        $data['body_id'] = $founds['type'];
        if(isset(Registry::i()->additional)){
            $data['additional'] = Registry::i()->additional;
        }
        if(isset(Registry::i()->xml_header)){
            $xml_pars = "template|default::".Registry::i()->xml_header;
        }else{
            $xml_pars = "template|default::header";
        }
        if(isset(Registry::i()->xsl_header)){
            $xsl_pars = "template|default::".Registry::i()->xsl_header;
        }else{
            $xsl_pars = "template|default::header";
        }
        
        // Header
        if(isset(Registry::i()->user_header)){
            $data['header'] = Registry::i()->user_header; 
        }else{
            $data['header'] = $this->xml->preg_load($data,$xml_pars,$xsl_pars);
        }
        
        /*
        $xml_pars = "template|default::index";
        $xsl_pars = "template|default::index";
        
        echo $this->xml->preg_load($data,$xml_pars,$xsl_pars);
        */

        //Выводим тему
        echo Template::factory(Registry::i()->template['name'],'index',$data,NULL);
    }
    /**
     * Загружает настройки с базы данных
     *
     * @return void 
     */
    protected function user_language(){
        $lang = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
        $lang = strstr($lang, "-", TRUE);
        
        // Заглушка языка
        $lang = "ru";
        
        $code = Query::i()->sql("lang.get_code",array(":lang_code"=>$lang), NULL, TRUE);
        
        Registry::i()->user_language = $code;
    }
    /**
     * Загружает настройки с базы данных
     *
     * @return void 
     */
    protected function settings(){
        $query = Query::i()->sql("settings",NULL,NULL);
        
        foreach($query AS $result)
            $settings[$result['name']] = $result['value'];
        
        Registry::i()->settings = $settings;
    }
    
}