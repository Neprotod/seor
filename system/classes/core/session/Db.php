<?php defined('SYSPATH') OR exit();
/**
 * Cookie - класс, основанный на сессии .
 *
 * @package    Tree
 * @category   Core
 */
class Core_Session_Db extends Session{
    protected $_user = array();
    protected $_old = array();
    
    public function __construct($config, $token){
        if(isset(Registry::i()->user)){
            $this->_user = Registry::i()->user;
            $this->read();
        }
        elseif($token){
            $where = "token = $token AND lifetime > NOW()";
            if($token = Query::i()->sql("tokens.get",array(":where"=>$where))){
                $this->_user["id"] = $token["id_user"];
            }else{
                throw new Core_Exception("Токен не соответствует сессии. Работа сайта прекращена.");
            }
        }else{
            throw new Core_Exception("Сессия не была установлена. Работа сайта прекращена.");
        }
    }
    /**
     * @return  string
     */
    public function id(){
        return NULL;
    }
    /**
     * @return  bool
     */
    protected function _read($id = NULL){
        $data = Query::i()->sql("session.get",array(":id"=>$this->_user["id"]));
        if($data)
            foreach($data AS $value){
                $this->_data[$value["name"]] = $value["value"];
            }
        $this->_old = $this->_data;
    }
    /**
     * @return  bool
     */
    protected function _regenerate(){
        return NULL;
    }
    /**
     * @return  bool
     */
    protected function _write(){
        $old = $this->_old;
        $data = $this->_data;
        
        $update = Arr::array_diff_assoc($data, $old);
             
        $insert = array_diff_key($data, $old);
        
        $update = Arr::array_diff_assoc($update, $insert);
        
        $delete = array_diff_key($old, $data);
        
        
        /*
        $update = $this->_to_serialize($update);
        $insert = $this->_to_serialize($insert);
        */

        $sql = Model::factory("sql","system");
        
        try{
            if($insert){
                $insert = $this->_to_name($insert);
                $string = $sql->insert_string($insert);
                
                Query::i()->sql("session.insert",array(":set"=>$string));
                
            }
            if($update){
                $set = "";
                $where = $sql->insert_string(array_keys($update));
                foreach($update AS $key => $value){
                    $set .= sprintf("WHEN name = %s THEN %s ",DB::escape($key), DB::escape($value));
                }

                Query::i()->sql("session.update",array(
                                                        ":set" => $set,
                                                        ":where" => $where,
                                                        ":id_user" => $this->_user["id"],
                                                        ));
            }
            if($delete){
                $where = sprintf("%s AND id_user = %s",$sql->insert_string(array_keys($delete)), $this->_user["id"]);
                
                Query::i()->sql("delete",array(
                                                ":table" => "session",
                                                ":where" => "name",
                                                ":insert" => $where,
                                                ));
            }
        }catch(Exception $e){
                Core_Exception::handler($e);
        }
        
    }
    protected function _to_serialize($data){
        if(!empty($data))
            foreach($data AS $key => &$value){
                $data[$key] = $this->_serialize($value);
            }
        return $data;
    }
    protected function _serialize($data){
        if(is_array($data))
           $data = json_encode($data);
               
        return $data;
    }
    protected function _to_name($data){
        $new = array();
        if(!empty($data)){
            foreach($data AS $key => $value){
                   $new[$key]["id_user"] = $this->_user["id"];
                   $new[$key]["name"] = $key;
                   $new[$key]["value"] = $value;
            }
        }
        return $new;
    }
    
    public function write(){
        // Установите последний активный timestamp
        $this->_data['last_active'] = time();

        try{
            return $this->_write();
        }catch (Exception $e){
            // Log & ignore all errors when a write fails
            //Core::$log->add(Log::ERROR, Core_Exception::text($e))->write();

            return FALSE;
        }
    }
    
    /**
     * Установка переменной в массиве сессии.
     *
     *     $session->set('foo', 'bar');
     *
     * @param   string   $key   имя переменной
     * @param   mixed    $value значение
     * @return  $this
     */
    public function set($key, $value){
        $this->_data[$key] = $this->_serialize($value);

        return $this;
    }
    
    /**
     * Получите переменную массива, сессии.
     *
     *     $foo = $session->get('foo');
     *
     * @param   string  $key     Имя переменной
     * @param   mixed   $default Значение по умолчанию для возвращения.
     * @return  mixed
     */
    public function get($key, $default = NULL){
        return array_key_exists($key, $this->_data) ? Arr::is_json($this->_data[$key]) : $default;
    }
    
    /**
     * Получить и удалить переменную из массива сессии.
     *
     *     $bar = $session->get_once('bar');
     *
     * @param   string  $key     имя переменной
     * @param   mixed   $default значение по умолчанию для возвращения.
     * @return  mixed
     */
    public function get_once($key, $default = NULL){
        $value = Arr::is_json($this->get($key, $default));

        unset($this->_data[$key]);

        return $value;
    }
    
    /**
     * @return  bool
     */
    protected function _destroy(){
        Query::i()->sql("delete",array(
                                        ":table" => "session",
                                        ":where" => "id_user",
                                        ":insert" => insert_string((array)$_user["id"]),
                                        ));
    }
    
}
