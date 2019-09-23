<?php defined('MODPATH') OR exit();

class Server_Module{
    /**
     * @var string имя контроллера
     */
    protected $controller;
    
    /**
     * @var array заголовки
     */
    protected $header;
    
    /**
     * @var array типы декодирования
     */
    public $decode = array(
                'base64'=>'base64_decode',
                'uuencode'=>'convert_uudecode',
                'utf8'=>'utf8_decode',
            );
    
    /**
     * @var object обработка ошибок.
     */
    protected $error;
    
    protected $login = "3b0c14770e6bd663518496da60f524da";
    protected $password = "4a7d1ed414474e4033ac29ccb8653d9b";
    
    protected static $send = array();
    
    protected $content_type = array(
                    'text/xml'=>'xml',
                    'application/x-www-form-urlencoded'=>'post',
                    );
    
    function __construct(){
        $this->login = md5($this->login);
        $this->password = md5($this->password);
        $this->error = Model::factory('error','system');
    }
    
    function init(){
        //Исходя из типа контента, запускает ту или иную функцию
        $this->content_type($_SERVER['CONTENT_TYPE']);
    }
    
    
    /**
     * Определяет функцию к типу контента
     *
     * @param  string тип контента
     * @return void
     */
    function content_type($type){
        
        $action = '';
        if(array_key_exists($type,$this->content_type)){
            $action = $this->content_type[$type];
        }else{
            if((isset($_POST['login']) AND isset($_POST['password'])) AND ($_POST['login'] == $this->login AND $_POST['password'] == $this->password)){
                $this->error(Str::__('Не могу обработать тип данных <b>:type</b>',array(':type'=>$type)));
                
            }
            $this->error->error();
        }
        //Статус 200, проверка типа пройдена.
        self::$send['status'] = '200';
        
        //Подключаем тип обработки.
        $this->$action();
    }
    
    /**
     * Обрабатывает запросу XML
     *
     */
    function xml(){
        $request = '';
        if(isset($GLOBALS['HTTP_RAW_POST_DATA']))
            $request = $GLOBALS['HTTP_RAW_POST_DATA'];
        else
            $this->error('Нет тела запроса');

        $dom = new DOMDocument();
        $dom->loadXML($request);
        
        $root = $dom->documentElement;
        $xpath = new DOMXPath($dom);
        
        $xml_param = $xpath->query("param");
        $xml_method = $xpath->query("method");
        
        //Отбираем параметры
        $param = array();
        foreach($xml_param AS $element){
            if($element->nodeType == 1){
                $param[$element->getAttribute('name')] = $element->getAttribute('value');
            }
        }

        //Отбираем методы
        $method = array();
        foreach($xml_method AS $element){
            if($element->nodeType == 1){
                $type = $element->getAttribute('type');
                $name = $element->getAttribute('name');
                $action = $element->getAttribute('action');
                $id = $element->getAttribute('id');
                
                $fonds = array();
                if($element->hasChildNodes()){
                    foreach($element->childNodes AS $arg){
                        $encode = $arg->getAttribute('encode');
                        $arg_type = $arg->getAttribute('type');
                        
                        $value = $arg->nodeValue;
                        
                        if(isset($this->decode[$encode])){
                            $value = $this->decode[$encode]($arg->nodeValue);
                        }else{
                            throw new Core_Exception('Установлен не существующий тип кодирования :encode',array(':encode'=>$encode));
                        }
                        
                        switch($arg_type){
                            case 'boolean':
                                $value = (boolean)$value;
                                break;
                            case 'integer':
                                $value = (integer)$value;
                                break;
                            case 'float':
                                $value = (float)$value;
                                break;
                            case 'string':
                                $value = (string)$value;
                                break;
                            case 'array':
                                $value = unserialize($value);
                                break;
                            case 'object':
                                $value = json_decode($value);
                                break;
                            case 'null':
                                $value = NULL;
                                break;
                            default:
                                throw new Core_Exception('Неизвестный тип данных :type',array(':type'=>$arg_type));
                        }
                        $fonds[] = $value;
                    }
                }
                
                $method[$type][$name][$id][$action] = $fonds;
            }
        }
        
        ksort($method);
        
        //Обрамляем дополнительной ячейкой
        $method = $method;
        
        //Сливаем параметры и методы;
        $params['param'] = $param;
        $params['action'] = $method;
        
        $this->action($params);
    }
    
