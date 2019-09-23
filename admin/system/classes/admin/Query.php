<?php defined('SYSPATH') OR exit();
/**
 * Класс запросов
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Query extends Query{
    /**
     * @var object содержит образец класса
     * @static
     */
    protected static $i;
    
    private function __construct() { }
    
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function i() {
        if ( ! isset( static::$i ) ) {
            static::$i = new static();
            static::$i->xml = Model::factory('query','xml',array(static::$i));
            static::$i->xml->pars();
        }
        return static::$i;
    }
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function instance() {
        return static::i();
    }
}