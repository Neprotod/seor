<?php defined('SYSPATH') OR exit();
/**
 * Распределение и подключение модулей
 *
 * @package   Tree
 * @category  Admin
 */
class Admin_Controller extends Core_Controller{
    
    /**
     * @var array содержит экземпляры всех найденных классов
     * @static
     */
    protected static $return = array();
    
    /**
     * @var string имя класса подключение модулей
     */
    protected static $module = "Admin_Module";
}
