<?php defined('SYSPATH') OR exit();
/**
 * Используется для подключения файлов в корневой директории.
 *
 * @package    Tree
 * @category   Core
 */
class Core_View_Root extends View{
    /**
     * Подключение объекта
     *
     * @param  string $file   путь к файлу
     * @param  string $module путь к модулю
     * @param  string $data   данные
     * @return string         
     */
    public static function factory($file = NULL, $module = NULL, array $data = NULL){
        $view = new self($file, $module, $data);
        
        try{
            return $view->render();
        }catch (Exception $e){
            // Отображение сообщение об исключении
            Core_Exception::handler($e);

            return '2';
        }
    }
    
    function set_filename($file, $dir){
        // создаем путь к файлу
        $file = trim(str_replace('_', DIRECTORY_SEPARATOR, strtolower($file)),'/');

        // Абсолютный путь к файлу
        if(isset($dir)){
            $dir = trim(str_replace('_', DIRECTORY_SEPARATOR, strtolower($dir)),'/');
            $dir = DOCROOT.$dir;
            $path = $dir.DIRECTORY_SEPARATOR.$file.EXT;
        }else{
            $path = $file.EXT;
        }
        // Храните путь к файлу локально
        $this->_file = $path;

        return $this;
    }
}