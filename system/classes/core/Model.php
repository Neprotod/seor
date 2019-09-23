<?php defined('SYSPATH') OR exit();
/**
 * Подключение моделей
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Model extends Core_Controller{
    
    /**
     * @var array содержит экземпляры всех найденных классов
     */
    protected static $return = array();
    /**
     * @var string тип как Controller или Model
     */
    protected static $type = "Model";
    /**
     * @var array для вывода ошибки
     * @static
     */
    protected static $error = array("load"=>"Нет модели","factory"=>"Модель","method"=>"модели");
}