<?php defined('SYSPATH') OR exit();

/**
 * Модель базового класса. Все модели должны расширить этот класс.
 * 
 * @package    Tree
 * @category   Admin
 */
class Admin_Model extends Core_Model{
    
    /**
     * @var array содержит экземпляры всех найденных классов
     */
    protected static $return = array();

    /**
     * @var string имя класса подключение модулей
     */
    protected static $module = "Admin_Module";
}