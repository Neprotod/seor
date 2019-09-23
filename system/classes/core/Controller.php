<?php defined('SYSPATH') OR exit();
/**
 * Распределение и подключение модулей
 *
 * @package   Tree
 * @category  Core
 */
class Core_Controller{
    
    /**
     * @var array содержит экземпляры всех найденных классов
     * @static
     */
    protected static $return = array();
    /**
     * @var array для вывода ошибки
     * @static
     */
    protected static $error = array("load"=>"Нет контроллера","factory"=>"Контроллер","method"=>"контроллере");
    
    /**
     * @var string имя класса подключение модулей
     */
    protected static $module = "Module";
    /**
     * @var string тип как Controller или Model
     */
    protected static $type = "Controller";
    /*********methods***********/
    /**
     * Создайте новый экземпляр контроллера. Класс возвращается через clone
     *
     *     $model = Model::factory($name);
     *
     * @param   string   $name     имя контроллера
     * @param   string   $module   имя модуля
     * @param   array    $settings аргументы
     * @param   bool     $bool     FALSE если не нужен экземпляр класса
     * @param   bool     $new      TRUE принудительно пересоздать класс
     * @return  Model
     * @static
     */
    public static function factory($name, $module, $settings = NULL, $bool = TRUE,$new = FALSE){
        // Добавить префикс модель
        $class = static::$type.'_'.$name;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));
        
        $mod = static::$module;
        
        // Проверяем административный ли это модуль. И дополняем имя
        if(static::$module == "Module")
            $class .= "_".$module;
        else
           $class .= "_".$mod::name($module);
        
        // Абсолютный путь к файлу
        $path = $mod::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$file.EXT;

        // Проверка существующий ли данный Контроллер
        if(is_file($path)){
            if(!class_exists($class, FALSE)){
                require $path;
                // Еще раз проверяем на существование, это поможет избежать Fatal error
                if(!class_exists($class, FALSE)){
                    throw new Core_Exception(':mod_name <b>:class</b> в файле <b>:path</b>',array(":mod_name"=>static::$error["load"],":class"=>$class,":path"=>$path));
                }
            }
        }else{
            throw new Core_Exception(':mod_name  <b>:name</b> не найден в модуле <b>:module</b>',array(':name'=>$name,':module'=>$module,":mod_name"=>static::$error["factory"]));
        }
        if($bool === TRUE){
            //Собираем строку для вставки в массив
            $key_array = $path;
            if(is_array($settings)){
                $key_array .= serialize($settings);
            }
            $key_array = md5($key_array);
            
            // Возвращаем из массива если есть
            if(array_key_exists($key_array,static::$return) AND $new === FALSE){
                return clone static::$return[$key_array];
            }
            //Если есть аргументы
            if(!empty($settings) AND is_array($settings)){
                $str = '';
                foreach($settings as $key => $value)
                    $str .= '$settings["'.$key.'"],';
                $str = trim($str,',');
                
                // Вывод с помощью eval
                return clone static::$return[$key_array] = eval('return new $class('.$str.');');
            }
            return clone static::$return[$key_array] = new $class;
        }else{
            return $class;
        }
    }
    
    /**
     * Подключает найденный контроллер
     *
     * @param  string  $name     имя контроллера
     * @param  string  $module   имя модуля
     * @param  array   $method   имя метода
     * @param  array   $settings аргументы
     * @return mixed   
     * @static
     */
    static function load($name = NULL, $module = NULL, $method = NULL, array $settings = null){
        // Подключаем файл контроллера
        $class = static::factory($name,$module,NULL,FALSE);

        // Создаем правильное имя класса
        if(method_exists($class, $method)){
            $return = static::execution($class,$method,$settings);
        }else{
            throw new Core_Exception('Нет метода <b>:method</b> в :mod_name <b>'.$class.'</b> в модуле <b>'.$module.'</b>', array(":mod_name"=>static::$error["method"],":method"=>$method));
        }
    
        return $return;
    }
    
    
    /**
     * Выполнение метода
     *
     * @param  string $module   имя модуля
     * @param  string $index    имя метода который нужно запустить
     * @param  array  $settings настройки модуля, массив превращается в строку аргументов
     * @return string вывод модуля
     * @static
     */
    protected static function execution($module,$index,$settings){
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
}
