<?php defined('SYSPATH') OR exit();
/**
 * Класс обработки sql permission
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Permission_User{
    protected $user;
    protected $sql;
    protected $permission = array();
    protected $permission_id = array();
    protected $rule = array();
    protected $rule_id = array();
    
    
    function __construct(Admin_Permission_Sql $sql){
        $this->sql = $sql;
    }
    
    function get_all_permission(){
        $found = array();
        $found["permission"] = $this->permission_id;
        $found["rule"] = $this->rule_id;
        return $found;
    }
    
    function set_user($user){
        $this->user = $user;
    }
    
    function set_permission($permission){
        
    }
    
    function get_permission($ids){
        if(!is_scalar($ids)){
            throw new Core_Exception("Пришел неверный id, он должен быть скалярным");
        }
        $found = array(
            "permission" => array(),
            "rule" => array()
        );
        if(isset($this->permission[$ids])){
            $found["permission"] = $this->permission[$ids];
            if(isset($this->rule[$ids])){
                $found["rule"] = $this->rule[$ids];
            }
        }
        return $found;
    }
    
    function pars($all_permission){
        $permission = $all_permission["id_permission"];

        $rule = $all_permission["rule"];

        $this->permission = Admin_Query::i()->sql("permission.user.permission",array(
                                                                    ":id" => $this->user["id_type"],
                                                                    ),"id_permission");
        
        $this->rule = Admin_Query::i()->sql("permission.user.rule",array(
                                                                    ":id" => $this->user["id_type"],
                                                                   ));
        if($this->permission){
            $this->permission_id = $this->permission;
            
            $test = current($this->permission);
            $test = $test["status"];
            
            $keys = array_keys($this->permission);
            
            if(!$p = $this->check($this->permission, $test)){
                 // Составляем правильный массив
                $permission = $this->bool_perm($permission,TRUE);
                
                $permission = $this->bool_key($keys,$permission,TRUE,FALSE);
            }else{
                 // Составляем правильный массив
                $permission = $this->bool_perm($permission,TRUE,FALSE);
                
                $permission = $this->bool_key($keys,$permission,TRUE,TRUE);
            }
            $this->permission = $permission;
        }else{
            $this->permission = $this->bool_perm($permission,TRUE);
        }
        
        if($this->rule = $this->sql->pack($this->rule, "id_permission", "rule")){
            $this->rule_id = $this->rule;
            
            $test = Arr::flatten(Arr::search("status",$this->rule,TRUE));
            $test = $test["status"];

            $keys = array_keys($this->rule);
            
            if(!$r = $this->check($this->rule, $test)){
                $rule = $this->bool_perm($rule,FALSE);
                
                $rule = $this->bool_key($keys,$rule,FALSE,FALSE);
                
            }else{
                $rule = $this->bool_perm($rule,FALSE,FALSE);
                
                $rule = $this->bool_key($keys,$rule,FALSE,TRUE);
            }
            $this->rule = $rule;
            
            if(isset($p) AND $r != $p)
                throw new Core_Exception("Такого не может быть, что бы admin_permission и admin_rule имели разные статусы");
        }else{
            $this->rule = $rule = $this->bool_perm($rule,FALSE);;
        }
    }
    
    function bool_perm($perm, $permission, $bool = TRUE){
        if($permission){
            foreach($perm AS &$value){
                $value = $bool;
            }
        }else{
            foreach($perm AS &$value){
                foreach($value AS $name => $val){
                    $value[$name] = $bool;
                }
            }
        }
        return $perm;
    }
    function bool_key($keys, $perm, $permission, $bool = TRUE){
        if($permission){
            foreach($keys AS $key){
                $perm[$key] = $bool;
            }
        }else{
            foreach($keys AS $key){
                foreach($perm[$key] AS $name => $val){
                    if(isset($this->rule[$key][$name]))
                        $perm[$key][$name] = $bool;
                }
            }
        }
        return $perm;
    }
    
    function check($check, $test){
        // Тем самым мы сможем проверить от противного
        $test = abs($test - 1);
        if($t = Arr::search(array("status"=>$test),$check)){
            throw new Core_Exception("В группе прав <b>:group</b> есть ошибка, все статусы должны быть либо 0 либо 1", array(":group"=>$this->user["type"]));
        }else{
            
            return ($test)? 0: 1;
        }
        
        
    }
}