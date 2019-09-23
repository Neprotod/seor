<?php defined('MODPATH') OR exit();

class Auth_Admin implements I_Module{
    /**
     * @var образец класса
     */
    private static $i;
    /**
     * @var mixed образец класса
     */
    protected $driver = "native";
    /**
     * @var array образец класса
     */
    public $user = array();
    
    
    function __construct(){
        if ( ! isset( self::$i ) ) {
            self::$i = $this;
        }else{
            return self::$i;
        }
        // Определяем тип драйвера
        $driver = Admin_Controller::factory($this->driver,'auth');
        
        $this->driver = $driver;
        
        $this->user = $this->driver->login();
        
        return self::$i;
    }
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function i() {
        if ( ! isset( self::$i ) ) {
            return new self();
        }
        return self::$i;
    }
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function instance() {
        return self::i();
    }
    
    function logout(){
        self::i()->driver->_logout();
    }
}