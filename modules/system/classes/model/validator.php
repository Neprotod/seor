<?php defined('MODPATH') OR exit();

/**
 * Проверяет данные
 * 
 * @package    module/system
 * @category   Valid
 */
class Model_Validator_System{
    
    /**
     * Валидация, результат можно передать в module::error 
     * 
     * Аргумент $to_valid принимает следующие значения
     * $valid = array(
     *                   "key_array" => array(
     *                       "type" => "int", // значения "int","str","bool","array","NULL"
     *                       "required" => TRUE,
     *                       "pattern" => "^[a-zA-Z]{3,}$", // соответствие глобулярному выражению
     *                   )
     *           );
     *
     *
     * @param array массив с указанием валидации
     * 
     * @return array результат, если массив пустой, значит ошибок нет.
     */
    function valids(array $to_valid, array &$for_valid, $one = FALSE){
        $test = array();
        
        foreach($for_valid AS $key => $value){
            if(is_array($value)){
                $test[$key] = $this->valids($to_valid, $value, $one);
            }else{
                if(isset($to_valid[$key])){
                    $valid = $to_valid[$key];
                    foreach($valid AS $k => $v){
                        if($v){
                            $error = FALSE;
                            
                            $test[$key][$k] = &$error;
                            
                            $action = "valid_" . $k;
                            $message = TRUE;
                            if(is_array($v)){
                                $a = each($v);
                                $message = current($v);
                                $v = current($a);
                            }
                            
                            $this->$action($value,$v,$error,$message);
                            
                            if($error AND $one){
                                break;
                            }
                            
                            unset($error);
                            if(empty($test[$key][$k])){
                                unset($test[$key][$k]);
                            }
                        }
                    }
                }
            }
            if(isset($test[$key]) AND empty($test[$key])){
                 unset($test[$key]);
            }
        }

        return $test;
    }
    
    /**
     * То же самое что и valids только работает перебором по массиву $to_valid
     *
     */
    function valids_around(array $to_valid, array $for_valid, $one = FALSE){
        
        $test = array();
        foreach($to_valid AS $key => $valid){
            // Нужна ли проверка на пустоту.
            if(!isset($for_valid[$key]) AND (array_key_exists("required",$valid) AND $valid["required"])){
               $for_valid[$key] = NULL;
            }
            $value = $for_valid[$key];
            foreach($valid AS $k => $v){
                if($v){
                    $error = FALSE;
                    
                    $test[$key][$k] = &$error;
                    
                    $action = "valid_" . $k;
                    $message = TRUE;
                    if(is_array($v)){
                        $a = each($v);
                        $message = current($v);
                        $v = current($a);
                    }
                    
                    $this->$action($value,$v,$error,$message);
                    
                    if($error AND $one){
                        break;
                    }
                    
                    unset($error);
                    if(empty($test[$key][$k])){
                        unset($test[$key][$k]);
                    }
                }
            }
            if(isset($test[$key]) AND empty($test[$key])){
                 unset($test[$key]);
            }
        }
        
        return $test;
    }
    
    /**
     * Проверка на обязательное поле.
     *
     *
     */
    protected function valid_required($param,$valid,&$error,$message){
        if(!isset($param) OR (Arr::emptys($param) AND $param !== 0)){
           $error = $message;
        }
    }
    /**
     * Проверка на регулярное выражение.
     *
     *
     */
    protected function valid_pattern(&$param,$valid,&$error,$message){
        $pattern = "/$valid/u";
        
        if(!preg_match($pattern,$param)){
           $error = $message;
        }
    }
    
    /**
     * Привести к типу.
     *
     *
     */
    protected function valid_type(&$param,$valid,&$error,$message){
        $type = array("int","str","bool","array","NULL");
                            
        if(!in_array($valid,$type)){
            throw new Core_Exception("Нет такого типа <b>:valid</b> разрешенные типы <b>:type</b>",array(":valid"=>$valid,":type"=>implode(" | ", $type)));
        }
        
        $param = Request::to_type($param, $valid);
    }
}