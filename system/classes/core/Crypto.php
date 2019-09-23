<?php defined('SYSPATH') OR exit();
/**
 * @package   Tree
 * @category  Crypto
 */
class Core_Crypto{
    
    protected static $driver = "Core_Crypto_Native";
    /**
     * Создает
     *
     * @param   string строка для кодировки
     * @return  string
     */
    static function set($string){
        $driver = self::$driver;
        return $driver::set($string);
    }

    /**
     * Возвращает
     *
     * @param   string строка для кодировки
     * @return  string
     */
    static function get($string){
        $driver = self::$driver;
        return $driver::get($string);
    }
}