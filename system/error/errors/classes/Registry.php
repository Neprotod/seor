<?php
/*
 * Класс глобальных переменных
 */
class Registry{
    private static $i;
    private $values = array();


    private function __construct() { }

    static function i(){
        if ( ! isset( self::$i ) ) {
            self::$i = new self();
        }
        return self::$i;
    }

    function get( $key ) {
        if ( isset( $this->values[$key] ) ) {
            return $this->values[$key];
        }
        return null;
    }

    function set( $key, $value ) {
        $this->values[$key] = $value;
    }
}