    /**
     * Обрабатывает запрос POST
     *
     */
    function post(){
        
        if(!isset($_POST['param']) OR !isset($_POST['action']))
            $this->error->error();
        
        $this->action($_POST);
    }
    
    
    /**
     * Выполняет действия по порядку.
     * 
     * @param array набор имен контроллеров и моделей с методами.
     */
    function action($params){
        $param = !empty($params['param'])?$params['param']:array();
        $action = !empty($params['action'])?$params['action']:array();
        //Авторизация
        if((!isset($param['login']) OR !isset($param['password'])) OR ($param['login'] != $this->login OR $param['password'] != $this->password)){
            $this->error->error();
        }
        
        //Массив значений для возврата
        $request = array();
        $request['status'] = self::$send['status'];
        $request['content-type'] = 'array';
        $request['count_error'] = 0;
        
        //Заполняет ответ серера
        $fonds = array();
        $request['request'] =& $fonds;
        foreach($action AS $type => $array){
            foreach($array AS $name => $methods){
                try{
                    $class = '';
                    if($type == 'controller'){
                        $class = Controller::factory($name,'server');
                    }else{
                        $class = Model::factory($name,'server');
                    }
                    
                    foreach($methods AS $id => $metod){
                        $method_name = key($metod);
                        $arg = reset($metod);
                        
                        $fonds[$id]['method'] = $method_name;
                        $fonds[$id]['type'] = $type;
                        $fonds[$id]['name'] = $name;
                            try{
                                if(method_exists($class,$method_name)){
                                    $fonds[$id]['body'] = $this->execute($class,$method_name,$arg);
                                }else{
                                    $this->error_push('Такого метода нет',$fonds[$id],$request);
                                    $request['count_error']++;
                                }
                            }catch(Exception $e){
                                $request['count_error']++;
                                $this->error_push($e,$fonds[$id],$request);
                            }
                        
                    }
                }catch(Exception $e){
                    $request['count_error']++;
                    $this->global_error($e,$request);
                }
            }
        }
        //Возвращает результат
        echo self::strCode(serialize($request));
    }
    
    /**
     * Выполнение методов класса.
     * 
     * @param mixed либо объект EXCEPTION либо просто сообщение
     */
    function error_push($error,&$var = FALSE,&$global = FALSE){
        $fond = array();
        if(is_object($error)){
            if($error instanceof Exception){
                $fond['type'] = 'exception';
                $fond['message'] = $error->getMessage();
                $fond['code'] = $error->getCode();
                $fond['file'] = $error->getFile();
                $fond['getLine'] = $error->getLine();
            }
        }else{
            $fond['type'] = 'message';
            $fond['message'] = $error;
        }
        if($var !== FALSE)
            $var['error'] = $fond;
        if($global !== FALSE)
            $global['error'][] = $fond;
    }
    /**
     * Запись к общим ошибкам.
     * 
     * @param mixed либо объект EXCEPTION либо просто сообщение
     */
    function global_error($error,&$global){
        $fond = array();
        if(is_object($error)){
            if($error instanceof Exception){
                $fond['type'] = 'exception';
                $fond['message'] = $error->getMessage();
                $fond['code'] = $error->getCode();
                $fond['file'] = $error->getFile();
                $fond['getLine'] = $error->getLine();
            }
        }else{
            $fond['type'] = 'message';
            $fond['message'] = $error;
        }

        if($global !== FALSE)
            $global['error'][] = $fond;
    }
    
    /**
     * Выполнение методов класса.
     */
    private function execute($class,$method,$args){
        if(!is_object($class)){
            throw new Core_Exception('Для выполнения метода нужен объект');
        }
        $str = '';
        if(is_array($args))
            foreach($args as $key => $value)
                $str .= '$args["'.$key.'"],';
            $str = trim($str,',');

        // Вывод с помощью eval
        return eval('return $class->$method('.$str.');');
    }
    /**
     * Вывод простой ошибки
     *
     */
    function error($massage){
        
        $error = array();
        $error['status'] = '404';
        $error['error'] = $massage;
        echo self::strCode(serialize($error['error']));
        exit();
    }
    
    /**
     * XOR обратимое шифрование
     *
     * @param  string строка для зашифровки и расшифровки
     * @param  string ключ
     * @return string зашифрованная или расшифрованная строка
     */
    static function strCode($str, $passw="server"){
        $salt = "tree";
        $len = UTF8::strlen($str);
        $gamma = '';
        
        
        $hesh = md5($passw.$salt);
        
        $gamma .= $hesh;
        $gamma_length = UTF8::strlen($gamma);
        $total_length = $gamma_length;
        while($len>$total_length){
            $total_length += $gamma_length;
            $gamma .= $hesh;
        }

        $text = unpack('H*', $gamma);
        if(is_array($text))
            $gamma = $text[1];
        
        return $str^$gamma;
    }
    /**
     * Admin connect 
     *
     */
    static function admin_connect(){
        $core_path = getcwd();
        //$path = Url::i()->root().'/'.Core::TREE_ID.'/system/'.'classes/admin/Admin.php';
        $admin_path = '/'.trim(Url::i()->root().'/'.Core::TREE_ID,'/');
        if($admin_path = realpath('.'.$admin_path)){
            chdir($admin_path);
            
            
            $designAdmin = 'design';

            $modulesAdmin = 'modules';

            $systemAdmin = 'system';
            
            if (is_dir($admin_path.$designAdmin))
                $designAdmin = $admin_path.$designAdmin;
            
            if (is_dir($admin_path.$modulesAdmin))
                $modulesAdmin = $admin_path.$modulesAdmin;
            
            if (is_dir($admin_path.$systemAdmin))
                $systemAdmin = $admin_path.$systemAdmin;
            
            define('DESIGN_ADMIN', realpath($designAdmin).DIRECTORY_SEPARATOR);
            define('MODPATH_ADMIN', realpath($modulesAdmin).DIRECTORY_SEPARATOR);
            define('SYSPATH_ADMIN', realpath($systemAdmin).DIRECTORY_SEPARATOR);
            
            Core::new_paths(array(SYSPATH_ADMIN,DESIGN_ADMIN, APPPATH, SYSPATH));
            Admin_Module::module_path(TRUE);
            Core::$sample = 'admin';
            chdir($core_path);
            return TRUE;
        }
        return FALSE;
    }
}