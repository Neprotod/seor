<?php defined('SYSPATH') or exit();
/**
 * Класс переводчик
 *
 * @package    Tree
 * @category   Core
 */
class Core_UTF8 {

    /**
     * @var  boolean  Поддерживает ли сервер UTF-8 изначально?
     */
    public static $server_utf8 = NULL;

    /**
     * @var  array  Список вызываемых методов, в которые был включен необходимый файл.
     */
    public static $called = array();

    /**
     * Рекурсивно очищает массивы, объекты и строки. Удаляет ASCII-управление
     * коды и конвертирует в запрошенный набор символов при молчаливом отбрасывании 
     * несовместимых символов
     *     UTF8::clean($_GET); // Clean GET data
     *
     * [!!] This method requires [Iconv](http://php.net/iconv)
     *
     * @param   mixed   $var     переменная для очистки
     * @param   string  $charset набор символов, по умолчанию Core::$charset
     * @return  mixed
     * @uses    UTF8::strip_ascii_ctrl
     * @uses    UTF8::is_ascii
     */
    public static function clean($var, $charset = NULL){
        if ( ! $charset){
            // Использовать набор символов приложения
            $charset = Core::$charset;
        }

        if (is_array($var) OR is_object($var)){
            foreach ($var as $key => $val){
                // Recursion!
                $var[self::clean($key)] = self::clean($val);
            }
        }
        elseif (is_string($var) AND $var !== ''){
            // Удалить управляющие символы
            $var = self::strip_ascii_ctrl($var);

            if ( ! self::is_ascii($var)){
                // Отключить уведомления
                $error_reporting = error_reporting(~E_NOTICE);

                // iconv тяжелый, поэтому он используется только тогда, когда это необходимо
                $var = iconv($charset, $charset.'//IGNORE', $var);

                // Включите уведомления
                error_reporting($error_reporting);
            }
        }

        return $var;
    }

    /**
     * Проверяет, содержит ли строка 7-битные ASCII байтов. Это используется для
     * определения использовать родные функции или UTF-8
     *
     *     $ascii = UTF8::is_ascii($str);
     *
     * @param   mixed    $str string or array of strings to check
     * @return  boolean
     */
    public static function is_ascii($str){
        if (is_array($str)){
            $str = implode($str);
        }

        return ! preg_match('/[^\x00-\x7F]/S', $str);
    }

    /**
     * Strips out device control codes in the ASCII range.
     * Очищает коды в диапазоне ASCII
     *     $str = UTF8::strip_ascii_ctrl($str);
     *
     * @param   string  string to clean
     * @return  string
     */
    public static function strip_ascii_ctrl($str){
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
    }

    /**
     * Вырезает все не-7-битные ASCII-байты.
     *
     *     $str = UTF8::strip_non_ascii($str);
     *
     * @param   string  string to clean
     * @return  string
     */
    public static function strip_non_ascii($str){
        return preg_replace('/[^\x00-\x7F]+/S', '', $str);
    }

    /**
     * Заменяет special/accented символы UTF-8 символами ASCII-7.
     *
     *     $ascii = UTF8::transliterate_to_ascii($utf8);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string   $str  строка для транслитерации
     * @param   integer  $case -1 только в нижнем регистре, только 1 в верхнем регистре, 0 в обоих случаях
     * @return  string
     */
    public static function transliterate_to_ascii($str, $case = 0){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _transliterate_to_ascii($str, $case);
    }

