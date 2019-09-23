<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Codebench
 * @category   Tests
 * @author     Geert De Deckere <geert@idoe.be>
 */
class Bench_Crypto extends A_Codebench_Codebench_Admin {
    
    public $loops = 10000;

    public $subjects = array("I think I think I think");
    
    public function bench_crypto_test($subjects){
        $menu = Admin_Model::factory("menu","system");
        $menu->get();
    }

    public function bench_crypto_array($subjects){
       $permission = Admin_Permission::i();
        
        // Сделать запуск по желанию (кнопкой).
        //$permission->set_up();
        $permission->init();

        $r = $permission->perm_controller("system","test","g");
        return $r;
    }

}