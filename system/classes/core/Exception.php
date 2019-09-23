<?php defined('SYSPATH') OR exit();
/*
 * Распределение и подключение модулей
 *
 * @package   Tree
 * @category  Core
 */
 
class Core_Exception extends Core_Exception_Exception {
    static function client($e, $forcibly = false){
        if(Core::$selected_mode < 4 OR $forcibly){
            Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
        }else{
            Core_Exception::handler($e);
        }
    }
}
