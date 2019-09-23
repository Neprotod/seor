<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_User_Group_Moderator_Admin{
    public $error;
    public $xml;
    public $sql;
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
    }
    
    /**
     * Перенаправляет, на список групп или на группу
     *
     *     $bar = $session->get_once('bar');
     *
     * @return  string
     */
    function get(){
        if($id = Request::get("group")){
            return $this->group($id);
        }else{
            return $this->all_group();
        }
    }
    /**
     * Перенаправляет, на список групп или на группу
     *
     * @param   string  ID группы или "new" 
     * @return  string
     */
    protected function group($id){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        $xml_pars = "admin_template|default::moderator_group_group";
       
        $tech = array(
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
            "action" => Url::root(NULL),
        );

        $found = array();
        
        $found["group"] = array();
        if($id != "new"){
            $found["group"] = Admin_Query::i()->sql("moderator.type",array(":id"=>$id),"id");
        }
        $found["module"] = array();
        
        $perm = Admin_Permission::i()->all_permission();
        // Если есть модуль, добавляем роль в этот массив
        if(isset($perm["module"])){
            $module = $perm["module"];
            foreach($module AS &$class){
                $this->permission_pack($class,$perm);
                if(isset($class["controller"])){
                    foreach($class["controller"] AS &$controll){
                        $this->permission_pack($controll,$perm);
                    }
                }
                if(isset($class["model"])){
                    foreach($class["model"] AS &$controll){
                        $this->permission_pack($controll,$perm);
                    }
                }
            }
            $found["module"] = $module;
        }
        //Пользовательские права
        $found["status"] = array();
        
       
        if($id != "new"){
            $sample_perm = Admin_Permission::i(TRUE);
            $sample_perm->init();
            $sample_perm->user_init(array("id_type"=>$id));
            
            $user_permission = $sample_perm->all_user_permission();
            
            $stat = Arr::search_one("status",$user_permission);
            $stat = Arr::flatten($stat);
            
            if(!isset($stat["status"]) OR !$stat["status"]){
                $found["status"]["ban"] = $user_permission;
            }else{
                $found["status"]["perm"] = $user_permission;
            }
            //var_dump($found["status"]);
            //exit;
        }
        if(Request::method("post")){
            $this->post($found, $perm);
        }
        
        
        return $this->xml->preg_load($found,$xml_pars,$xml_pars,$tech);
    }
    
    /**
     * Обрабатывает Post запрос
     *
     * @param   array  отобранные данные которые будут дополнятся
     * @param   array  все права
     * @return  void
     */
    protected function post(&$found, $perm){
        $status_control = Request::post("status_control");
        $group = Request::post("group");
        $all = Request::post("all");
        // Находим пустые значения и заменяем их на NULL
        $group = Arr::replace_value($group,"",NULL);
        
        // Собираем значения у пользователя
        $user_perm = $found["status"];
        $found["status"] = $all;
        $original_group = $found["group"];
        $found["group"] = $group;
        
        if(!Arr::emptys($all)){
            if($status_control === "1"){
                end($all);
            }
            $test = key($all);

            $all = $all[$test];
            
            $for_perm = NULL;
            $for_rule = NULL;
            
            if(isset($user_perm[$test])){
                $user_perm = $user_perm[$test];
                
                //////////////////
                //Определяем права
                //////////////////
                $for_perm = $this->get_delete_insert($user_perm,$all,"permission");
                $for_rule = $this->get_delete_insert($user_perm,$all,"rule");
                
            }else{
                
                // Не совпали режимы прав. Значит нужно удалить все старые права
                // и добавить новые с новым режимом.
                
                $user_perm = current($user_perm);
                // Получим все значения удаление.
                $delete = $this->get_delete_insert($user_perm,array(),"permission");
                // Получим все значения на вставку.
                $insert = $this->get_delete_insert(array(),$all,"permission");
                
                $for_perm = Arr::merge($delete,$insert); 
                
                // Тоже самое, но с Rule
                $delete = $this->get_delete_insert($user_perm,array(),"rule");
                $insert = $this->get_delete_insert(array(),$all,"rule");
                $for_rule = Arr::merge($delete,$insert);
            }
        }else{
            if(!Arr::emptys($user_perm)){
                $user_perm = current($user_perm);
            }

            // Получим все значения удаление.
            $for_perm = $this->get_delete_insert($user_perm,array(),"permission");
            $for_rule = $this->get_delete_insert($user_perm,array(),"rule");
        }

        // Проверяем группу.
        $validator = Model::factory("validator","system");
 
        $valid = array(
                        "id" => array(
                            "type" => "int",
                        ),
                        "type" => array(
                            "type" => "str",
                            "required" => TRUE,
                            "pattern" => "^[a-zA-Z]{3,}$",
                        ),
                        "title" => array(
                            "type" => "str",
                            "required" => TRUE,
                        )
                );
                
        // Проверяем форму на ошибки.
        $t = $validator->valids($valid,$group);

        if($t){
            $t["group"] = current($t);
            $this->error->set("error","warning",array("message"=>"Неправильно заполнено поле"));

            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Могут быть только латинские буквы без пробелов"));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }
                
            }  
            
            Registry::i()->errors = $this->error->output();
            return FALSE;
        }

        // Заполняем в базу.
        reset($group);
        
        $group = current($group);
        $original_group = current($original_group);
        
        if(isset($group["id"])){
            if($update = Arr::array_diff_assoc($group,$original_group, FALSE)){
                $message[] = "Группа прав была обновлена";
                // Обновляем.
                $set = $this->sql->where(",",$update);
        
                Admin_Query::i()->sql("update",array(
                                                      ":set" => $set,
                                                      ":id" => $group["id"],
                                                      ":table" => "admin_type"
                                                    ));
            }
        }else{
            $message[] = "Группа прав была добавлена";
            // Записываем.
            $id = Admin_Query::i()->sql("moderator.insert",array(
                                                      ":type" => $group["type"],
                                                      ":title" => $group["title"],
                                                      ":description" => $group["description"]
                                                    ));
            $group["id"] = $id[0];
        }
        $message["perm"] = "";
        // Удаляем ненужные правила и rule.
        if(!empty($for_perm["delete"])){
            $message["perm"] .= "Permission было удалено<br/>";
            $this->delete($for_perm["delete"],"admin_permission","id_permission",$group["id"]);
        }
        if(!empty($for_rule["delete"])){
            $message["perm"] .= "Rule было удалено<br/>";
            $this->delete($for_rule["delete"],"admin_rule","id_rule",$group["id"]);
        }
        
        // Добавляем правила и rule.
        if(!empty($for_perm["insert"])){
            $message["perm"] .= "Permission было добавлено<br/>";
            $this->insert($for_perm["insert"],"admin_permission","id_permission",$group["id"],$status_control);
        }
        if(!empty($for_rule["insert"])){
            $message["perm"] .= "Rule было добавлено<br/>";
            $this->insert($for_rule["insert"],"admin_rule","id_rule",$group["id"],$status_control);
        }
        
        if(isset($message) AND !Arr::emptys($message)){
            $query = array(
                "group" => $group["id"]
            );
            Cookie::set("success",serialize($message));
            Request::redirect(Url::query_root($query,FALSE));
        }
    }

    /**
     * Выводит список группы
     *
     * @return  string
     */
    protected function all_group(){
        $group = Admin_Query::i()->sql("moderator.all_type");
 
        $xml_pars = "admin_template|default::moderator_group_allGroup";
        
        $tech = array(
            "url" => Url::root(FALSE),
            array("plus" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus"))
        );
        
        return $this->xml->preg_load($group,$xml_pars,$xml_pars,$tech);
    }
    
    /////////////////////////////////
    ///// ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ
    ////////////////////////////////
    /**
     * Удаляет permission или rule
     *
     * @param   array   id правил
     * @param   string  таблица с которой будет удалено
     * @param   string  тип (id_permission, id_rule)
     * @param   int     id группы
     * @return  void
     */
    protected function delete($ids, $table ,$type,$id_type){
        $set = $this->sql->insert_string($ids);

        Admin_Query::i()->sql("permission.user.delete",array(
                                                      ":where" => $type,
                                                      ":insert" => $set,
                                                      ":id_admin_type" => $id_type,
                                                      ":table" => $table
                                                    ));
    }
    /**
     * Добавление permission или rule
     *
     * @param   array   id правил
     * @param   string  таблица в которую будем добавлять
     * @param   string  тип (id_permission, id_rule)
     * @param   int     id группы
     * @param   int     статус правила 0 или 1
     * @return  void
     */
    protected function insert($ids, $table ,$type,$id_type,$status_control){
        
        $found = array();
        foreach($ids AS $key => $value){
            $found[$key][] = $id_type;
            $found[$key][] = $value;
            $found[$key][] = $status_control;
        }
        
        $set = $this->sql->insert_string($found);

        Admin_Query::i()->sql("permission.user.insert",array(
                                                      ":table" => $table,
                                                      ":set" => $type,
                                                      ":insert" => $set,
                                                    ));
    }
    /**
     * Создает массив с двумя ячейками delete и insert
     *
     * @param   array   правила из базы
     * @param   array   правила из формы
     * @param   string  permission или rule
     * @return  void
     */
    protected function get_delete_insert($user_perm,$all,$type){
        // Так как ячейка с ID содержит приписку типа, мы просто его добавим.
        $key = "id_".$type;
        
        // Начальный массив для заполнения
        $return = array(
                        "insert"=>array(),
                        "delete"=>array()
                        );
                        
        if(isset($user_perm[$type])){
            // Отбираем все id правил
            $user_perm[$type] = Arr::search($key,$user_perm[$type]);
            
            if(!isset($all[$type])){
                // Все идут на удаление.
                $return["delete"] = Arr::flatten_key($user_perm[$type]);
                if(isset($all[$type]))
                    $return["insert"] =  Arr::flatten_key($all[$type]);
            }else{
                if($insert = Arr::array_diff_assoc($all[$type],$user_perm[$type])){
                   $return["insert"] =  Arr::flatten_key($insert);
                }
                if($delete = Arr::array_diff_assoc($user_perm[$type], $all[$type])){
                    $return["delete"] =  Arr::flatten_key($delete);
                }
            }
            
        }else{
            if(isset($all[$type])){
                // Все идут на добавление.
                $return["insert"] = Arr::flatten_key($all[$type]);
            }
        }
        
        return $return;
    }
    /**
     * Массив для перебора классов из прав, дополняет массив ячейками method и rule
     *
     *     $bar = $session->get_once('bar');
     *
     * @param   array  дополняемый класс
     * @param   array  все права и роли
     * @return  mixed
     */
    protected function permission_pack(&$class,$perm){
        if(isset($class["method"])){
             foreach($class["method"] AS $method_name => &$method){
                 $permission_id = $method["id"];
                 if(isset($perm["rule"][$permission_id])){
                     $method["rule"] = $perm["rule"][$permission_id];
                 }
             }
        }
    }
}