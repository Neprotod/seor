<?php defined('SYSPATH') OR exit();
/**
 * Класс запросов
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Permission{
    protected $permission;
    
    /**
     * @var object Admin_Permission_Xml
     */
    protected $xml;
    
    /**
     * @var object Admin_Permission_Sql
     */
    public $sql;
    
    /**
     * @var object self
     */
    protected static $i;
    
    /**
     * @var object clone
     */
    protected static $c;
    
    /**
     * @var string путь к схеме xsd
     */
    public static $xsd;
    
    /**
     * @var string как называется файл правил в модуле
     */
    public static $xml_permission = "permission.xml";
    
    /**
     * Сохраняет образец класса, заполняет начальные данные
     *
     * @return object self
     */
    static function i($sample = FALSE){
        if ( ! isset( static::$i ) ) {
            static::$i = new static();
            static::$xsd = SYSPATH_ADMIN . "config" . DIRECTORY_SEPARATOR . "xsd". DIRECTORY_SEPARATOR ."permission.xsd";
            if(!is_file(static::$xsd))
                throw new Core_Exception("Нет файла по пути :path",array(":path"=>static::$xsd));
            
            static::$i->xml = new Admin_Permission_Xml();
            static::$i->sql = new Admin_Permission_Sql();
            static::$i->user = new Admin_Permission_User(static::$i->sql);
        }
        if($sample){
            return clone static::$i;
        }
        return static::$i;
    }
    
    function __clone(){
        $this->xml = new Admin_Permission_Xml();
        $this->sql = new Admin_Permission_Sql();
        $this->user = new Admin_Permission_User($this->sql);
    }
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function instance(){
        return static::i();
    }
    
    /**
     * Сверяет правила в файлах с правилами в таблице.
     *
     * @return void
     */
    function set_up(){
        $all_path = Admin_Module::mod_path();
        $module = array();
        //$all_path = array_fill_keys($all_path,1);
        foreach($all_path AS $key => $value){
            $path = $value.static::$xml_permission;
            if(is_file($value.static::$xml_permission)){
                $all_path[$key] = $path;
                $module += $this->xml->read($key, $path);
            }else{
                unset($all_path[$key]);
            }
        }
       
        // Выдаем массив для дальнейшего использования в классе Admin_Permission_Sql
        $sql = $this->xml->return_sql();
       
        // Сверяем массив с базой данных, добавляем новые записи, удаляем старые
        $this->sql_set_up($sql);
    }
    
    /**
     * Заполняет массив уже записанными правилами из базы данных.
     *
     * @return void
     */
    function init(){
        $this->permission = $this->sql->get_permission();
    }
    
    /**
     * Выдает все заполненные права.
     *
     * @return array массив всех прав
     */
    function all_permission(){
        return $this->permission;
    }
    
    /**
     * Выдает все права пользователя.
     *
     * @return array массив всех прав
     */
    function all_user_permission(Admin_Permission_User $user = NULL){
        return $this->user->get_all_permission();
    }
    
    /**
     * Разбивает строку URL и дальнейшей проверки прав
     *
     * @param  string  URL
     * @return array   возвращает массив с permission и rule
     */
    function link_permission($link){
        if($link != "/"){
            $arr = explode("/",$link);
            $mod = $arr[0];
            $method = "fetch";
            if(isset($arr[1])){
                $method = $arr[1];
            }
            return $this->perm_module($mod, $method);
        }else{
            return $this->perm_model("system", "default", "fetch");
        }  
        
    }
    /**
     * 
     *
     * @param  array  массив с данными пользователя
     * @return void
     */
    function user_init($user){
        
        /*if(!isset($user->user))
            throw new Core_Exception("У пользователя не установлена переменная user");*/
        $this->user->set_user($user);
        $this->user->pars($this->permission);
    }
    
    /**
     * Выводит права на модуль у пользователя пользователь 
     * задается через $this->user_init($user);
     *
     * @param  string  имя модуля
     * @param  string  имя метода
     * @param  bool    TRUE вернет только permission FALSE вернет еще и rule
     * @return array   возвращает массив с permission и rule
     */
    function perm_module($module,$method,$one = FALSE){
        if(!is_string($module) OR !is_string($method)){
            throw new Core_Exception("Принимаются только строковые типы");
        }
        if(!$one){
            return $this->perm_check($module, NULL, $method, NULL);
        }else{
            $perm = $this->perm_check($module, NULL, $method, NULL);
            return current($perm);
        }
    } 
    /**
     * Выводит права на контроллер у пользователя пользователь 
     * задается через $this->user_init($user);
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @param  bool    TRUE вернет только permission FALSE вернет еще и rule
     * @return array   возвращает массив с permission и rule 
     */
    function perm_controller($module, $class, $method){
        if(!is_string($module) OR !is_string($method)OR !is_string($class)){
            throw new Core_Exception("Принимаются только строковые типы");
        }
         return $this->perm_check($module, $class, $method, "controller");
    }
    /**
     * Выводит права на модель у пользователя пользователь 
     * задается через $this->user_init($user);
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @param  bool    TRUE вернет только permission FALSE вернет еще и rule
     * @return array   возвращает массив с permission и rule
     */
    function perm_model($module, $class, $method){
        if(!is_string($module) OR !is_string($method)OR !is_string($class)){
            throw new Core_Exception("Принимаются только строковые типы");
        }
        return $this->perm_check($module, $class, $method, "model");
    }
    
    /**
     * Проверяет и достает права пользователя
     * задается через $this->user_init($user);
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @param  string  тип controller или model
     * @return array   возвращает массив с permission и rule
     */
    protected function perm_check($module, $class, $method,$type = NULL){
        try{
            if(!$class){
                $mod = $this->module($module,$method);
            }else{
                $mod = $this->$type($module,$class,$method);
            }
            $perm_id = Arr::path($mod["method"],"id");
            return $this->user->get_permission($perm_id);
        }catch(Exception $e){
            try{
                if(!$class){
                    Admin_Module::mod_path($module);
                }else{
                    $type = "Admin_".ucfirst($type);
                    $type::factory($class, $module, NULL, FALSE);
                }
                    
                $found = array(
                    "permission" => TRUE,
                    "rule" => array()
                );
                return $found;
            }catch(Exception $e){
                throw new Core_Exception("Ошибка прав доступа: <br />".$e->getMessage());
            }
        }
    }
    /**
     * Выводит права на модуль
     *
     * @param  string  имя модуля
     * @param  string  имя метода
     * @return array   возвращает массив с заполненными правами  
     */
    function module($module = NULL,$method = NULL){
        if(!isset($this->permission["module"]))
            throw new Core_Exception('Вы не инициализировали правила, запустите метод $this->init()');
        
        if(empty($module)){
            return $this->permission;
        }
        
        $found = array(
            "class" => array(),
            "method" => array(),
            "rule" => array()
            );
        
        if(!isset($this->permission["module"][$module])){
            throw new Core_Exception('Не существует прав на модуль <b>:module</b>',array(":module"=>$module));
        }
        
        $class = $this->permission["module"][$module];
        
        $found["class"] = $class;
        
        if(empty($method))
            return $found;
        
        if(!isset($class["method"][$method])){
            throw new Core_Exception('Не существует прав на метод <b>:method</b> в модуле <b>:module</b>',array(":method"=>$method,":module"=>$module));
        }
        $method = $class["method"][$method];
        
        $found["method"] = $method;
        if(isset($this->permission["rule"][$method["id"]]))
            $found["rule"] = $this->permission["rule"][$method["id"]];
        
        return $found;
    }
    
    /**
     * Выводит права на контроллер
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @return array   возвращает массив с заполненными правами
     */
    function controller($module, $class = NULL,$method = NULL){
        // Для отображения ошибок
        $error = array();
        $error["not"] = 'Не существует прав на контроллеры у модуля <b>:module</b>';
        $error["class"] = 'Не существует прав на контроллер <b>:class</b> в модуле <b>:module</b>';
        $error["method"] = 'Не существует прав на метод <b>:method</b> в контроллере <b>:class</b> у модуля <b>:module</b>';
        
        // Получаем права
        return $this->controller_model($module, $class,$method,"controller",$error);
    }
    /**
     * Выводит права на модель
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @return array   возвращает массив с заполненными правами
     */
    function model($module, $class = NULL,$method = NULL){
        $error = array();
        $error["not"] = 'Не существует прав на модели у модуля <b>:module</b>';
        $error["class"] = 'Не существует прав на модель <b>:class</b> в модуле <b>:module</b>';
        $error["method"] = 'Не существует прав на метод <b>:method</b> в модели <b>:class</b> у модуля <b>:module</b>';
        
        // Получаем права
        return $this->controller_model($module, $class,$method,"model",$error);
    }
    /**
     * Выводит права на контроллер или модель
     *
     * @param  string  имя модуля
     * @param  string  имя класса
     * @param  string  имя метода
     * @return array   возвращает массив с заполненными правами
     */
    protected function controller_model($module, $class = NULL,$method = NULL,$type,$error){
        $mod = $this->module($module);
        if(!isset($mod["class"][$type]))
            throw new Core_Exception($error["not"],array(":module"=>$module));

        $controller = $mod["class"][$type];
        if(empty($class))
            return $controller;
        
        
       $found = array(
            "class" => array(),
            "method" => array(),
            "rule" => array()
            );
        
        if(!isset($controller[$class])){
            throw new Core_Exception($error["class"],array(":class"=>$class,":module"=>$module));
        }
        
        $class = $controller[$class];
        
        $found["class"] = $this->permission["class"][$class["id"]];
        
        if(empty($method))
            return $found;
        
        if(!isset($class["method"][$method])){
            throw new Core_Exception($error["method"],array(":class"=>$class["class_name"],":method"=>$method,":module"=>$module));
        }
        $method = $class["method"][$method];
        
        $found["method"] = $method;
        if(isset($this->permission["rule"][$method["id"]]))
            $found["rule"] = $this->permission["rule"][$method["id"]];
        
        return $found;
    }
    /**
     * SQL сравнения прав
     *
     * @param  array  передается массив генерируемый Admin_Permission_Xml->return_sql()
     * @return void
     */
    protected function sql_set_up($sql){
        $this->sql->set_up($sql);
    }
    
}