<?php defined('SYSPATH') OR exit();
/**
 * Класс глобальных переменных
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Registry{
    /**
     * @var object содержит образец класса
     * @static
     */
    private static $i;
    
    /**
     * @var array хранит экземпляр класса
     */
    private $values = array();


    private function __construct() { }
    
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function i() {
        if ( ! isset( self::$i ) ) {
            self::$i = new self();
        }
        return self::$i;
    }
    
    /**
     * Получить значение
     *
     * @param  string $key
     * @return mixed
     */
    function get( $key ) {
        if ( isset( $this->values[$key] ) ) {
            return $this->values[$key];
        }
        return null;
    }
    /**
     * Сохраняем значение
     *
     * @param  string $key   ключ
     * @param  mixed  $value значение
     * @return void
     */
    function set( $key, $value ) {
        $this->values[$key] = $value;
    }
}