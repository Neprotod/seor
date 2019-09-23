<?php defined('SYSPATH') OR exit();
/**
 * 
 * 
 * @package    module/xml
 * @category   query
 */
class Model_Query_XML{
    
   /**
     * @var string все запросы с XML файла
     */
    public $request = array();
   /**
     * @var string путь к директории
     */
    private $all_path = 'query';
   /**
     * @var string файл с запросами
     */
    private $default_query = 'query';
   /**
     * @var string файл с запросами
     */
    private $default_xsd = 'query';
   /**
     * @var string путь к схеме
     */
    private $shema = '';
   /**
     * @var array не проверяемые типы
     */
    private $unverifiable_type = array("transaction");

    /**
     * @var object DOM
     */
    private $xml;
    /**
     * @var string расширение файла xml
     */
    private $xml_ext = 'xml';
    /**
     * @var string расширение файла xsd
     */
    private $xsd_ext = 'xsd';
    /**
     * @var array имена group и query
     */
    private $names = array("group"=>"","query"=>"");
    
    
    
    function __construct($parent){
        $path = "";
        // Если административный класс, подключаем административные пути, и дополняем путь.
        if(strtolower(get_class($parent)) == "admin_query"){
            $mod  = "Admin";
            $path = "admin".DIRECTORY_SEPARATOR;
        }else{
            $mod  = "Core";
        }
        
        // Подключаем файлы XML и XSD
        if(!$this->all_path = $mod::find_file("config", $this->default_query, $this->xml_ext)){
                $path .= "config".DIRECTORY_SEPARATOR.$this->default_query.".".$this->xml_ext;
                
                throw new Core_Exception('Нет XML файла, проверьте по пути <b>:xml_path</b>',array(":xml_path"=> $path));
        }
        if(!$this->shema = Core::find_file("config", "xsd".DIRECTORY_SEPARATOR.$this->default_xsd, $this->xsd_ext)){
                $path = "config".DIRECTORY_SEPARATOR."xsd".DIRECTORY_SEPARATOR.$this->default_xsd.".".$this->xsd_ext;
                throw new Core_Exception('Нет XML файла схемы, проверьте по пути <b>:xsd_path</b>',array(":xsd_path"=> $path));
        }
    }
    
    function pars(){
        $xml = new DOMDocument();
        $xml->presserveWhiteSpase = false;
        $xml->load($this->all_path);
        
        $this->xml = $xml;
        
        if(is_file($this->shema)){
            $this->xml->schemaValidate($this->shema);
        }
        
        $root = $xml->documentElement;
        
        $fonds = array();
        if($childs = $root->childNodes){
            foreach($childs AS $child){
                if($child->nodeType == 1){
                    $action = strtolower($child->nodeName);
                    
                    $fonds += $this->$action($child);
                }
            }
        }
        $this->request = $fonds;
    }
    
    protected function query($child){
        $fonds = array();
        if($key = $child->getAttribute('name')){
            // Задаем имя запроса для ошибок
            $this->names['query'] = $key;
            $fonds[$key] = array();
            if($childs = $child->childNodes){
                foreach($childs AS $child){
                    if($child->nodeType == 1){
                        $action = strtolower($child->nodeName);
                        
                        $fonds[$key] += $this->$action($child);
                    }
                }
            }
            
            // Проверяем запрос
            $this->check($fonds);
            
            // Обнуляем
            $this->names['query'] = '';
            
            return $fonds;
        }else{
                echo "Error";
        }
        return array();
    }
    protected function group($child){
        $fonds = array();
        if($key = $child->getAttribute('name')){
            // Задаем имя запроса для ошибок
            $this->names['group'] = $key;
            
            $fonds[$key] = array();
            if($childs = $child->childNodes){
                foreach($childs AS $child){
                    if($child->nodeType == 1){
                        $action = strtolower($child->nodeName);
                        
                        $fonds[$key] += $this->$action($child);
                    }
                }
            }
            
            // Обнуляем
            $this->names['group'] = '';
            
            return $fonds;
        }else{
                echo "Error";
        }
        return array();
    }
    
    protected function request($child){
        $founds = array();
        $founds['request'] = trim($child->nodeValue);
        $founds['type'] = strtolower(trim($child->getAttribute("type")));
        return $founds;
    }
    
    protected function description($child){
        $founds = array();
        $founds['description'] = trim($child->nodeValue);
        return $founds;
    }
    protected function params($child){
       $fonds = array();
       $key = 'params';
       $fonds[$key] = array();
        if($childs = $child->childNodes){
            foreach($childs AS $child){
                if($child->nodeType == 1){
                    $value = trim($child->nodeValue);

                    if($child->getAttribute("check") === '0'){
                        $fonds[$key][$value] = 1;
                    }else{
                        $fonds[$key][$value] = NULL;
                    }
                }
            }
        }
        return $fonds;
    }
    
    protected function check($fonds){
        $fonds = current($fonds);
        $path = $this->names["query"];
        if($this->names["group"]){
            $path = $this->names["group"] . "." . $path;
        }
        
        $excep = array(
          ":path"=> $path,
          ":request"=> $fonds["request"]
          );
        
        // Проверяем запрос
        if(!in_array(strtolower($fonds["type"]),$this->unverifiable_type) AND !is_int(stripos($fonds["request"],$fonds["type"]))){
            $excep[":type"] = $fonds["type"];
            throw new Core_Exception('В запросе <b>:path</b> нет типа <b>:type</b> обрати внимание на запрос:<br/><pre>:request</pre>', $excep);
        }
        if(isset($fonds["params"])){
            $error = array();
            foreach($fonds["params"] as $key => $value){
                if(!is_int(strpos($fonds["request"],$key))){
                    $error[] = $key;
                }
            }
            
            if(!empty($error)){
                $error = implode(" | ",$error);
                $excep[":param"] = $error;
                
                throw new Core_Exception('В запросе <b>:path</b> не правильно заданы параметры <b>:param</b> обрати внимание на запрос:<br/><pre>:request</pre>',$excep);
            }
        }
            
    }
}