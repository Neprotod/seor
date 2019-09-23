<?php
/*
 * empty class
 */
class Str{
    static function separator($string,$char = '.',$separator = '/'){
        if($result = str_replace($char, $separator, $string)){
            return $result;
        }
        return FALSE;
    }
    static function __($string, array $values = NULL){

        return empty($values) ? $string : strtr($string, $values);
    }
    
    static function money($price){
        $r = fmod($price, 1);
        if($r == 0){
            $price = $price - $r;
            $price = number_format($price, 0, '.', ' ');
        }else{
            $price = number_format($price, 2, '.', ' ');
        }
        return $price;
    }
    
    static function key_value($fonds = array()){
        if(!empty($fonds)){
            foreach($fonds as $key => $value){
                if(!is_null($value)){
                    $value = DB::escape($value);
                }else{
                    $value = 'NULL';
                }
                $fond .= "$key = $value,";
            }
            return trim($fond,',');
        }
        return FALSE;
    }
    /*
     * Обрезает строку
     * $int = на сколько обрезать
     * $bool = обрезать до пробельного символа?
     */
    static function crop($string,$int,$bool = TRUE){
        $int = intval($int);
        $char = '';
        $length = UTF8::strlen($string);
        if($length > $int){
            $ofset = UTF8::substr($string,$int);
            preg_match("/(^.[^\W\s]*)(\.{3}|\W|\s)/u",$ofset,$result);
            if(!empty($result)){
                $int += UTF8::strlen($result[1]);
                if($bool === TRUE AND !empty($result[2])){
                    $char = preg_replace("/[^\.]/u", '...', $result[2]);
                }
            }
            $string = UTF8::substr($string,0,$int);
        }
        return $string.$char;
    }
    
    // Определяем кодировку Windows-1251 или UTF-8
    static function charset($string){
        
        $string = (string)$string;
        if(!preg_match('/(.)/u',$string,$char_map)){
            $charset = 'ASCII';
        }else{
            $charset = 'UTF-8';
        }
        
        return $charset;
    }
}