    /**
     * Возвращает длину данной строки. Это версия с поддержкой UTF8
     * of [strlen](http://php.net/strlen).
     *
     *     $length = UTF8::strlen($str);
     *
     * @param   string   $str строка, измеряемая по длине
     * @return  integer
     * @uses    UTF8::$server_utf8
     */
    public static function strlen($str){
        if (UTF8::$server_utf8)
            return mb_strlen($str, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strlen($str);
    }

    /**
     * Находит позицию первого вхождения строки UTF-8. Это
     * UTF8-aware version of [strpos](http://php.net/strpos).
     *
     *     $position = UTF8::strpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str     стог сена
     * @param   string   $search  игла
     * @param   integer  $offset  смещение от символа в стоге сена, чтобы начать поиск
     * @return  integer           положение иглы
     * @return  boolean           FALSE если игла не найдена
     * @uses    UTF8::$server_utf8
     */
    public static function strpos($str, $search, $offset = 0)
    {
        if (UTF8::$server_utf8)
            return mb_strpos($str, $search, $offset, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__]))
        {
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strpos($str, $search, $offset);
    }

    /**
     * Находит позицию последнего вхождения символа в строку UTF-8. Это
     * UTF8 version of [strrpos](http://php.net/strrpos).
     *
     *     $position = UTF8::strrpos($str, $search);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str     стог сена
     * @param   string   $search  игла
     * @param   integer  $offset  смещение от символа в стоге сена, чтобы начать поиск
     * @return  integer           положение иглы
     * @return  boolean           FALSE если игла не найдена
     * @uses    UTF8::$server_utf8
     */
    public static function strrpos($str, $search, $offset = 0){
        if (UTF8::$server_utf8)
            return mb_strrpos($str, $search, $offset, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strrpos($str, $search, $offset);
    }

    /**
     * Возвращает часть строки UTF-8. Это версия с поддержкой UTF8
     * of [substr](http://php.net/substr).
     *
     *     $sub = UTF8::substr($str, $offset);
     *
     * @author  Chris Smith <chris@jalakai.co.uk>
     * @param   string   $str    строка ввода
     * @param   integer  $offset смещение
     * @param   integer  $length предел длины
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Core::$charset
     */
    public static function substr($str, $offset, $length = NULL){
        if (UTF8::$server_utf8)
            return ($length === NULL)
                ? mb_substr($str, $offset, mb_strlen($str), Core::$charset)
                : mb_substr($str, $offset, $length, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _substr($str, $offset, $length);
    }

    /**
     * Replaces text within a portion of a UTF-8 string.
     * Это версия с поддержкой UTF8 [substr_replace](http://php.net/substr_replace).
     *
     *     $str = UTF8::substr_replace($str, $replacement, $offset);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str         строка ввода
     * @param   string   $replacement строка замены
     * @param   integer  $length      смещение
     * @return  string
     */
    public static function substr_replace($str, $replacement, $offset, $length = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _substr_replace($str, $replacement, $offset, $length);
    }

    /**
     * Делает строчную строчку UTF-8. 
     * Это версия с поддержкой UTF8 [strtolower](http://php.net/strtolower).
     *
     *     $str = UTF8::strtolower($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string   mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     */
    public static function strtolower($str){
        if (UTF8::$server_utf8)
            return mb_strtolower($str, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strtolower($str);
    }

    /**
     * Делает строчку верхнего регистра UTF-8.
     * Это версия с поддержкой UTF8 [strtoupper](http://php.net/strtoupper).
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string   $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     * @uses    Core::$charset
     */
    public static function strtoupper($str){
        if (UTF8::$server_utf8)
            return mb_strtoupper($str, Core::$charset);

        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Функция была вызвана
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strtoupper($str);
    }

    /**
     * Делает первый символ строки UTF-8 в верхнем регистре. 
     * Это версия с поддержкой UTF8 [ucfirst](http://php.net/ucfirst).
     *
     *     $str = UTF8::ucfirst($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str mixed case string
     * @return  string
     */
    public static function ucfirst($str){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _ucfirst($str);
    }

    /**
     * Делает первый символ каждого слова в верхнем регистре строки UTF-8.
     * Это версия с поддержкой UTF8 [ucwords](http://php.net/ucwords).
     *
     *     $str = UTF8::ucwords($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str mixed case string
     * @return  string
     * @uses    UTF8::$server_utf8
     */
    public static function ucwords($str){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _ucwords($str);
    }

    /**
     * Сравнение строк с нечувствительным к регистру строк UTF-8.
     * Это версия с поддержкой UTF8 [strcasecmp](http://php.net/strcasecmp).
     *
     *     $compare = UTF8::strcasecmp($str1, $str2);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str1 строка для сравнения
     * @param   string   $str2 строка для сравнения
     * @return  integer        меньше 0, если str1 меньше str2
     * @return  integer        больше 0, если str1 больше str2
     * @return  integer        0 if they are equal
     */
    public static function strcasecmp($str1, $str2){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strcasecmp($str1, $str2);
    }

    /**
     * Возвращает строку или массив с все вхождения $search в $str
     * (игнорируя регистр) и заменить на заданное значение замены.
     * Это версия с поддержкой UTF8 [str_ireplace](http://php.net/str_ireplace).
     *
     * [!!] Эта функция очень медленная по сравнению с исходной версией.
     *      Избегайте использования, когда это возможно.
     *
     * @author  Harry Fuecks <hfuecks@gmail.com
     * @param   mixed   $search  string|array  текст для замены (который заменится)
     * @param   mixed   $replace string|array  текст замены
     * @param   mixed   $str     string|array  предметный текст
     * @param   integer $count   количество совпадающих и замененных игл будет возвращено через этот параметр, который передается по ссылке
     * @return  mixed            если вход был string|array
     */
    public static function str_ireplace($search, $replace, $str, & $count = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _str_ireplace($search, $replace, $str, $count);
    }

    /**
     * Не зависящая от регистра UTF-8 версия strstr. Возвращает всю строку ввода
     * от первого появления иглы до конца. 
     * Это версия с поддержкой UTF8 [stristr](http://php.net/stristr).
     *
     *     $found = UTF8::stristr($str, $search);
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str    строка ввода
     * @param   string  $search игла
     * @return  string          подстрока, если найдена
     * @return  FALSE           если подстрока не была найдена
     */
    public static function stristr($str, $search){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _stristr($str, $search);
    }

    /**
     * Находит длину маски, соответствующей начальному сегменту. 
     * Это версия с поддержкой UTF8 [strspn](http://php.net/strspn).
     *
     *     $found = UTF8::strspn($str, $mask);
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str    строка ввода
     * @param   string   $mask   маска для поиска
     * @param   integer  $offset начальная позиция строки для проверки
     * @param   integer  $length длина строки для проверки
     * @return  integer  длина начального сегмента, содержащая символы в маске
     */
    public static function strspn($str, $mask, $offset = NULL, $length = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strspn($str, $mask, $offset, $length);
    }

    /**
     * Находит длину начального сегмента, не соответствующего маске.
     * Это версия с поддержкой UTF8 [strcspn](http://php.net/strcspn).
     *
     *     $found = UTF8::strcspn($str, $mask);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   input string
     * @param   string   mask for search
     * @param   integer  start position of the string to examine
     * @param   integer  length of the string to examine
     * @return  integer  length of the initial segment that contains characters not in the mask
     */
    public static function strcspn($str, $mask, $offset = NULL, $length = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strcspn($str, $mask, $offset, $length);
    }

    /**
     * Накладывает строку UTF-8 на определенную длину с другой строкой.
     * Это версия с поддержкой UTF8 [str_pad](http://php.net/str_pad).
     *
     *     $str = UTF8::str_pad($str, $length);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str              строка ввода
     * @param   integer  $final_str_length нужная длина строки после заполнения
     * @param   string   $pad_str          строка для использования в качестве дополнения
     * @param   string   $pad_type         тип заполнения: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
     * @return  string
     */
    public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _str_pad($str, $final_str_length, $pad_str, $pad_type);
    }

    /**
     * Преобразует строку UTF-8 в массив.
     * Это версия с поддержкой UTF8 [str_split](http://php.net/str_split).
     *
     *     $array = UTF8::str_split($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $str          строка ввода
     * @param   integer  $split_length максимальная длина каждого куска
     * @return  array
     */
    public static function str_split($str, $split_length = 1){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _str_split($str, $split_length);
    }

    /**
     * Изменяет строку UTF-8. Это версия с поддержкой UTF8 [strrev](http://php.net/strrev).
     *
     *     $str = UTF8::strrev($str);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string  $str строка, подлежащая изменению
     * @return  string
     */
    public static function strrev($str){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _strrev($str);
    }

    /**
     * Сбрасывает пробелы (или другие символы UTF-8) с самого начала и до конеца строки.
     * Это версия с поддержкой UTF8 [trim](http://php.net/trim).
     *
     *     $str = UTF8::trim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str      строка ввода
     * @param   string  $charlist строка символов для удаления
     * @return  string
     */
    public static function trim($str, $charlist = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _trim($str, $charlist);
    }

    /**
     * Срезает пробелов (или других символов UTF-8) с начала строк.
     * Это версия с поддержкой UTF8 [ltrim](http://php.net/ltrim).
     *
     *     $str = UTF8::ltrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str      строка ввода
     * @param   string  $charlist строка символов для удаления
     * @return  string
     */
    public static function ltrim($str, $charlist = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _ltrim($str, $charlist);
    }

    /**
     * Срезает пробелы (или другие символы UTF-8) из конца строки.
     * Это версия с поддержкой UTF8 [rtrim](http://php.net/rtrim).
     *
     *     $str = UTF8::rtrim($str);
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @param   string  $str      строка ввода
     * @param   string  $charlist строка символов для удаления
     * @return  string
     */
    public static function rtrim($str, $charlist = NULL){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _rtrim($str, $charlist);
    }

    /**
     * Возвращает порядковый номер юникода для символа.
     * Это версия с поддержкой UTF8 [ord](http://php.net/ord).
     *
     *     $digit = UTF8::ord($character);
     *
     * @author  Harry Fuecks <hfuecks@gmail.com>
     * @param   string   $chr UTF-8 encoded character
     * @return  integer
     */
    public static function ord($chr){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _ord($chr);
    }

    /**
     * Принимает строку UTF-8 и возвращает массив int, представляющий символы Unicode.
     * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
     * Происшествия спецификации игнорируются. Суррогаты не допускаются.
     *
     *     $array = UTF8::to_unicode($str);
     *
     * Исходный код - это код клиента Mozilla Communicator.
     * Первоначальным разработчиком исходного кода является Netscape Communications Corporation.
     * Порциями, созданными начальным разработчиком, являются авторские права (C) 1998 Начальный разработчик.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
     *
     * @param   string  $str Закодированная строка UTF-8
     * @return  array        unicode code points
     * @return  FALSE        if the string is invalid
     */
    public static function to_unicode($str){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _to_unicode($str);
    }

    /**
     * Получает массив int, представляющий символы Unicode, и возвращает строку UTF-8.
     *
     * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
     * Occurrances of the BOM are ignored. Surrogates are not allowed.
     *
     *     $str = UTF8::to_unicode($array);
     *
     * The Original Code is Mozilla Communicator client code.
     * The Initial Developer of the Original Code is Netscape Communications Corporation.
     * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
     * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
     * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
     *
     * @param   array   $arr кодовые точки юникода, представляющие строку
     * @return  string       utf8 string of characters
     * @return  boolean      FALSE if a code point cannot be found
     */
    public static function from_unicode($arr){
        if ( ! isset(self::$called[__FUNCTION__])){
            require SYSPATH.'utf8'.DIRECTORY_SEPARATOR.__FUNCTION__.EXT;

            // Function has been called
            self::$called[__FUNCTION__] = TRUE;
        }

        return _from_unicode($arr);
    }


} // End UTF8

if (Core_UTF8::$server_utf8 === NULL){
    // Определите, поддерживает ли этот сервер UTF-8 изначально
    Core_UTF8::$server_utf8 = extension_loaded('mbstring');
}
