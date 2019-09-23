<?php defined('SYSPATH') OR exit();
/**
 * @package   Tree
 * @category  Core
 */
class Core{
    // Версия и идентификатор
    const VERSION = '0.0.1 alfa';
    const TREE_ID = '0001';
    
    const TREE_HOST = 'seor';
    
    // Режимы
    const PRODUCTION  = 1;
    const STAGING     = 2;
    const TESTING     = 3;
    const DEVELOPMENT = 4;
    
    
    /**
     * @var string Используемый режим
     * @static
     */
    public static $selected_mode = Core::DEVELOPMENT;

    /**
     * @var string Логи ошибок
     * @static
     */
    public static $log_errors = FALSE;
    
    /**
     * @var  string Тип контента
     * @static
     */
    public static $content_type = 'text/html';

    /**
     * @var  string  Кодировка
     * @static
     */
    public static $charset = 'utf-8';
    
    /**
     * @var  string  Имя сервера
     * @static
     */
    public static $server_name = '';

    /**
     * @var  array   Лист хостов
     * @static
     */
    public static $hostnames = array();
    
    /**
     * @var bool кешировать ли
     * @static
     */
    public static $caching = FALSE;
    
    /**
     * @var string деректория кеширования
     * @static
     */
    public static $cache_dir;
    
    /**
     * @var string время жизни кеша
     * @static
     */
    public static $cache_life = 60;
    
    /**
     * @var  string  Базовый URL
     * @static
     */
    public static $base_url = '/';
    
    /**
     * @var  string  Индексовый файл
     * @static
     */
    public static $index_file = 'index.php';

    /**
     * @var  bool  Проверка на windows
     * @static
     */
    public static $is_windows;
    
    /**
     * @var  bool  Включить ли отработку ошибок
     * @static
     */
    public static $errors = TRUE;
    
    /**
     * @var  bool  Хост и протокол
     * @static
     */
    public static $root_url = TRUE;
    
    /**
     * @var  bool Протокол
     * @static
     */
    public static $protocol = TRUE;
    
    /**
     * @var  bool  Объект конфигурации
     * @static
     */
    public static $config = TRUE;
    /**
     * @var  bool  Объект конфигурации
     * @static
     */
    public static $ajax = FALSE;
    
    /**
     * @var  array  Проверка хоста
     * @static
     */
    public static $check_host = FALSE;
    /**
     * @var  string  имя хоста
     * @static
     */
    public static $host = FALSE;
    
