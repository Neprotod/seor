<?php defined('MODPATH') OR exit();

class Model_Error_System{
    function error(){
        ob_end_clean();
        
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=UTF-8');
        header("Cache-control: no-store,max-age=0");
        
        echo View::root('error404','media_error',array('test'=>'test'));
        exit();
    }
    
    function table($id,$table = NULL){
        $e = new Exception("Страница с id = <b>{$id}</b> не найдена в таблице {$table}");
        Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
        $this->error();
    }
}