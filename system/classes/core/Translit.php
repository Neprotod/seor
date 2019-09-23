<?php defined('SYSPATH') OR exit();
/**
 * Для транслита кириллица - латиница.
 *
 * @package    Tree
 * @category   Helpers
 */
class Core_Translit{
    /**
     * Перевод строки из кирилицы в латиницу
     *
     * @param  string $stroka входная строка
     * @return string         
     */
    static function cyrillicy($stroka){
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',  'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
           
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
     
        );
     
        return strtr($stroka, $converter);
    }
    
    /**
     * Склоняет слова (товар, товаров, товары) в зависимости от числа. 1 яблоко, 2 яблока, 10 яблок
     * 
     * @param  integer $integer число для склонения
     * @param  array   $word    массив склонений (яблоко, яблока, яблок)
     * @return string           склоненное слово
     */
    static function declension_words($integer, $word) {
        $keisi = array (2, 0, 1, 1, 1, 2); 
        return $word[ ($integer%100 > 4 && $integer %100 < 20) ? 2 : $keisi[min($integer%10, 5)] ]; 
      // Сделайте $num = 111 и проверить функцию
      /*
        вариант
        function declension_words($n,$words){
            return ($words[($n=($n=$n%100)>19?($n%10):$n)==1?0 : (($n>1&&$n<=4)?1:2)]);
        }
      */
    }
    
    static function url($str) {
        $url = '';
        if(!empty($str)){
            $url = strtolower(Translit::cyrillicy($str));
            $url = trim($url, '/');
            $url = preg_replace("/[^0-9a-zа-я\/-_]/ui", '-', $url);
            $url = preg_replace("/-{2,}|_-{1,}|-{1,}_/", '-', $url);
            $url = trim($url, '-_');
        }
        return $url;
    }
}
