<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Use_System{

    /*
     * @param массив Registry->header;
     *
     * @return сгруперованные метаданные
     */
    function meta($date = array()){
        if(empty($date) OR !is_array($date))
            return NULL;
        
        $return = '';
        foreach($date as $meta => $value){
            if(is_array($value)){
                //Определяем содержимое тега
                switch($meta){
                    case 'meta':
                        $name = 'name';
                        $attr = 'content';
                        break;
                    case 'link':
                        $name = 'rel';
                        $attr = 'href';
                        break;
                    default:
                        continue;
                }
                //Заполняем данными
                foreach($value as $key => $content){
                    if(!empty($content))
                        $return .= "<$meta {$name}=\"{$key}\" {$attr}=\"".$content."\"  />\n";
                }
            }
        }
        return $return;
    }
}