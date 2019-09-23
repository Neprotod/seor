<?php defined('SYSPATH') OR exit();
/**
 * Действует как обертки объекта для HTML страниц со встроенным PHP.
 *
 * @package    Tree
 * @category   Admin
 */
class Admin_View extends View{

    /**
     * @var array Массив глобальных переменных
     */
    protected static $_global_data = array();

    
    /**
     * @var string Просмотр файла
     */
    protected $_file;

    /**
     * @var array Массив локальных переменных
     */
    protected $_data = array();
    /**
     * @var array тип модуля
     */
    protected $_mod = "Admin_Module";
    /**
     * @var array тип модуля
     */
    protected $_templeate = "Admin_Template";
    
    /**
     * Установка начальной имя файла вида и локальные данные.
     * Файл почти всегда должен загружается с помощью [Admin_View::factory].
     *
     *     $view = new Admin_View($file);
     *
     * @param   string  view filename
     * @param   array   array of values
     * @return  void
     * @uses    Admin_View::set_filename
     */
    public function __construct($file = NULL, $module, array $data = NULL){
        if ($file !== NULL AND !is_object($file)){
            $this->set_filename($file, $module);
        }else{
            if($file instanceof Admin_Template)
                $this->_file = $file->template();
        }

        if ($data !== NULL){
            // Добавьте значения в текущих данных
            $this->_data = $data + $this->_data;
        }
    }
    
    /**********************/
    static function path($directory){
        $dir = explode(ADMINROOT,$directory);
        $dir = str_replace('\\', '/', strtolower($dir[1]));
        return $dir;
    }
}
