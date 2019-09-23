<?php defined('SYSPATH') OR exit();
/**
 * Класс работы со строками. 
 * 
 * @package    Tree
 * @category   Helpers
 */
class Core_Str{
    /**
     * Заменяет символы в строке через str_replace()
     *
     * @param  string  $string    строка
     * @param  mixed   $char      символ поиска
     * @param  mixed   $separator символ замены
     * @return string             отформатированная строка
     */
    static function separator($string,$char = '.',$separator = '/'){
        if($result = str_replace($char, $separator, $string)){
            return $result;
        }
        return FALSE;
    }
    
    /**
     * Форматирует строку и заменяет значения. 
     * Замена происходит по схеме, ключ = значение
     *     $values[:key] = 'value';
     *
     * @param  string $string строка
     * @param  array  $values значения
     * @return string         отформатированная строка
     */
    static function __($string, array $values = NULL){
        return empty($values) ? $string : strtr($string, $values);
    }
    
    /**
     * Преобразует число в денежную единицу
     *
     * @param  int    $price число для преобразования
     * @return string        денежная единица
     */
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
    
    /**
     * Нужен для вставки значение в такие SQL запросы как UPDATE, INSERT
     * Схема преобразования ключ = значение, где ключ это имя таблицы
     * (не желательно вставлять через $query->param)
     *
     * @param  array  $fonds     массив key = value
     * @param  string $separator разделитель
     * @param  string $table     имя таблицы
     * @return string            строка для вставки в базу данных 
     */
    static function key_value($fonds = array(),$separator = ",",$table = NULL){
        $fond = '';
        if(!empty($table))
            $table = $table.'.';
        if(!empty($fonds) AND is_array($fonds)){
            foreach($fonds as $key => $value){
                if(!is_null($value)){
                    $value = DB::escape($value);
                }else{
                    $value = 'NULL';
                }
                $fond .= "{$table}{$key} = {$value}{$separator}";
            }
            return trim($fond,$separator);
        }
        return FALSE;
    }
    
    /**
     * Сливает строку вместе используя разделитель
     *
     * [!] sql_where(string $separator,string $string[,string $string...]);
     * 
     * @param  string разделитель как AND или OR
     * @param  string строка
     * @return string сливает строку используя разделитель
     */
    static function concat(){
        $fond = "";
        $args = func_get_args();
        if(empty($args))
            throw new Core_Exception('Не пришел разделитель');
        
        //Берем разделитель
        $separator = array_shift($args);
        
        //Сливаем в единую строку
        foreach($args as $string){
            if(!is_scalar($string)){
                continue;
            }
            $fond .= "$string $separator ";
        }
        //Убираем лишний разделитель
        $fond = trim($fond,$separator.' ');
        return $fond;
    }
    /**
     * Обрезает строку
     *
     * @param  string $string строка
     * @param  int    $int    на сколько обрезать
     * @param  bool   $bool   TRUE обрезает строку до пробельного символа.
     * @return string         обрезанная строка.
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
    
    /**
     * Определяем кодировку Windows-1251 или UTF-8
     *
     * @param  string $string строка для определения
     * @return string         имя кодировки.
     */
    static function charset($string){
        
        $string = (string)$string;
        if(!preg_match('/(.)/u',$string,$char_map)){
            $charset = 'ASCII';
        }else{
            $charset = 'UTF-8';
        }
        
        return $charset;
    }
    
    /**
     * Функция конвертации даты и времени с МySQL по формату PHP
     * Использовать осторожно, может вызвать ошибку если дата не верна.
     *
     * @param  string $format как форматировать
     * @param  string $date   дата с МySQL
     * @return mixed          вернет отформатированную дату либо FALSE.
     */
    static function sql_date($format,$date){
        $date_two = explode(' ',$date);
        if(count($date_two) != 2)
            return FALSE;

        $date = explode('-',$date_two[0]);
        $time = explode(':',$date_two[1]);
        
        for($i = 0;$i<3;$i++){
            if(isset($date[$i]) AND isset($time[$i])){
                $date[$i] = intval($date[$i]);
                $time[$i] = intval($time[$i]);
            }
        }
        return date($format, mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));
    }
    
    /*
     * Функция кодирует HTML сущности
     *
     * @param string HTML строка
     * @return mixed кодированная строка либо NULL.
     */
    static function html_encode($html,$charset = 'UTF-8'){
        return (!empty($html) AND is_string($html))? htmlspecialchars($html,ENT_QUOTES,$charset) : NULL;
    }
    
    /*
     * Функция декодирует HTML сущности
     *
     * @param string HTML строка
     * @return mixed декодирования строка либо NULL.
     */
    static function html_decode($html,$charset = 'UTF-8'){
        return (!empty($html) AND is_string($html))? html_entity_decode($html,ENT_QUOTES,$charset) : NULL;
    }
    
    /*
     * Функция очищает строку от всех недопустимых символов
     *
     * @param string строка
     * @return mixed декодирования строка либо NULL.
     */
    static function escape($string){
        return (!empty($string) AND is_string($string))? strip_tags($string) : $string;
    }
}