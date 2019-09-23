<?php defined('SYSPATH') OR exit();
/**
 * @package   Tree
 * @category  Admin
 */
class Admin extends Core{
    
    /**
     * @var  string  Базовый URL
     */
    public static $base_url = '/';
    
    public static $url;
    
    protected static $_paths = array(SYSPATH_ADMIN, DESIGN_ADMIN);
    
    
    ////////////////////////
    ///// Запуск CMS
    ////////////////////////
    /**
     * Запуск CMS
     *
     * @param   bool $no_return  
     * @return  void
     */
    function __construct($no_return = FALSE){
        //Core::$selected_mode = Core::DEVELOPMENT;

        Core::$_paths = array(APPPATH, SYSPATH);
        Core::init();

        Core::$sample = get_class($this);
        /*Узнаем корневое УРЛ*/
        $url = explode('/', URL::instance()->url);
        
        Core::$base_url = array_shift($url);
        
        self::$url = implode('/',$url);
        
        unset($url);
        /*
         * Запускаем пути подключения путей
         */
        Core::$config->attach(new Config_File);
        Cookie::$salt = md5('salt');
        // Загружаем модули

        Module::module_path(TRUE);
        
        Admin_Module::module_path(TRUE);
        
        // Загружаем административные SQL запросы
        Admin_Query::i();
        
        // Загружаем SQL запросы ядра
        Query::i();
        
        // Какую сессию использовать
        Session::$default = 'native';
        
        // Модуль аутентификации
        Admin_Module::factory('auth');
        Registry::i()->auth = Auth_Admin::i();
 
        /********
        *Определение пользователя
        **********/
        //$user = Admin_Module::load('user','index');
        if(!$no_return){
            // Подключаем основной модуль отображения
            $this->system = Admin_Module::factory('system',TRUE);
        }

    }
    /**
     * Инициализируем сессию и тему
     *
     * @return  void
     */
    function render(){
        // Инициализируем сессию и тему
        $this->system->init();
    }
    
    /**
     * Буфиринизируем все данные и выводит результат
     *
     * @return  void
     */
    function execute(){
        // Буфиринизируем все данные
        ob_start();
        $this->render();
        $buffer = ob_get_clean();
        
        // Начинаем строить вывод
        header('Content-Type: text/html; charset=utf-8');
        echo $buffer;
    }
    /**
     * Auto Load переназначенный с ядра
     *
     * @return  bool
     */
    static function auto_load($class){
        try{
            // Transform the class name into a path
            $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));

            if($path = static::find_file('classes', $file)){
                // Подключаем класс файл
                require $path;

                // Класс найден
                return TRUE;
            }else{
                // Запускаем руками auto_load ядра
                Core::auto_load($class);
            }

            // Не найден данный класс
            return FALSE;
        }catch (Exception $e){
            echo 'Не удалось подключить<pre>';
            echo $e->getMessage() . "<br /><br />";
            print_r($e->getTrace());
            echo '</pre>';
            exit();
        }
    }
}
