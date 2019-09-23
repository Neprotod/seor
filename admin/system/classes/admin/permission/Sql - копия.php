<?php defined('SYSPATH') OR exit();
/**
 * Класс обработки permission в XML 
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Permission_Sql{
    
    protected $module = array();
    
    protected $module_current;
    protected $class_current;
    protected $type_current;
    protected $key_current;
    
    protected $controller = array();
    
    protected $model = array();
    
    protected $permission = array();
    
    protected $rule = array();
    
    protected $sql = array();
    
    function pars($module){
        foreach($module as $key => $value){
            $found = array();
            $this->module[$key] = &$found;
            $this->module_current = $key;
            
            $found["class_name"] = $key;
            $found["type"] = "module";
            
            $this->arr_rout($value, $found);
            
        }
        var_dump($this->permission);
    }
    
    protected function arr_rout($arr, &$found){
        foreach($arr as $action => $value){
            $found[$action] = $this->$action($value);
        }
    }
    protected function description($string){
        return $string;
    }
    
    protected function methods($array){
        
        $this->class_current = $this->module_current;
        $this->type_current = "module";
        
        foreach($array as $key => $value){
            $found = array();
            $this->permission[] = &$found;
            $this->key_current = count($this->permission);
            
            $found["method"] = $key;
            $found["type"] = $this->type_current;
            $found["class"] = $this->class_current;
            $found["module"] = $this->module_current;
            
            $this->arr_rout($value, $found);
            unset($found);
        }
        
        //return $found;
    }
    
    protected function permission($array){
        $found = array();
        $this->arr_rout($array, $found);
        
    }
    protected function rule($array){
        $found = array();
        $this->rule[$this->key_current] = &$found;
       
        foreach($array as $key => $value){
                $found[$key] = array();
            foreach($value as $action => $val){
                $found[$key]["id_permission"] = $this->key_current;
                $found[$key]["rule"] = $key;
                $found[$key][$action] = $val;
            } 
        }
    }
    
    protected function sql($array){
       $found = array();
       $this->sql[$this->key_current] = &$found;
       
       foreach($array as $key => $value){
            $found[$key] = array();
            foreach($value as $action => $val){
                $found[$key]["id_permission"] = $this->key_current;
                $found[$key]["sql"] = $key;
                $found[$key][$action] = $val;
            } 
       }
    }
    protected function controllers($array){
        $found = array();
        
        $this->type_current = "controller";
        
        foreach($array as $key => $value){
            
            //$this->key_current = count($this->permission);
            //$this->permission[$this->key_current] = &$found;
            $this->class_current = $key;
            
            /*$found["method"] = $this->class_current;
            $found["type"] = $this->type_current;
            $found["class"] = $this->class_current;
            $found["module"] = $this->module_current;*/
            /*if(is_array($value))
                $this->arr_rout($value, $found);*/
            foreach($value as $act => $val){
                $found = array();
                $this->key_current = count($this->permission);
                $this->permission[$this->key_current] = &$found;
                
                $found["method"] = $act;
                $found["type"] = $this->type_current;
                $found["class"] = $key;
                $found["module"] = $this->module_current;
                $this->arr_rout($val, $found);
                unset($found);
            }
        }
        
        //$this->arr_rout($array, $found);
    }
    
    protected function models($array){
        $found = array();
        $this->type_current = "model";
       
        //$this->arr_rout($array, $found);
    }

    
}