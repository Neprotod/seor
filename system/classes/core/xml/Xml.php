<?php defined('SYSPATH') OR exit();
/**
 * Подключает XML файлы.
 *
 * @package    Tree
 * @category   Core
 */
class Core_Xml_Xml extends View{
    public $ext = 'xml';
    public $default_path = 'xml'
    
    /**
     * Возвращает новый объект Вида. Если вы не определяете параметр "Файл",
     * вам необходимо вызвать [View::set_filename].
     *
     *     $view = View::factory($file);
     *
     * @param   string  $file   view filename
     * @param   string  $module module name
     * @param   array   $data   array of values
     * @return  View
     */
    public static function factory($file = NULL, $module = NULL, array $data = NULL,$settings = array()){
        $view = new View($file, $module, $data,$settings);
        
        try{
            return $view->render();
        }catch (Exception $e){
            // Отображение сообщение об исключении
            Core_Exception::handler($e);

            return '';
        }
    }
    
    public function __construct($file = NULL, $module, array $data = NULL,$settings){
        if(!empty($settings)){
            if(isset($settings['default_path']))
                $this->default_path = $settings['default_path'];
            if(isset($settings['ext']))
                $this->ext = $settings['ext'];
        }
        if ($file !== NULL ){
            $this->set_filename($file, $module);
        }

        if ($data !== NULL){
            // Добавьте значения в текущих данных
            $this->_data = $data + $this->_data;
        }
    }
    
    function set_filename($file, $module){
        $file = $this->gefault_path.'_'.$file;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        
        // Абсолютный путь к файлу
        if(isset($module))
            $path = Module::mod_path($module).$file.EXT;
        else 
            $path = $file.'.'.$this->ext;
        // Храните путь к файлу локально
        $this->_file = $path;

        return $this;
    }

}