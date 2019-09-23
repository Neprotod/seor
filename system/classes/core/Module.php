<?php defined('SYSPATH') OR exit();
/**
 * Для подключения модулей.
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Module{
    /**
     * @var array Содержит класс Config_Reader
     */
    protected static $_Config_Reader;
    /**
     * @var array Содержит файл конфигураций
     */
    protected static $_config = array();
    /**
     * @var array содержит имена модулей
     */
    protected static $_modules = array();
    
    /**
     * @var array содержит массив модуль - путь
     */
    protected static $_modules_path = array();
    
    /**
     * @var array содержит все запущенные ранее модули
     */
    protected static $return_module = array();
    
    /**
     * @var array пути для дополнительных включений
     */
    public static $app = array(APPPATH);
    
    /**
     * @var array пути модулей
     */
    public static $path = array(MODPATH);
    
    /**
     * Создает пути к модулю
     *
     * @param  array $modules модули которым нужно создать пути если NULL то возращает пути
     * @return void
     */
    static function module_path($modules = NULL){
        // Пути по умолчанию для модулей
        $default_array = array();
        
        if ($modules === NULL){
            // Вернуть подключенные модули
            return static::$_modules;
        }
        
        // Добавляем к пути приложения папку модуль
        if(!empty(static::$app))
            foreach(static::$app as $app)
                $default_array[] = $app.'modules'.DIRECTORY_SEPARATOR;
        
        // Пути по умолчанию для модулей
        if(!empty(static::$path))
            foreach(static::$path as $path)
                $default_array[] = $path;

        // Сливаем два массива.
        if(!empty(static::$_modules)){
            $difference = Arr::merge(static::$_modules,$modules);
            //sort($difference);
            $modules = $difference;
        }
        ///////////////
        // Создаем директории включения
        ///////////////
        
        //Если TRUE заполнить с имен папок
        elseif($modules === TRUE){
            $modules = static::generator_module_path($default_array);
        }
        elseif(!is_array($modules)){
            throw new Core_Exception('Пришел не массив');
        }
        ///////////////
        // Заполняем пути
        ///////////////
        foreach ($default_array as $key => $path){
            foreach($modules as $key => $module){
                if(is_dir($path.$module)){

                    // временный путь
                    $temp_path = realpath($path.$module).DIRECTORY_SEPARATOR;

                    static::$_modules[] = $modules[$key];    
                    
                    // сохраняем модуль - путь для подключения моделей, контроллеров и view
                    static::$_modules_path[strtolower($modules[$key])] = $temp_path;
                    
                    unset($modules[$key]);
                }
            }
        }

        // Удаляем все не найденные модули
        unset($modules);
        
    }
    
    /**
     * Подключает найденные модули
     *
     * @param  array   $module   имя модуля
     * @param  string  $index    имя метода
     * @param  array   $settings аргументы
     * @return mixed             результат
     */
    static function load($module = NULL, $index = NULL, array $settings = null){
        // Подключаем файл модуля
        static::factory($module,FALSE);
        // Создаем правильное имя модуля
        $module = static::name($module);

        if(method_exists($module, $index)){
            $return = static::execution($module,$index,$settings);
        }else{
            throw new Core_Exception('Нет метода <b>:index</b> в модуле <b>:module</b>',array(":index"=>$index,":module"=>$module));
        }
    
        return $return;
    }
    
    /**
     * Проверка на параметры
     *
     * @param object $method reflectionMethod
     * @return void
     */
    private static function check($method){
        // Берем параметры метода
        $params = $method->getParameters();
        
        // Если нет параметров выводим исключение
        if(empty($params))
            throw new Core_Exception('Нет аргумента в классе <b>:class</b> методе <b>:name</b>',array(":class"=>$method->class,":name"=>$method->name));
    }
    
    /**
     * Создаем имя модуля
     *
     * @param object $module reflectionMethod
     * @return void
     */
    static function name($module){
        return $module . "_Module";
    }
    
    /**
     * Выполнение модуля
     *
     * @param  string $module   имя модуля
     * @param  string $index    имя метода который нужно запустить
     * @param  array  $settings настройки модуля
     * @return string          вывод модуля
     */
    private static function execution($module,$index,$settings){
        $method = new ReflectionMethod($module, $index);
        
        //Если есть аргументы
        if(!empty($settings) AND is_array($settings)){
            $str = '';
            foreach($settings as $key => $value)
                $str .= '$settings["'.$key.'"],';
            $str = trim($str,',');
            
            // Вывод с помощью eval
            return eval('return $method->invoke(new $module, '.$str.');');
        }
        
        return $method->invoke(new $module);
    }
    
    /**
     * Выдает модуль - путь
     *
     * @param  string $module имя модуля если NULL вернет все пути.
     * @param  bool   $bool   если нужно заменить слеши и превратить в относительный путь TRUE
     * @return string         путь модуля, FALSE в случае неудачи
     */
    static function mod_path($module = NULL,$bool = FALSE){
        if($module === NULL){
            return static::$_modules_path;
        }
        $module = strtolower($module);
        if(isset(static::$_modules_path[$module])){
            if($bool === FALSE){
                return static::$_modules_path[$module];
            }else{
                $return = str_replace(DOCROOT, '/', static::$_modules_path[$module]);
                return str_replace('\\', '/', $return);
            }
        }
        // если не найдено
        throw new Core_Exception('Модуль <b>:module</b> не найден',array(":module"=>$module));
    }
    
    /*
     * Генератор путей модуля
     *
     * @param  array начальные папки, которые нужно просканировать
     * @return array пути всех модулей
     */
    static function generator_module_path($default_array){
        
        if(!is_array($default_array))
            throw new Core_Exception('Пришел не массив');
        
        foreach($default_array as $path){
            $scans = scandir($path);
            foreach($scans as $scan){
                if(($scan != '.' AND $scan != '..') AND is_dir($path.$scan))    
                    $result[] = $scan; 
            }
        }
        $result = array_unique($result);
        return $result;
    }
     
    /**
     * Подключаем модуль
     *
     * @param  string $module   имя модуля
     * @param  bool   $bool     FALSE только подключить, TRUE создать экземпляр 
     * @param  array  $settings передать аргументы в конструктор
     * @param  bool   $new      TRUE принудительно пересоздать класс
     * @return object           путь модуля, FALSE в случае неудачи
     */
    static function factory($module, $bool = FALSE, $settings = NULL,$new = FALSE){
        if(!is_bool($bool))
            throw new Core_Exception("Второй аргумент может быть только типа boolean, а пришло <b>:arg</b>",array(":arg"=>$bool));
        // Абсолютный путь к файлу
        $path = static::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$module.EXT;
        
        // Создаем имя
        $class = static::name($module);
        
        // Проверка существующий ли данный Модуль
        if(is_file($path)){
            if(!class_exists($class, FALSE)){
                require $path;
                // Еще раз проверяем на существование, это поможет избежать Fatal error
                if(!class_exists($class, FALSE)){
                    throw new Core_Exception('Нет модуля <b>:class</b> в файле <b>:path</b>',array(":class"=>$class,":path"=>$path));
                }
            }
        }else{
            throw new Core_Exception('Модуль <b>:module</b> не найден',array(":module"=>$module));
        }
        if($bool === TRUE){
            //Соберем строку для вставки в массив
            $key_array = $path;
            if(is_array($settings)){
                $key_array .= serialize($settings);
            }
            $key_array = md5($key_array);
            
            if(array_key_exists($key_array,static::$return_module) AND $new === FALSE){
                return clone static::$return_module[$key_array];
            }
            
            //Если есть аргументы
            if(!empty($settings) AND is_array($settings)){
                $str = '';
                foreach($settings as $key => $value)
                    $str .= '$settings["'.$key.'"],';
                $str = trim($str,',');

                // Вывод с помощью eval
                return clone static::$return_module[$key_array] = eval('return new $class('.$str.');');
            }
            
            return clone static::$return_module[$key_array] = new $class;
            
        }else{
            return $class;
        }
    }
    /**
     * Подключаем интерфейсы внутри модуля (class/implements)
     *
     * @param  string   $interface  имя интерфейса
     * @param  string   $module     имя модуля 
     */
    static function implement($interface, $module){
        // Создаем имя
        $name = "I_".$module."_".$interface;
        $class = static::name($name);
        
        if(!interface_exists($class, FALSE)){
            // Абсолютный путь к файлу
            $path = static::mod_path($module).'classes'.DIRECTORY_SEPARATOR."implements".DIRECTORY_SEPARATOR.$interface.EXT;
            // Проверка существующий ли данный интерфейс
            if(is_file($path)){
                    require $path;
                    // Еще раз проверяем на существование, это поможет избежать Fatal error
                    if(!interface_exists($class, FALSE)){
                        throw new Core_Exception('Нет интерфейса <b>:class</b> в файле <b>:path</b>',array(":class"=>$class,":path"=>$path));
                    }
                
            }else{
                throw new Core_Exception('Не найдет файл интерфейса по пути  <b>:path</b>',array(":path"=>$path));
            }
        }
    }
    /**
     * Подключаем интерфейсы внутри модуля (class/abstract)
     *
     * @param  string   $abstract имя абстрактного класса
     * @param  string   $module   имя модуля 
     */
    static function abstracts($abstract, $module){
        // Создаем имя
        $name = "A_".$module."_".$abstract;
        $class = static::name($name);
        
        if(!class_exists ($class, FALSE)){
            // Абсолютный путь к файлу
            $path = static::mod_path($module).'classes'.DIRECTORY_SEPARATOR."abstract".DIRECTORY_SEPARATOR.$abstract.EXT;
            // Проверка существующий ли данный интерфейс
            if(is_file($path)){
                    require $path;
                    // Еще раз проверяем на существование, это поможет избежать Fatal error
                    if(!class_exists ($class, FALSE)){
                        throw new Core_Exception('Нет абстрактного класса <b>:class</b> в файле <b>:path</b>',array(":class"=>$class,":path"=>$path));
                    }
                
            }else{
                throw new Core_Exception('Не найдет файл интерфейса по пути  <b>:path</b>',array(":path"=>$path));
            }
        }
    }
    
    /**
     * Получить путь к файлу из папки модуля
     *
     * @param string $file   имя файла
     * @param string $dir    директория внутри модуля
     * @param string $module имя модуля
     * @param string $ext    расширение
     * @return string буфиринизированый файл
     */
    static function file_path($file, $dir = NULL, $module, $ext = NULL){
        // Абсолютный путь к файлу
        $path = static::mod_path($module);
        
        if(!empty($dir)){
            $dir = str_replace('_', DIRECTORY_SEPARATOR, strtolower($dir)).DIRECTORY_SEPARATOR;
        }
        if($ext === NULL){
            $ext = EXT;
        }else{
            $ext = '.'.trim($ext, '.');
        }
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        $path .= $dir.$file.$ext;
        if(is_file($path)){
            return $path;
        }
        return FALSE;

    }
    /**
     * Подключить файл
     *
     * @param  string $file    имя файла
     * @param  array  $setting директория внутри модуля
     * @return string          буфиринизированый файл
     */
    static function file_load($file, $setting = array()){
        if(!empty($setting)){
            if(($module = $setting['module'])){
                $dir = (isset($setting['dir']))? $setting['dir'] : NULL;
                $ext = (isset($setting['ext']))? $setting['ext'] : NULL;
                $file = static::file_path($file,$dir,$module,$ext);
            }else{
                return FALSE;
            }
        }
        if(is_file($file)){
            ob_start();
                require $file;
            return ob_get_clean();
        }else{
            return FALSE;
        }
    }
    /**
     * Подключения файла конфигураций
     *
     * @param  string $file    имя файла
     * @param  array  $setting директория внутри модуля
     * @return string          буфиринизированый файл
     */
    static function config($module, $group){
        $module = strtolower($module);
        
        if (strpos($group, '.') !== FALSE){
            // Разделить группу конфигурации и пути
            list ($group, $path) = explode('.', $group, 2);
        }
        
        // Если нужна подпапка
        $group = str_replace('_', DIRECTORY_SEPARATOR, strtolower($group));
        
        $key = $module . "_" . $group;

        // Создаем класс для создание конфигураций
        if(!static::$_Config_Reader){
            static::$_Config_Reader = new Core_Config_Module();
        }
        // Проверяем есть ли такая конфигурация, если нет создаем.
        if (!isset(static::$_config[$key])){
            static::$_config[$key] = static::$_Config_Reader->load($group,array(
                                    "mod"=>$module,
                                    "type"=>get_class(new static)
                                    ));
        }
        
        // Если есть путь, создаем его.
        if (isset($path)){
            return Arr::path(static::$_config[$key], $path, NULL, '.');
        }else{
            return static::$_config[$key];
        }
    }
}