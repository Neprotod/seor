<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Test_Ajax{
    
    function active(){
        return array("exception" => 1);
    }
}