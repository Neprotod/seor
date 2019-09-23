<?php defined('SYSPATH') OR exit();

/**
 * Модель базового класса. Все модели должны расширить этот класс.
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Model{
    
    /**
     * @var array содержит экземпляры всех найденных классов
     */
    protected static $return_model = array();
    
    /*********methods***********/
    /**
     * Создайте новый экземпляр модели.
     *
     *     $model = Model::factory($name);
     *
     * @param   string   имя модели
     * @param   string   имя модуля
     * @param   array    аргументы
     * @param   bool     FALSE если не нужен экземпляр класса
     * @param   bool     TRUE принудительно пересоздать класс
     * @return  Model
     */
     
     public static function factory($name, $module, $settings = NULL, $bool = TRUE,$new = FALSE){
        // класс модели
        $class = 'Model_'.$name;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));
        
        $class .= "_".$module;
        // Абсолютный путь к файлу
        $path = Module::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$file.EXT;

        // Проверка счуществут ли данная Модель
        if(is_file($path)){
            if(!class_exists($class))
                require $path;
        }else{
            throw new Core_Exception('Модель  '.$name.' не найдена');
        }
        if($bool === TRUE){
            //Если есть аргументы
            if(!empty($settings) AND is_array($settings)){
                $str = '';
                foreach($settings as $key => $value)
                    $str .= '$settings['.$key.'],';
                $str = trim($str,',');
                
                // Вывод с помощью eval
                return eval('return new $class('.$str.');');
            }
            return new $class;
        }
    }
}