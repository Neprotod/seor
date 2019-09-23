<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Action_Module{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
    }
    
    function fetch(){
        try{
            $get = array();
            if(isset(Registry::i()->founds["id_table"])){
                $get["params"] = array();
                $get["action"] = Query::i()->sql("action.get",array(":id"=>Registry::i()->founds["id_table"]),NULL,"id");
                if(empty($get["action"])){
                    throw new Core_Exception("Нет действия для страницы.");
                }
            }
            $get = Arr::merge($get, Registry::i()->action_list);
        }catch(Exception $e){
        }
        $render = $this->render($get);
        
        // Определяем метод запуска.
        return $this->view($render);
    }
    
    function render($action){
        // Нет действий, ничего делать не нужно.
        if(empty($action))
            return FALSE;
        
        $test = array("action","params");
        foreach($test AS $t){
            if(!array_key_exists($t,$action)){
                throw new Core_Exception("Нет ячейки <b>:key</b> должны быть все ячейки <b>:all</b>",array(":key"=>$t,":all"=>implode(" | ",$test)));
            }
        }
        
        $return = array();
        foreach($action["action"] AS $key => $value){
            $type = strtolower($value["type"]);
            $class = $value["class"];
            $method = $value["method"];
            $parent = $value["parent"];
            
            $params = array();
            
            if(isset($value["id_params"])){
                $params = isset($action["params"][$value["id_params"]])
                                ? $action["params"][$value["id_params"]]
                                : NULL;
            }
            
            if($type == "module"){
                $return[] = Module::load($class,$method,$params);
            }else{
                $type = ucfirst($type);
                $return[] = $type::load($class, $parent, $method, $params);
            }
            return $return;
        }
    }
    
    function view($render){
        ob_start();
        foreach((array) $render AS $value){
            echo $value;
        }
        return ob_get_clean();
    }
    
    function success(){
        $data_file = array();
        $data_file["type"] = "action";
        
         // Определяем стили
        $style = Model::factory('style','system');
        $style->init($data_file);
        $view = (Request::get("view"))? Request::get("view"): "default";

        return View::factory($view,"action",array());
    }
}