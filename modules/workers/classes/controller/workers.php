<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Workers_Workers{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
    }
    
    function fetch(){
        
        $model = "workers_";
        if($user = $this->user->get()){
            // Передаем управление модели
            $model .= "user";
        }else{
            $model .= "guest";
        }
        
        return Model::load($model,"workers","fetch",array($user));
        
    }
    
    
    /**
     * Получить вакансию.
     * 
     * 
     */
    function worker($id = NULL){
        Registry::i()->data["file_name"] = "worker";
        
        // Увеличиваем счетчик просмотров.
        if(!empty($id)){
            Query::i()->sql("update",array(
                                        ":table" => "user",
                                        ":set" => "count_view = count_view + 1",
                                        ":id" =>$id,
                                    ));
        }
        
        $model = "worker_";
        if($user = $this->user->get()){
            // Передаем управление модели
            $model .= "user";
        }else{
            $model .= "guest";
        }
        
        return Model::load($model,"workers","fetch",array($id, $user));
    }
}