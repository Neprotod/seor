<?php defined('MODPATH') OR exit();
/**
 * Вызывает ошибку 404
 * 
 * @package    module/system
 * @category   error
 */
class Model_Error_System{
    /**
     * Метод возвращает ошибку 404
     *
     * @return void
     */
    function error(){
        if(ob_get_length()){
            ob_end_clean();
        }
        
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=UTF-8');
        header("Cache-control: no-store,max-age=0");
        
        echo View::root('error404','media_error',array('test'=>'test'));
        exit();
    }
    
    /**
     * Записывает ошибку связанную с не найденной записью в XML файл, и вызывает ошибку 404
     *
     * @param integer $id    id таблицы
     * @param string  $table имя таблицы
     * @return void 
     */
    function table($id,$table = NULL){
        $e = new Exception("Страница с id = <b>{$id}</b> не найдена в таблице {$table}");
        Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
        $this->error();
    }
}