<?php defined('SYSPATH') OR exit();
/*
 * empty class
 */
class Admin_Module extends Core_Module{
    // Содержит имена модулей
    protected static $_modules = array();
    
    // Содержит массив модуль - путь
    protected static $_modules_path = array();
    
    /**
     * @var array содержит все запущенные ранее модули
     */
    protected static $return_module = array();
    
    // Пути для дополнительных включений
    public static $app = array();
    
    public static $path = array(MODPATH_ADMIN);
    
    /*
     * Создаем имя модуля
     *
     * @param object reflectionMethod
     * @return void
     */
    static function name($module){
        return $module . "_Admin";
    }

}