    /**
     * @var  array  Типы ошибок для отображения при выключении
     * @static
     */
    public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);
    
    /**
     * @var  boolean  Проверяет, была ли вызвана основная функция
     * @static
     */
    protected static $_init = FALSE;
    
    /**
     * @var  array  основные пути
     * @static
     */
    protected static $_paths = array(APPPATH, SYSPATH);
    
    /**
     * @var  array  Список модулей
     * @static
     */
    protected static $_modules = array();
    
    /**
     * @var  int  порт https
     * @static
     */
    protected static $_httpsPort = 443;
    
    static $sample = TRUE;
    
    /*********methods***********/
    
    // Отключаем возможность вызывать конструктор.
    protected function __construct(){}
    
    /**
     * Запускает ядро
     *
     * @param   array  Для сохранения глобальных настроек. имя настройки это имя переменных в Core
     * @return  void
     */
    static function init(array $settings = NULL){
        if (Core::$_init){
            // Запрет повторного запуска
            return;
        }
        if(Core::$sample === TRUE){
            $sample = new Core;
            Core::$sample = get_class($sample);
        }
        // Инициализирует запуск
        Core::$_init = TRUE;
        
        // Запускаем буфиринизацию
        //ob_start();
        
        // Отлавливаем E_FATAL.
        //register_shutdown_function(array('Core', 'shutdown_handler'));
        // Является ли windows
        Core::$is_windows = (DIRECTORY_SEPARATOR === '\\');
        
        // Задаем директорию кеша
        if (isset($settings['cache_dir'])){
            if (!is_dir($settings['cache_dir'])){
                try{
                    // Создаем кеш директорию
                    mkdir($settings['cache_dir'], 0755, TRUE);

                    // Задать разрешение
                    chmod($settings['cache_dir'], 0755);
                }catch (Exception $e){
                    echo 'Данную директорию нельзя создать';
                }
            }
            
            // Задать путь к каталогу кэша
            Core::$cache_dir = realpath($settings['cache_dir']);
        }else{
            // Стандартный путь кеширования
            Core::$cache_dir = APPPATH.'cache';
        }
        
        //Настройки ошибок
        if (isset($settings['errors'])){
            // Включить обработку ошибок
            Core::$errors = (bool) $settings['errors'];
        }
        
        if (Core::$errors === TRUE){
            // Включить обработку исключений
            switch(Core::$selected_mode){
                case Core::DEVELOPMENT:
                    set_exception_handler(array('Core_Exception', 'handler'));
                    break;
                case Core::PRODUCTION:
                    set_exception_handler(array('Core_Exception_Production', 'handler'));
                    break;
                default:
                    set_exception_handler(array('Core_Exception', 'handler'));
                    break;
            }
            
            // Включить обработку ошибок
            set_error_handler(array('Core', 'error_handler'));
        }
        if(Core::$check_host === FALSE){
            $host = str_replace('www.','',$_SERVER['HTTP_HOST']);
            if($host == Core::TREE_HOST)
                Core::$check_host = TRUE;
        }
        
        Core::$host = $_SERVER['HTTP_HOST'];
        
        if (Core::$root_url === TRUE){
            // Протокол
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? 'https' : 'http';
            if($_SERVER["SERVER_PORT"] == Core::$_httpsPort)
                $protocol = 'https';
            Core::$protocol = $protocol;        
            Core::$root_url = $protocol.'://'.rtrim($_SERVER['HTTP_HOST']);
        }
        // Регистрация функции отключения
        register_shutdown_function(array('Core', 'shutdown_handler'));
        
        // Создаем экземпляр класса конфигураций
        Core::$config = Config::instance();
        
        // Проверка на AJAX
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
              Core::$ajax = TRUE;
          }
    }
    
    /**
     * Обеспечивает простое кэширование файлов для строк и массивов:
     *
     *     // Set the "foo" cache
     *     Core::cache('foo', 'hello, world');
     *
     *     // Get the "foo" cache
     *     $foo = Core::cache('foo');
     *
     * Все кеши хранятся как PHP-код, сгенерированный с помощью [var_export][ref-var].
     * Кэширование объектов может не работать должным образом. Хранение ссылок 
     * или объект или массив с рекурсией вызовет E_FATAL.
     *
     * Каталог кеша и время жизни кеша по умолчанию устанавливаются в [Core::init]
     *
     * [ref-var]: http://php.net/var_export
     *
     * @param   string   имя кеша
     * @param   mixed    данные в кеш
     * @param   integer  количество секунд, в течение которого кеш действителен
     * @return  mixed    for getting
     */
    public static function cache($name, $data = NULL, $lifetime = NULL){
        // Кэш-файл - это хэш имени
        $file = sha1($name).'.txt';

        // Каталоги кэша разделяются ключами, чтобы предотвратить перегрузку файловой системы
        $dir = static::$cache_dir.DIRECTORY_SEPARATOR.$file[0].$file[1].DIRECTORY_SEPARATOR;

        if ($lifetime === NULL){
            // Использовать время жизни по умолчанию
            $lifetime = static::$cache_life;
        }

        if ($data === NULL){
            if (is_file($dir.$file)){
                if ((time() - filemtime($dir.$file)) < $lifetime){
                    // Return the cache
                    try{
                        return unserialize(file_get_contents($dir.$file));
                    }catch (Exception $e){
                        // Кэш поврежден, пусть код продолжается.
                    }
                }else{
                    try{
                        // Срок действия кэша истек.
                        unlink($dir.$file);
                    }catch (Exception $e){
                        // Кэш, скорее всего, уже удален,
                        // let return happen normally.
                    }
                }
            }

            // Cache not found
            return NULL;
        }

        if ( ! is_dir($dir)){
            // Create the cache directory
            mkdir($dir, 0777, TRUE);

            // Set permissions (must be manually set to fix umask issues)
            chmod($dir, 0777);
        }

        // Force the data to be a string
        $data = serialize($data);

        try{
            // Write the cache
            return (bool) file_put_contents($dir.$file, $data, LOCK_EX);
        }catch (Exception $e){
            // Failed to write cache
            return FALSE;
        }
    }
    
    /**
     * Загрузка классов
     * @param   string  $class  имя класса
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

    /**
     * Возвращает абсолютный путь к файлу
     *         Примеры: 
     *     // Возвращает views/template.php
     *     Core::find_file('views', 'template');
     *
     *     // Возвратит media/css/style.css
     *     Core::find_file('media', 'css/style', 'css');
     *
     * @param   string   $dir   Имя директории
     * @param   string   $file  Имя файла с подкаталогом
     * @param   string   $ext   Расширение
     * @param   bool     $array Вернуть массив файлов?
     * @return  array    Список файлов, когда $array является TRUE
     * @return  string   Один путь к файлу
     */
    static function find_file($dir, $file, $ext = NULL, $array = FALSE){

        if ($ext === NULL){
            // Используем расширение по умолчанию
            $ext = EXT;
        }
        elseif ($ext){
            // Используем заданное расширение
            $ext = ".{$ext}";
        }else{
            // без расширения
            $ext = '';
        }
        
        
        
        $dir = str_replace('_', DIRECTORY_SEPARATOR, strtolower($dir));
        
        // Создание частичного пути имени файла
        $path = $dir.DIRECTORY_SEPARATOR.$file.$ext;
       
        // создаем два имени фала с большой буквой и маленькой
        $explode = explode(DIRECTORY_SEPARATOR, $path);
        $exClass = array_pop($explode);
        $exDir = implode(DIRECTORY_SEPARATOR, $explode);
        $pathUpper = $exDir.DIRECTORY_SEPARATOR.ucfirst($exClass);

        ////////////////////////////////
        //// Ищем файл
        ///////////////////////////////
        if ($array){
            // Включенные пути надо искать в обратном порядке
            $paths = array_reverse(static::$_paths);

            // Массив файлов, которые были найдены
            $found = array();

            foreach ($paths as $direct){
                if(is_file($direct.$pathUpper)){
                    // Этот путь имеет файл, добавить его в список
                    $found[] = $direct.$pathUpper;
                }
                elseif(is_file($direct.$path)){
                    // Этот путь имеет файл, добавить его в список
                    $found[] = $direct.$path;
                }
            }
        }else{
            // Файл не найден
            $found = FALSE;

            foreach (static::$_paths as $direct){
                if (is_file($direct.$pathUpper)){
                    // Путь был найден
                    $found = $direct.$pathUpper;

                    // Остановка поиска
                    break;
                }
                elseif(is_file($direct.$path)){
                    // Этот путь имеет файл, добавить его в список
                    $found = $direct.$path;
                    
                    // Остановка поиска
                    break;
                }
            }
        }

        return $found;
    }
    
    /**
     * Получаем массив путей
     *
     * @param   string  $directory Имя директории
     * @param   array   $paths     пути
     * return void
     */
     static function list_files($directory = NULL, array $paths = NULL){
        $root = FALSE;
        if ($directory !== NULL){
            if($directory == '.')
                $root = TRUE;
            // Добавление разделителя каталогов
            $directory .= DIRECTORY_SEPARATOR;
        }

        if ($paths === NULL){
            // Использовать пути по умолчанию
            $paths = static::$_paths;
        }

        // Создайте массив для файлов
        $found = array();

        foreach ($paths as $path){
            if (is_dir($path.$directory)){
                // Создайте новый каталог итератор
                $dir = new DirectoryIterator($path.$directory);

                foreach ($dir as $file){
                    // Получить имя файла
                    $filename = $file->getFilename();
                    /*if(Core::$is_windows)
                        $filename = iconv('cp1251','UTF-8',$filename);*/
                        
                    if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~'){
                        // Пропустить все скрытые файлы и резервные UNIX файлы
                        continue;
                    }

                    // Относительное имя файла
                    $key = $directory.$filename;
                    if ($file->isDir()){
                        if ($sub_dir = static::list_files($directory.$filename, $paths)){
                            if (isset($found[$key])){
                                // Добавляет список подкаталог
                                $found[$key] += $sub_dir;
                            }else{
                                // Создайте новый список подкаталог
                                $found[$key] = $sub_dir;
                            }
                        }
                    }
                    else{
                        if ( ! isset($found[$key])){
                            // Добавить новые файлы в список
                            /*if(Core::$is_windows)
                                $found[$key] = iconv('cp1251','UTF-8',realpath($file->getPathName()));
                            else
                                $found[$key] = realpath($file->getPathName());*/
                            $found[$key] = realpath($file->getPathName());
                                
                        }
                    }
                }
            }
        }

        // Отсортировать результаты по алфавиту
        ksort($found);
        return $found;
    }
     
    /**
     * Возвращает массив конфигурации для запрошенной группы.  Посмотреть
     * [configuration files](core/files/config) для более конкретной информации.
     *
     *     // Получите всю конфигурацию config/database.php
     *     $config = Core::config('database');
     *
     *     // Получите только типовую connection конфигурацию
     *     $default = Core::config('database.default')
     *
     *     // Получить только имя узла соединения по умолчанию
     *     $host = Core::config('database.default.connection.hostname')
     *
     * @param   string  $group имя группы конфигураций
     * @return  Config
     */
    static function config($group){
        static $config;
        // Если нужна подпапка
        $group = str_replace('_', DIRECTORY_SEPARATOR, strtolower($group));
        
        if (strpos($group, '.') !== FALSE){
            // Разделить группу конфигурации и пути
            list ($group, $path) = explode('.', $group, 2);
        }

        if (!isset($config[$group])){
            // Загрузка конфигурации группы в кэш
            $config[$group] = Core::$config->load($group);
        }

        if (isset($path)){
            return Arr::path($config[$group], $path, NULL, '.');
        }else{
            return $config[$group];
        }
    }
    
    /**
     * Загружает файл
     *
     * @param   string $file имя файла
     * @return  mixed
     */
    public static function load($file){
        return require $file;
    }
    /**
     * Разделяем директория/файл
     *
     * @param   string
     * @return  string
     */
    
    /**
     * Функция обработчики ошибок.
     *
     * @param  string тип ошибки
     * @param  string описание ошибки
     * @param  string в каком файле
     * @param  string на какой линии
     * @return bool
     */
    
    static function error_handler($code, $error, $file = NULL, $line = NULL){
        if(Core::$selected_mode != Core::DEVELOPMENT){
            Core::error_rout($code, $error, $file, $line);
        }
        elseif (error_reporting() & $code){
            // Эта ошибка не подавляется текущих настроек отчетности ошибки
            // Преобразовать ошибки в ErrorException
            throw new ErrorException($error, $code, 0, $file, $line);
        }
        // Не выполнять обработчик ошибок PHP
        return TRUE;
    }
    
    /** 
     * Определяет, что писать пользователю, и как выводить ошибку.
     *     
     * @param  string тип ошибки
     * @param  string описание ошибки
     * @param  string в каком файле
     * @param  string на какой линии
     * @return void
     */
    static function error_rout($code, $error, $file = NULL, $line = NULL){
        $module_error = Controller::factory('system',"error");
        
        //Основная ячейка массива для функции set()
        $default = 'error';
        $error_type = 'warning';
        $error_param = array();
        
        switch($code){
            case E_WARNING: 
                $error_param['title'] = 'Произошла незначительная ошибка.';
                $error_param['message'] = 'Разработчики уже уведомлены. Возможны искажения в отображении.';
            break;
            case E_NOTICE:
                $default = 'message';
                $error_type = 'info';
            break;
        }
        
        //Если это не тестирование, то выводим пользовательскую информацию
        if(Core::$selected_mode != Core::TESTING){
            if(!empty($error_param))
                $module_error->set($default,$error_type,$error_param);
            
            Model::factory('exception','system')->set_xml(new Core_Exception($error."<br /> <b>В файле:</b> {$file} <br /> <b>На линии:</b> {$line}",array(),$code),array('client'=>'true'));
        }else{
            $error_param = array('message'=>$error."<br /> <b>В файле:</b> {$file} <br /> <b>На линии:</b> {$line}");
            
            if(isset(Core_Exception::$php_errors[$code])){
                $error_param['title'] = Core_Exception::$php_errors[$code].': ';
            }
            $module_error->set($default,$error_type,$error_param);
        }
    }
    
    /**
     * Функция завершение работы скрипта.
     *
     * @return void
     */
    static function shutdown_handler(){
        if (!Core::$_init){
            // Не был активирован
            return;
        }
        if (Core::$errors AND $error = error_get_last() AND in_array($error['type'], Core::$shutdown_errors)){
            // Clean the output buffer
            ob_get_level() and ob_clean();
            
            // Fake an exception for nice debugging
            Core_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
           
            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }
    
    /**
     * Уничтожает глобальные переменные. И проверяет на атаку.
     *
     * @return  void
     */
    public static function globals(){
        if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])){
            // Prevent malicious GLOBALS overload attack
            echo "Обнаружена атака глобальной переменной! Запрос прерван";

            // Exit with an error status
            exit(1);
        }

        // Получить имена всех глобальных переменных
        $global_variables = array_keys($GLOBALS);

        // Удалить стандартные глобальные переменные из списка
        $global_variables = array_diff($global_variables, array(
            '_COOKIE',
            '_ENV',
            '_GET',
            '_FILES',
            '_POST',
            '_REQUEST',
            '_SERVER',
            '_SESSION',
            'GLOBALS',
        ));

        foreach ($global_variables as $name){
            // Unset the global variable, effectively disabling register_globals
            unset($GLOBALS[$name]);
        }
    }
    
    
    /**
     * Замена путей в Core::$_paths
     *
     * @param  array $array новые пути
     * @return void
     */
    static function new_paths($array){
        static::$_paths = $array;
    }
    
}
