<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Workers_Module{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
    }
    
    function fetch(){
        $data = array();
        Registry::i()->data = array();
        Registry::i()->data["type"] = Registry::i()->founds["type"];
        Registry::i()->data["file_name"] = "workers";
        
        if(Registry::i()->founds["id_content_type"]){
            Registry::i()->data["content_type"] = Registry::i()->founds["id_content_type"];
        }
        
        $data["content"] = $this->rout();
        
        // Определяем подключаемый файл
        $template = Model::factory('template','system');
        $path = $template->path(Registry::i()->data);

         // Определяем стили
        $style = Model::factory('style','system');
        
        $style->init(Registry::i()->data);
        
        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,$data);
    }
    
    /**
     * Метод перенаправляет на пользовательский модуль
     *
     */
    protected function rout(){
        $module = "workers";
        
        $pars = Model::factory("route","system")->parse_url(Registry::i()->founds["url"]);

        if(!empty(Registry::i()->founds["id_table"])){
            Registry::i()->data = $this->method->get_user_page(Registry::i()->founds);
        }
        
        if(empty(Registry::i()->founds["id_table"])){
            Registry::i()->data["file_name"] = $pars["module"];
        }
        $path = '';
        $path .= $pars["module"];

        try{
            return Controller::load($path, $module, $pars["action"], $pars["param"]);
        }catch(Exception $e){
            if(Core::$selected_mode > 2){
                Core_Exception::handler($e);
            }else{
                // Обрабатываем ошибку
                Core_Exception::client($e);
                
                Model::factory('error','system')->error();
            }
        }
    }
}