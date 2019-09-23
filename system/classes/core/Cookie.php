<?php defined('SYSPATH') OR exit();
/**
 * Для работы с cookie.
 *
 * @package   Tree
 * @category  Helpers
 */
class Core_Cookie {

    /**
     * @var  string  Соль для cookie
     * @static
     */
    public static $salt = "tree";

    /**
     * @var  integer  Срок действия
     * @static
     */
    public static $expiration = 0;

    /**
     * @var  string  Ограничить путь к cookie ( "/" по всем каталогам)
     * @static
     */
    public static $path = '/';

    /**
     * @var  string  Ограничение по домену, например если нужно задать cookie на поддомен.
     * @static
     */
    public static $domain = NULL;

    /**
     * @var  boolean  Передавать только по защищенному соеденению HTTPS
     * @static
     */
    public static $secure = TRUE;

    /**
     * @var  boolean  Только передаете файлы cookie по протоколу HTTP, отключая доступ к cookie через javascript
     * @static
     */
    public static $httponly = FALSE;

    /**
     * Получает значение подписанного файла cookie. 
     * Если cookie существует но срок годности вышел, cookie будет удален.
     *
     *     // Получать cookie темы или использовать "blue" по умолчанию.
     *     $theme = Cookie::get('theme', 'blue');
     *
     * @param   string  $key     имя cookie
     * @param   mixed   $default значение по умолчанию если cookie нет.
     * @return  string
     */
    public static function get($key, $default = NULL){
        if ( ! isset($_COOKIE[$key])){
            // Файл cookie не существует
            return $default;
        }

        // Получить cookie
        $cookie = $_COOKIE[$key];

        // найти точку раскола между солью и содержанием.
        $split = UTF8::strlen(Cookie::salt($key, NULL));

        if (isset($cookie[$split]) AND $cookie[$split] === '~'){
            // Отделить соль и значение
            list ($hash, $value) = explode('~', $cookie, 2);

            if (Cookie::salt($key, $value) === $hash){
                // cookie действительно
                return $value;
            }

            // Cookie не действительно, удалить его.
            Cookie::delete($key);
        }

        return $default;
    }

    /**
     * Задает значение cookie.
     *
     *     // задать "theme" cookie
     *     Cookie::set('theme', 'red');
     *
     * @param   string   $name       Имя cookie
     * @param   string   $value      Значение
     * @param   integer  $expiration Время жизни в секундах
     * @return  boolean
     * @uses    Cookie::salt
     */
    public static function set($name, $value, $expiration = NULL){
        if ($expiration === NULL){
            // Использовать время жизни по умолчанию
            $expiration = Cookie::$expiration;
        }

        if ($expiration !== 0){
            // Добавялем текущее время.
            $expiration += time();
        }

        // Добавить соль в значения cookie
        $value = Cookie::salt($name, $value).'~'.$value;

        return setcookie($name, $value, $expiration, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
    }

    /**
     * Удалить cookie
     *
     *     Cookie::delete('theme');
     *
     * @param   string   $name имя cookie
     * @return  boolean
     * @uses    Cookie::set
     */
    public static function delete($name){
        // Удалить cookie
        unset($_COOKIE[$name]);

        // Удалить печенью и поставить время в минус.
        return setcookie($name, NULL, -86400, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
    }

    /**
     * Создает строку соли для cookie на основе имени и значения.
     *
     *     $salt = Cookie::salt('theme', 'red');
     *
     * @param   string $name  Имя cookie
     * @param   string $value Значение cookie
     * @return  string
     */
    public static function salt($name, $value){
        // Если нет соли
        if (!Cookie::$salt){
            throw new Core_Exception('Пожалуйста установите Cookie::$salt.');
        }

        // Определить агента пользователя
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent.$name.$value.Cookie::$salt);
    }

}
