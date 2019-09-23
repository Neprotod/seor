<?php defined('SYSPATH') OR exit();
/**
 * Подключает XSL файлы.
 *
 * @package    Tree
 * @category   Core
 */
class Core_Xml_Xsl extends View{
    
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
    public static function factory($file = NULL, $module = NULL, array $data = NULL){
        $view = new View($file, $module, $data);
        
        try{
            return $view->render();
        }catch (Exception $e){
            // Отображение сообщение об исключении
            Core_Exception::handler($e);

            return '';
        }
    }
    
    public function __construct($file = NULL, $module, array $data = NULL){
        if ($file !== NULL AND !is_object($file)){
            $this->set_filename($file, $module);
        }else{
            if($file instanceof Template)
                $this->_file = $file->template();
        }

        if ($data !== NULL){
            // Добавьте значения в текущих данных
            $this->_data = $data + $this->_data;
        }
    }
    
    function set_filename($file, $module){
        $file = 'views_'.$file;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        
        // Абсолютный путь к файлу
        if(isset($module))
            $path = Module::mod_path($module).$file.EXT;
        else 
            $path = $file.EXT;
        // Храните путь к файлу локально
        $this->_file = $path;

        return $this;
    }

}