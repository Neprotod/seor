<?php defined('SYSPATH') OR exit();
/**
 * Database exceptions.
 *
 * @package    Core/Database
 * @category   Exceptions
 */
class Core_Database_Database_Exception extends Core_Exception {

    protected $db_code = NULL;
    
    /**
     * Creates a new translated database exception.
     *
     *     throw new Database_Exception(1234, 'Something went terrible wrong, :user',
     *         array(':user' => $user));
     *
     * @param   string   raw DB error code
     * @param   string   error message
     * @param   array    translation variables
     * @param   integer  the exception code
     * @return  void
     */
    public function __construct($db_code, $message, array $variables = NULL, $code = 0)
    {
        $this->db_code = $db_code;

        parent::__construct($message, $variables, $code);
    }
    
    /**
     * Gets the DB error code
     * 
     * @return string
     */
    public function db_code()
    {
        return $this->db_code;
    }
}
