<?php defined('SYSPATH') OR exit();
/**
 * Выполнение всего кода
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Request{
    /**
     * @var array Для хранения роутера
     * @static
     */
    public $router;
    /**
     * @var array Для хранения системного модуля
     * @static
     */
    protected $system;
    // Здесь хранится URL для запроса
    //private $url;
    
    // Здесь хранится модуль для заполнения контента
    //private $module;
    /*********Method***********/
    
    /*Static*/
    /**
    * Создает образец объекта
    *
    * @return this
    */
    static function factory(){
        $request = new Request();
        return $request;
    }
    
    /*no static*/
    /**
    * Конструктор класса, заполняем начальные параметры, вызывает rout, 
    * определят текущую тему, возможно в будущем будет кешировать станицу.
    *
    * @return void
    */
    function __construct(){
        //Socket или Server
        if((isset($_SERVER['HTTP_TYPE']) AND isset($_SERVER['HTTP_INIT'])) AND $_SERVER['HTTP_INIT'] == '0000'){
            switch(strtoupper($_SERVER['HTTP_TYPE'])){
                case 'SOCKET': 
                    ob_start();
                    Module::load('socket','fetch');
                    $buffer = ob_get_clean();
                    echo $buffer;
                    break;
                case 'SERVER':
                    Module::factory('server',TRUE)->init();
                    break;
            }
            //Завершаем работу.
            exit();
        }
        
        $this->router = new Route();
        $this->router->init();
        
        // Подключаем основной модуль и выводим содержимое
        $this->system = Module::factory('system',TRUE);
    }
    /**
     * Инициализируем сессию и тему
     *
     * @return void
     */
    function render(){
        // Инициализируем сессию и тему
        $this->system->init();
    }
    
    /**
     * Буференизируем все данные (по всему движку и выполняем их)
     *
     * @return void
     */
    function execute(){
        header('Content-Type: text/html; charset=utf-8');
        header("Expires: -1");
        header("Cache-control: no-store, max-age=0");
        header("X-Frame-Options: sameorigin");
        // Буференизируем все данные
        ob_start();
        $this->render();
        $buffer = ob_get_clean();
        
        // Начинаем строить вывод
        header('Content-Length: '.strlen($buffer));
        echo $buffer;
    }
    /*************************/
    /*Вспомогательные функции*/
    /*************************/
   /**
    * Возвращает переменную _POST, отфильтрованную по заданному типу, 
    * если во втором параметре указан тип фильтра
    *
    * Если $type не задан, возвращает переменную в чистом виде
    *
    * @param  string $name ключ в $_POST
    * @param  string  $type может иметь такие значения: integer(int), string(str), boolean(bool) Если $type не задан, возвращает переменную в чистом виде
    * @param  mixed  $return может иметь такие значения: integer, string, boolean Если $type не задан, возвращает переменную в чистом виде
    * @return mixed
    */
    static function to_type($name = NULL, $type = NULL,$return = NULL){
        $val = $return;
        
        if(!empty($name) OR $name === 0)
            $val = $name;

        if($type == 'string'  || $type == 'str')
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
            
        if($type == 'integer' || $type == 'int')
            return intval($val);

        if($type == 'boolean' || $type == 'bool')
            return !empty($val);
        
        if($type == 'array' || $type == 'arr')
            return (is_array($val))?$val:array();
        
        if($type == 'NULL')
            return NULL;

        return $val;
    }
   /**
    * Возвращает переменную _POST, отфильтрованную по заданному типу, 
    * если во втором параметре указан тип фильтра
    *
    * Если $type не задан, возвращает переменную в чистом виде
    *
    * @param  string $name ключ в $_POST
    * @param  string  $type может иметь такие значения: integer(int), string(str), boolean(bool) Если $type не задан, возвращает переменную в чистом виде
    * @param  mixed  $return может иметь такие значения: integer, string, boolean Если $type не задан, возвращает переменную в чистом виде
    * @return mixed
    */
    static function post($name = NULL, $type = NULL,$return = NULL){
        $val = $return;
        if(!empty($name) && isset($_POST[$name]))
            $val = $_POST[$name];
        elseif(empty($name))
            $val = file_get_contents('php://input');
            
        if($type == 'string'  || $type == 'str')
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
            
        if($type == 'integer' || $type == 'int')
            return intval($val);

        if($type == 'boolean' || $type == 'bool')
            return !empty($val);

        return $val;
    }
    
    /**
    * Возвращает переменную _GET, отфильтрованную по заданному типу, 
    * если во втором параметре указан тип фильтра
    * Если $type не задан, возвращает переменную в чистом виде
    *
    * @param  string $name ключ в $_GET
    * @param  string  $type может иметь такие значения: integer(int), string(str), boolean(bool) Если $type не задан, возвращает переменную в чистом виде
    * @return mixed
    */
    
    static function get($name, $type = NULL,$return = NULL){
        $val = $return;
        if(isset($_GET[$name]))
            $val = $_GET[$name];
        if(!empty($type) && is_array($val))
            $val = reset($val);
        
        if($type == 'string'  || $type == 'str')
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
            
        if($type == 'integer' || $type == 'int')
            return intval($val);

        if($type == 'boolean' || $type == 'bool')
            return !empty($val);
            
        return $val;
    }
    
    /**
     * Проверка сессии
     *
     * @return bool
     */
    static function check_session(){
        if(!empty($_POST)){
            if(empty($_POST['session_id']) || $_POST['session_id'] != session_id()){
                unset($_POST);
                return false;
            }
        }
        return true;
    }
    
    /**
     * Проверка типа запроса GET, POST
     *
     * @param  string $method указать запрос например "post"
     * @return bool
     */
    static function method($method = null){
        if(!empty($method))
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
        return $_SERVER['REQUEST_METHOD'];
    }
    
   /**
    * Возвращает переменную _FILES
    * Обычно переменные _FILES являются двухмерными массивами, поэтому можно указать второй параметр,
    * например, чтобы получить имя загруженного файла: $filename = Request::files('myfile', 'name');
    *
    * @param  string $name  имя
    * @param  string $name2 ключ [name|type|tmp_name|error|size]
    * @return string имя файла
    */
    static function files($name, $name2 = null){
        if(!empty($name2) && !empty($_FILES[$name][$name2]))
            return $_FILES[$name][$name2];
        elseif(empty($name2) && !empty($_FILES[$name]))
            return $_FILES[$name];
        else
            return null;
    }
    
    /**
    * Выдача параметров. (Очистка от HTML сущностей) Используется Str::html_encode()
    * Так же функция если значения не существует выдает значение по умолчанию из параметра $return
    *
    * @param  string $param   Параметр который нужно проверить
    * @param  mixed  $escape  Значение либо TRUE либо 'strip'. TRUE запускает Str::html_encode 'strip' - strip_tags
    * @param  string $return  Значение по умолчанию если $param пуст
    * @param  string $charset charset
    * @return string
    */
    static function param($param, $escape = NULL,$return = NULL, $charset = 'UTF-8'){
        if(isset($param) AND !empty($param)){
            if($escape !== NULL)
                if($escape === TRUE AND is_string($param))
                    return Str::html_encode(strip_tags($param),$charset);
                elseif(strtolower($escape) === 'strip' AND is_string($param))
                    return strip_tags($param);
            
            return $param;
        }else{
            return $return;
        }
    }
   /**
    * Удаляет значение и возвращает результат
    *
    * @param  mixed  $param значение попадает по ссылке, переменная будет удалена
    * @return mixed
    */
    static function unsets(&$param){
        if(!empty($param)){
            $get = $param;
            unset($param);
            return $get;
        }
    }
   /**
    * Удаляет значение и возвращает результат
    *
    * @param  mixed  $param значение попадает по ссылке, переменная будет удалена
    * @return mixed
    */
    static function redirect($url,$code = 301){
        header("Location: ".$url,$code);
        exit;
    }
   /**
    * Проверка на ajax
    *
    * @param  mixed  $param значение попадает по ссылке, переменная будет удалена
    * @return mixed
    */
    static function in_ajax(){
        return Core::$ajax;
    }
    
}