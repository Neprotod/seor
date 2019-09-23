<?php defined('SYSPATH') OR exit();
/**
 * Tree exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Tree
 * @category   Exceptions
 */
class Core_Exception_Staging extends Exception {

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = array(
        E_ERROR              => 'Fatal Error',
        E_USER_ERROR         => 'User Error',
        E_PARSE              => 'Parse Error',
        E_WARNING            => 'Warning',
        E_USER_WARNING       => 'User Warning',
        E_STRICT             => 'Strict',
        E_NOTICE             => 'Notice',
        E_RECOVERABLE_ERROR  => 'Recoverable Error',
    );
    public static $error_full = array();
    
    /**
     * Обработчик исключения
     * @param   string   error message
     * @param   array    translation variables
     * @param   integer  the exception code
     * @return  void
     */
    /*function __construct($message, array $variables = NULL, $code = 0)
    {

        // Pass the message to the parent
        parent::__construct($message, $code);
    }*/
    protected static $directory_error = 'error';
    
    static function handler(Exception $e){
        
    }
    
    public function __construct($message, array $variables = NULL, $code = 0){

    }
}
