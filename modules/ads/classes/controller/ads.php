<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Ads_Ads{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
    }
    
    function fetch(){
        
        $model = "ads_";
        if($user = $this->user->get()){
            // Передаем управление модели
            $model .= "user";
        }else{
            $model .= "guest";
        }
        
        return Model::load($model,"ads","fetch",array($user));
        
    }
    
    
    /**
     * Получить вакансию.
     * 
     * 
     */
    function ad($id = NULL){
        Registry::i()->data["file_name"] = "ad";
        
        // Увеличиваем счетчик просмотров.
        if(!empty($id)){
            Query::i()->sql("update",array(
                                        ":table" => "ads",
                                        ":set" => "count_view = count_view + 1",
                                        ":id" =>$id,
                                    ));
        }
        
        $model = "ad_";
        if($user = $this->user->get()){
            // Передаем управление модели
            $model .= "user";
        }else{
            $model .= "guest";
        }
        
        return Model::load($model,"ads","fetch",array($id, $user));
    }
}