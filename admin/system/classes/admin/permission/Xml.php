<?php defined('SYSPATH') OR exit();
/**
 * Класс обработки permission в XML 
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Permission_Xml{
    /**
     * @var string имя модуля который сейчас подключается
     */
    protected $module_name;
    
    /**
     * @var string в данное свойство попадает одно из свойств $this->module|controller|model
     */
    protected $connect_class;
    
    /**
     * @var string имя класса который будет отображен в ошибках
     */
    protected $error_class;
    
    /**
     * @var string имя класса подключающий модули
     */
    protected $module = "Admin_Module";
    
    /**
     * @var string имя класса подключающий контроллеры
     */
    protected $controller = "Admin_Controller";
    
    /**
     * @var string имя класса подключающий модели
     */
    protected $model = "Admin_Model";
    
    /**
     * @var array содержит имена всех методов класса
     */
    protected $reflection;
    
    /**
     * @var string для проверки запросов
     */
    protected $query = "Admin_Query";
    /**
     * @var string маркер типа как module, controller, model
     */
    protected $sql_type;
    /**
     * @var string маркер текущего класса
     */
    protected $sql_class;
    /**
     * @var string маркер текущего модуля
     */
    protected $id_module;
    /**
     * @var string id текущей ячейки, для ссылок на class или permission
     */
    protected $id;
    /**
     * @var string id текущей ячейки, для ссылок на class или permission
     */
    protected $id_permission;
    
    /**
     * @var array используется как ссылка для упрощения заполнения
     */
    protected $found = array();
    /**
     * @var array содержит информацию о всех модулях
     */
    protected $clear_sql = array(
                            "class" => array(),
                            "permission" => array(),
                            "rule" => array(),
                            "sql" => array(),
                            );
    /**
     * @var array содержит информацию о всех модулях
     */
    protected $sql = array(
                            "class" => array(),
                            "permission" => array(),
                            "rule" => array(),
                            "sql" => array(),
                            );
    
    /**
     * Читает файл прав из папки модуля? по одному модулю за раз
     *
     * @param  string  имя модуля
     * @param  string  путь к файлу permission.xml
     * @return array   массив всех прав
     */
    function read($name, $path){
        if(!is_file($path)){
           throw new Core_Exception("Пришел не файл");
        }else{
            $info = pathinfo($path);
            if(strtolower($info['extension']) != "xml")
                throw new Core_Exception("Переданный файл не xml <b>:path</b>",array(":path"=>$path));
            
        }
        
        $this->module_name = $name;
        $this->connect_class = $this->module;

        // SQL
        // Удаляем и обнуляем ссылку
        unset($this->found);
        $this->found = array();
        
        $this->sql["class"][] = &$this->found; 
        $this->id_module = count($this->sql["class"]) - 1; 
        $this->id = $this->id_module; 
      
        /////////////

        return  array($name => $this->pars($path));
    }
    
    /**
     * Открывает файл xml и запускает процесс чтения
     *
     * @param  string   путь к файлу прав
     * @return array    массив всех прав
     */
    protected function pars($path){
        $xml = new DOMDocument();
        $xml->presserveWhiteSpase = false;
        $xml->load($path);
        
        
        $xml->schemaValidate(Admin_Permission::$xsd);
        
        try{
            return $this->root($xml);
        }catch(Exception $e){
            throw new Core_Exception("Ошибка парсинга: <br/> :message",array(":message"=>$e->getMessage()));
        }
    }
    
    /**
     * Перебирает детей и запускает метод который совпадает с именем элемента xml
     *
     * @param  object   элемент DOM
     * @param  bool     если TRUE то к ячейке будет добавлено имя тега DOM
     * @return array    массив прав
     */
    protected function return_child($parent,$toName = FALSE){
        $fonds = array();
        if($childs = $parent->childNodes){
            foreach($childs AS $child){
                if($child->nodeType == 1){
                    $action = strtolower($child->nodeName);
                    if(!$toName)
                        $fonds += $this->$action($child);
                    else{
                        if(!isset($fonds[$action]))
                            $fonds[$action] = array();
                       $fonds[$action] += $this->$action($child);
                    }
                }
            }
        }
        
        return $fonds;
    }
    
    /**
     * Дополняет массив атрибутами
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function return_attr($child){
        $fonds = array();
        
        $name = $child->getAttribute('name');
        $fonds[$name] = array();
        if($child->hasAttribute("description")){
            $fonds[$name]["description"] = $child->getAttribute('description');
            $this->found["description"] = $fonds[$name]["description"];
            
        }
        return $fonds;
    }
    /**
     * Дополняет массив атрибутам description
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function return_desc($child){
        $fonds = array();
        
        if($child->hasAttribute("description")){
            $fonds["description"] = $child->getAttribute('description');
            $this->found["description"] = $fonds["description"];
        }
        return $fonds;
    }
    /**
     * Парсит корневой элемент
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function root($dom){
        $root = $dom->documentElement;
        
        $fonds = $this->return_desc($root);
        
        // При первом запуске нужно проверить, есть ли methods если нет, запускаем принудительно.
        if($childs = $root->childNodes){
            foreach($childs AS $child)
                if($child->nodeType == 1)
                    break;
        }
        
        if($child->tagName != "methods"){
            return $fonds += $this->methods($root);
        }else{
            return $fonds += $this->return_child($root);
        }
        
    }
    
    /**
     * Загружает из класса в свойства $this->reflection все методы
     *
     * @param  string   имя класса
     * @return void
     */
    protected function reflection_check($class){
        $this->error_class = $class;
        $reflection = new ReflectionClass($class);
        $reflection = $reflection->getMethods();

        $this->reflection = array();
        foreach($reflection as $value){
            $this->reflection[$value->name] = TRUE;
        }
    }
    /**
     * Парсит элемент methods
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function methods($child){
        $this->sql_type = 'module';
        $this->sql_class = $this->module_name;
        $module = $this->module;
       
        $class = $module::factory($this->module_name,FALSE);
        
        $this->reflection_check($class);
        
        /*SQL*/
        $this->found["class_name"] = $this->sql_class;
        $this->found["type"] = $this->sql_type;
        $this->found["id_class"] = NULL;
        /////////////////////

        return array("methods"=>$this->return_child($child));
    }
    
    /**
     * Парсит элемент method
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function method($child){
        // Удаляем и обнуляем ссылку
        unset($this->found);
        $this->found = array();
        
        $fonds = array();
        
        $fonds = $this->return_attr($child);
        
        $name = key($fonds);
        
        if(!isset($this->reflection[$name]))
            throw new Core_Exception("Нет метода <b>:name</b> в :class",array(":name"=>$name,":class"=>$this->error_class));
        
        /*SQL*/
        $id = $this->id;
        
        $this->sql["permission"][] = &$this->found;
        
        $this->id_permission = count($this->sql["permission"]) - 1;
        
        $this->found["method"] = $name;
        $this->found["id_class"] = $this->id;
        
        ///////////////////////////////
        
        $fonds[$name] += $this->return_child($child);
        
        return $fonds;
    }
    
    /**
     * Парсит элемент controllers
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function controllers($child){
        $this->sql_type = 'controller';
        $this->connect_class = $this->controller;
        
        return array("controllers"=>$this->return_child($child));
    }
    
    /**
     * Парсит элемент models
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function models($child){
        $this->sql_type = 'model';
        
        $this->connect_class = $this->model;
        
        return array("models"=>$this->return_child($child));
    }
    
    /**
     * Парсит элемент permission
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function permission($child){
        return array("permission"=>$this->return_child($child,TRUE));
    }
    
    /**
     * Парсит элемент classes
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function classes($child){
        // Удаляем и обнуляем ссылку
        unset($this->found);
        $this->found = array();
        
        $fonds = array();
        
        $fonds = $this->return_attr($child);
        
        $name = key($fonds);
        

        $connect = $this->connect_class;
        $class = $connect::factory($name,$this->module_name,NULL,FALSE);
        
        $this->reflection_check($class);
        
        /*SQL*/
        
        $this->sql["class"][] = &$this->found; 
        $this->id = count($this->sql["class"]) - 1;
        
        $this->found["description"] = (isset($fonds[$name]["description"]))
                                                                        ? $fonds[$name]["description"]
                                                                        : NULL;
        $this->found["class_name"] = $name;
        $this->found["type"] = $this->sql_type;
        $this->found["id_class"] = $this->id_module;
        ///////////////
        
        $fonds[$name] += $this->return_child($child);
        
        
        return $fonds;
    }
    
    /**
     * Парсит элемент rule
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function rule($child){
        // Удаляем и обнуляем ссылку
        unset($this->found);
        $this->found = array();
        
        $fonds = array();
        
        $fonds = $this->return_attr($child);
        
        $name = key($fonds);
        
        /*SQL*/
        
        
        $this->sql["rule"][$this->id_permission][$name] = &$this->found;
        $this->found["rule"] = $name;
        //$this->found["description"] = (isset($fonds[$name]["description"]))
                                                                        //? $fonds[$name]["description"]
                                                                       // : NULL;
                                                                        
        $this->found["id_permission"] = $this->id_permission;   
        ///////////////////////////
        
        return $fonds;
    }
    
    /**
     * Парсит элемент sql
     *
     * @param  object   элемент DOM
     * @return array    массив прав
     */
    protected function sql($child){
        // Удаляем и обнуляем ссылку
        unset($this->found);
        $this->found = array();
        
        $fonds = array();
        
        $fonds = $this->return_attr($child);
        $name = key($fonds);
        
        $path = $child->getAttribute('path');
        
        $fonds[$name]["path"] = $path;
        
        $query = $this->query;
        
        if(!$query::i()->get($path)){
            throw new Core_Exception("Нет SQL запроса <b>:sql</b>",array(":sql"=>$path));
        }
        
        /*SQL*/
        $this->sql["sql"][$this->id_permission][$name] = &$this->found;
        $this->found["name"] = $name;
        $this->found["description"] = (isset($fonds[$name]["description"]))
                                                                        ? $fonds[$name]["description"]
                                                                        : NULL;
                                                                        
        $this->found["id_permission"] = $this->id_permission;   
        $this->found["path"] = $fonds[$name]["path"];   
        /////////////////////
        
        return $fonds;
    }
    
    /**
     * Возвращает массив SQL собранный из всех модулей.
     * 
     * @return array массив sql
     */
    function return_sql(){
        $get = $this->sql;
        $this->sql = $this->clear_sql;
        return $get;
    }
}