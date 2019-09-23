<?php defined('SYSPATH') OR exit();
/**
 * Класс запросов
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Query{
    /**
     * @var object содержит образец класса
     * @static
     */
    protected static $i;
    
    const SELECT = 'select';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    /**
     * @var object хранит модель поведения
     */
    protected $xml = array();
    
    /**
     * @var array параметры которые должны пройти без обработки
     */
    protected $rout_param = array(":set",":where",":insert",":table");


    private function __construct() { }
    
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function i() {
        if ( ! isset( static::$i ) ) {
            static::$i = new static();
            static::$i->xml = Model::factory('query','xml',array(static::$i));
            static::$i->xml->pars();
        }
        return static::$i;
    }
    /**
     * Сохраняет образец класса
     *
     * @return object self
     */
    static function instance() {
        return static::i();
    }
    
    /**
     * Возвращает ячейку массива с self::$i->xml->request
     *
     * @param  string  имя запроса, можно передавать в таком виде [group|group.query]
     * @return mixed   просто возвращает ячейку с массива self::$i->xml->request
     */
    function get($name){
        return Arr::path(static::$i->xml->request,$name);
    }
    /**
     * Возвращает SQL запрос
     *
     * @param  string  имя запроса, можно передавать в таком виде group.query
     * @return string  запрос
     */
    function get_query($name){
        $name .= ".request";
        return Arr::path(static::$i->xml->request,$name);
    }
    /**
     * Возвращает description
     *
     * @param  string  имя запроса, можно передавать в таком виде group.query
     * @return string  description
     */
    function get_description($name){
        $name .= ".description";
        return Arr::path(static::$i->xml->request,$name);
    }
    /**
     * Возвращает массив параметров
     *
     * @param  string  имя запроса, можно передавать в таком виде group.query
     * @return array   запрос
     */
    function get_params($name){
        $name .= ".params";
        return Arr::path(static::$i->xml->request,$name);
    }
    
    /**
     * Отправляет запрос на сервер и возвращает результат
     *
     * @param  string путь к запросу в таком виде group.query
     * @param  array  параметры array(":param"=>"param")
     * @return array   
     */
    function sql($name, $params = NULL, $sort = NULL, $one = NULL, $queryType = false, $return = FALSE){
        if($get = $this->get($name)){
            // Берем тип из файла или принудительно вставляем
            $type = (!$queryType) 
                        ? $get["type"]
                        : $queryType;
            
            $sql = $get["request"];

            // Проверяем тип запроса
            switch($type){
                case self::SELECT:
                        $type = Database::SELECT; 
                        break;
                case "set":
                case "transaction":
                case self::INSERT: 
                        $type = Database::INSERT; 
                        break;
                case self::UPDATE: 
                        $type = Database::UPDATE; 
                        break;
                case self::DELETE: 
                        $type = Database::DELETE; 
                        break;
                default:
                    throw new Core_Exception('Типа SQL запроса <b>:type</b> не существует',array(":type"=>$type));
            }
            
            $error = array();
            $text = '';

            if(empty($params) AND isset($get['params'])){
                $text = "Вы не задали параметры <b>:key</b>";
                $error[":key"] = $get['params'];
            }
            else if(!empty($params) AND !isset($get['params'])){
                throw new Core_Exception('Вы задаете параметры запросу <b>:name</b>, а у запроса их нет',array(":name"=>$name));
            }
            else if(!empty($params) AND isset($get['params'])){
               // Если не хватает параметров
               $key = array_diff_key($get['params'], $params);
               if($key){
                   $text = "не хватает параметра <b>:key</b> ";
                   $error[":key"] = $key;
               }
               // Если лишний [ВРЕМЕННО ОТКЛЮЧЕН]
               /*$key = array_diff_key($params, $get['params']);
               if($key){
                   $text .= "лишние параметры <b>:excess</b> все заданные параметры в файле: <br/>:param<br/><br/>";
                   $error[":excess"] = $key;
                   $error[":param"] = implode("<br/>",array_keys($get['params']));
               }*/
               
               if(isset($text))
                   $text = UTF8::ucfirst($text);
            }
            
            // Если есть ошибки
            if($error){
                $request = $get['request'];
                if(!empty($error[":key"])){
                    $replace = array();
                    $keys = array_keys($error[":key"]);
                    $error[":key"] = implode(" | ",$keys);
                    foreach($keys as $value){
                        $replace[$value] = "<b>".$value."</b>";
                    }
                    $request = STR::__($get['request'], $replace);
                }
                if(isset($error[":excess"]) AND !empty($error[":excess"])){
                    $keys = array_keys($error[":excess"]);
                    $error[":excess"] = implode(" | ",$keys); 
                }
               
                $error[':request'] = $request;
                $error[':name'] = $name;
                
                throw new Core_Exception($text.' обратите внимание на запрос <b>:name</b>:<br /><pre>:request</pre>',$error);
            }
            
            $rout_param = $this->rout_param;
            
            if(isset($get['params'])){
                foreach($get['params'] AS $key => $value){
                    if($value){
                        $rout_param[] = $key;
                    }
                }
            }
            
            // Если есть параметр обозначенные в массиве $this->rout_param
            if(!empty($params) AND $to_set = arr::intersect_key($params, $rout_param)){
                // Убираем ненужное значение из массива
                $params = array_diff_key($params, $to_set);
                
                $sql = Str::__($sql,$to_set); 
            }
            // Создаем образец
            $sql = DB::placehold($sql);
           
            $query = DB::query($type, $sql);
            
            // Загружаем параметры
            if(!empty($params)){
                $query->parameters($params);
            }

            if($return){
                return $query->return_sql();
            }else{
                return $query->execute($sort, $one);
            }
        }else{
            throw new Core_Exception('SQL запрос <b>:name</b> не найден, проверьте имя',array(":name"=>$name));
        }
    }
}