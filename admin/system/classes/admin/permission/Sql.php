<?php defined('SYSPATH') OR exit();
/**
 * Класс обработки sql permission
 * 
 * @package    Tree
 * @category   Core
 */
class Admin_Permission_Sql{
    
    /**
     * @var array результат запроса на получение типов класса
     */
    public $type = NULL;
    
    public $permission = array(
            "module" => array(),
            "class" => array(),
            "permission" => array(),
            "id_permission" => array(),
            "rule" => array(),
    );
    
    /**
     * @var object модуль Admin_Model::factory("sql","system")
     */
    public $sql;
    
    /**
     * Загружает типы данных и модуль для формирования запросов
     *
     * @return void
     */
    function __construct(){
        $this->sql = Admin_Model::factory("sql","system");
        $this->type = Admin_Query::i()->sql("permission.mcm.type",NULL,"type");
    }
    
    /**
     * Сверяет массив с правилами в таблице.
     *
     * @param  array  модуль с классами, правами и уточнениями генерируемых классом Admin_Permission_Xml
     * @return void
     */
    function get_permission(){
        if($class_sql = Admin_Query::i()->sql("permission.mcm.all",NULL,"id")){
            $module_sql = Arr::search(array("type"=>"module"),$class_sql);
            //$module_sql_name = Arr::value_key($module_sql, "class_name");
            
            $this->permission["module"] = &$module_sql;
            $this->permission["class"] = &$class_sql;
            
            
            $permission = Admin_Query::i()->sql("permission.perm.all",NULL,"id");
            
            $this->permission["id_permission"] = $permission;
            
            $permission = $this->pack($permission,"id_class","method");
            
            $this->permission["permission"] = $permission;
            
            $this->permission["rule"] = $this->pack_rule("all");
            
            foreach($class_sql AS $key => $value){
                
                $found = array();
                if(empty($value["id_class"])){
                    $id = $value["id"];
                    $found = &$module_sql[$id];
                }else{
                    $id = $value["id"];
                    $module_sql[$value["id_class"]][$value["type"]][$value["class_name"]] = &$found;
                    $found = $value;
                }
                
               
                
                if(isset($permission[$id])){
                    $found["method"] = $permission[$id];
                }
                
                unset($found);
            }
            $module_sql = Arr::value_key($module_sql, "class_name");
            unset($module_sql,$permission);
            return $this->permission;
        }else{
            return array();
        }
    }
    
    /**
     * Сверяет массив с правилами в таблице.
     *
     * @param  array  модуль с классами, правами и уточнениями генерируемых классом Admin_Permission_Xml
     * @return void
     */
    function set_up($array){

        $class = $array["class"];

        // Сортируем модули отдельно
        $module = Arr::search(array("type"=>"module"),$class);
        
        // Заменяет ключи значением, нам нужны имена ячеек как имя модуля
        $module_name = Arr::value_key($module, "class_name");
        
    
        // Удаляем модули из списка
        $class = array_diff_key($class, $module);
        
        // Берем все классы из базы
        $class_sql = Admin_Query::i()->sql("permission.mcm.all",NULL,"id");
        
        $module_sql = Arr::search(array("type"=>"module"),$class_sql);
        $module_sql_name = Arr::value_key($module_sql, "class_name");
       
        $class_sql = array_diff_key($class_sql, $module_sql);
        
        // Находим расхождения добавляем или обновляем таблицы
        foreach($module_name AS $name => $value){
           
            if(isset($module_sql_name[$name])){
                
                $test = $module_sql_name[$name];
                // Удаляем, все не удаленные модули будут удалены из таблицы
                unset($module_sql_name[$name]);
                if($test["description"] != $value["description"]){
                    exit;
                    $this->update_mcm(
                                        array("description" => $value["description"]),
                                        $test["id"]
                                      );
                    
                }
            }else{
                
                $this->insert_class($value);
                unset($module_sql_name[$name]);
            }
        }

        // Удаляем если остались не подключенные модули
        if($ids = $this->drop_all($module_sql_name,"mcm")){
            $this->delete_mcm($ids,"id_class");
        }
        
        // Повторно заполняем все модули
        $module_sql_name = $this->select_module(array("type"=>"module"),"class_name");
        
        ///////////////////////////////////////////////////////////////////////
        //ALL CLASS
        ///////////////////////////////////////
        if(!empty($class)){

            foreach($class as $key => $value){
                $mod_name = $module[$value["id_class"]]["class_name"];
                $sql_mod = $module_sql_name[$mod_name];
                $name = $class[$key]["class_name"];
                $id = $sql_mod["id"];
                $class[$key]["id_class"] = $id;
                $value["id_class"] = $id;
                
                // Если есть соответствия в массиве из базы
                if($test = Arr::search(array("type"=>$value["type"],"id_class"=>$id,"class_name"=>$name),$class_sql)){
                    // Удаляем, все не удаленные классы будут удалены из таблицы
                    unset($class_sql[key($test)]);
                    $test = current($test);
                    if($test["description"] != $value["description"]){
                        $this->update_mcm(
                                        array("description" => $value["description"]),
                                        $test["id"]
                                      );
                    }
                }else{
                    $this->insert_class($value);
                }
            }
        }
        
        // Удаляем контроллеры и модели
        $this->drop_all($class_sql,"mcm");
        
        $class_sql = Admin_Query::i()->sql("permission.mcm.all",NULL,"id");
        $class = $array["class"];
        ///////////////////////////////////////////////////////////////////////
        //PERMISSION
        ///////////////////////////////////////
        // Сортируем модули отдельно
        $permission_sql =  Admin_Query::i()->sql("permission.perm.all",NULL,"id");
        
        if($permission = $array["permission"]){
            foreach($permission as $key => $value){
                // Формирует нужный массив для проверки правил
                $perem = array(
                    "value" => $value,
                    "permission_sql" => $permission_sql,
                    "class" => $class,
                    "module" => $module,
                    "module_sql_name" => $module_sql_name,
                    "class_sql" => $class_sql
                );
                $return = $this->sql_permission($perem);
                
                // Модуль из базы
                $sql_mod = $return["sql_mod"];
                
                // Правило из базы
                $test = $return["test"];

                $value["id_class"] = $sql_mod["id"];
                
                if($test){
                    // Удаляем правило, все не удаленные правила будут удалены из таблицы
                    unset($permission_sql[$test["id"]]);

                    if(isset($value["description"]) AND $test["description"] != $value["description"]){
                        $this->update_permission(
                                    array("description" => $value["description"]),
                                    $test["id"]);
                    }
                }else{
                    $this->insert_permission($value);
                }
            }
        }
        // Удаляем правила
        $this->drop_all($permission_sql,"permission");
            
        $permission_sql =  Admin_Query::i()->sql("permission.perm.all",NULL,"id");
        ///////////////////////////////////////////////////////////////////////
        //RULE
        ///////////////////////////////////////
        // Пакует запрос в нужном виде.
        $rule_sql = $this->pack_rule();
        
        if($rule = $array["rule"]){
            foreach($rule as $key => $value){
                $perm = $permission[$key];
                
                // Формирует нужный массив для проверки правил
                $perem = array(
                    "value" => $perm,
                    "permission" => $permission,
                    "permission_sql" => $permission_sql,
                    "class" => $class,
                    "module" => $module,
                    "module_sql_name" => $module_sql_name,
                    "class_sql" => $class_sql
                );
                
                $return = $this->sql_permission($perem);
                $test = $return["test"];
                
                $id = $test["id"];
                
                // Проверяет и удаляет роли которые уже существуют
                $this->rout_rule($rule_sql,$value,$id);
            }
        }
        // Удаляем роли
        $rule_sql = $this->drop_rule($rule_sql,"sql");
        ///////////////////////////////////////////////////////////////////////
        //SQL
        ///////////////////////////////////////
        // Пакует запрос в нужном виде.
        $sql_sql = $this->pack_rule("sql");
        
        if($sql = $array["sql"]){
            foreach($sql as $key => $value){
                $perm = $permission[$key];
                // Формирует нужный массив для проверки правил
                
                $perem = array(
                    "value" => $perm,
                    "permission" => $permission,
                    "permission_sql" => $permission_sql,
                    "class" => $class,
                    "module" => $module,
                    "module_sql_name" => $module_sql_name,
                    "class_sql" => $class_sql
                );
                
                $return = $this->sql_permission($perem);
                $test = $return["test"];
                $id = $test["id"];
                
                foreach($value AS $name => $val){
                    // Берем путь к запросу и создаем запрос. В пути запроса таблицы должны обязательно возвращать колонку с именем role и description по желанию
                    if($sql_path = Admin_Query::i()->sql($val["path"],NULL)){
                        $this->rout_rule($sql_sql,$sql_path,$id,1);
                    }
                    
                }
            }
        }
        // Удаляем rule
        $sql_sql = $this->drop_rule($sql_sql,"sql");
    }
    //////////////////////////////////////////////
    //////// СОКРАЩЕНИЕ КОДА
    /////////////////////////////////////////////
    /**
     * Определяет что делать с ролями, обновлять или добавлять
     *
     * @param  array  массив ролей из базы
     * @param  array  текущая роль
     * @param  int    id для получения роли из базы
     * @param  int    тип, если 0 будет сохранять их как полноценные роли 1 как результат запроса добавляя 1 в поле sql_stat 
     * @return void   
     */
    protected function rout_rule(&$rule_sql,$value,$id,$type = 0){
        if(isset($rule_sql[$id])){
            $get = $rule_sql[$id];
            foreach($value AS $val){
                $val['id_permission'] = $id;
                $rul = $val["rule"];
                if(isset($get[$rul])){
                    // Удаляем правило, все не удаленные правила будут удалены из таблицы
                    unset($rule_sql[$id][$rul]);
                    if($get[$rul]["description"] != $val["description"])
                            $this->update_rule(
                                array("description" => $val["description"]),
                                $get[$rul]["id"]);
                }else{
                    $this->insert_rule($val,$type);
                }
            }
            if(empty($rule_sql[$id])){
                unset($rule_sql[$id]);
            }
        }else{
            foreach($value AS $rul => $val){
                $val['id_permission'] = $id;
                $this->insert_rule($val,$type);
            }
        }
    }
    
    /**
     * Достает id с массива и передает их на удаление из базы
     *
     * @param  array  массив классов или правил для удаления
     * @param  type   тип удаляемых элементов
     * @return mixed  возвращает удаленные ID либо FALSE   
     */
    protected function drop_all($drop,$type){
        // Удаляем правила
        if(!empty($drop)){
            $ids = Arr::path($drop,"*.id");
            $ids = $this->sql->insert_string($ids);
            $action = "delete_{$type}";
            $this->$action($ids,"id");
            
            return $ids;
        }
        
        return FALSE;
        
    }
    
    /**
     * Находит правило в массивах из базы
     *
     * @param  array  массив классов или правил для удаления
     * @param  type   тип удаляемых элементов
     * @return mixed  возвращает удаленные ID либо FALSE   
     */
    protected function sql_permission($perem){
        
        // Необходимые ячейки
        $find = array("value","permission_sql","class","class_sql","module","module_sql_name");
        
        $ft = array_keys(array_flip($find));
        $pt = array_keys($perem);
        if($er = array_diff($ft, $pt)){
            throw new Core_Exception("Не пришла ячейка <b>:find</b>",array(":find" => implode(" | ", $er)));
        }
        
        extract($perem);
                
        $mod_class = $class[$value["id_class"]];
        
        // Определяем тип, если модуль то по имени, если нет, нужен более глубокий поиск
        if($mod_class["type"] == "module"){
            $sql_mod = $module_sql_name[$mod_class["class_name"]];
        }else{
            $id = $module[$mod_class["id_class"]]["class_name"];
            $id = $module_sql_name[$id]["id"];
            
            if(!$sql_mod = Arr::search(array("type"=>$mod_class["type"],"id_class"=>$id,"class_name"=>$mod_class["class_name"]),$class_sql))
                throw new Core_Exception("Нет класса, продолжение выполнения не имеет смысла. Проверьте базу данных, нужен класс <b>:name</b> с типом <b>:type</b> принадлежащий к модулю <b>:mod</b>",array(
                                   ":name" => $mod_class["class_name"],
                                   ":type" => $mod_class["type"],
                                   ":mod" => $class[$mod_class["id_class"]]["class_name"]
                                   ));
            $sql_mod = current($sql_mod);
        }
        
        
        
        $test = array();
        if($test = Arr::search(array("method"=>$value["method"],"id_class"=>$sql_mod["id"]), $permission_sql))
            $test = current($test);

        $return = array(
            "sql_mod" => $sql_mod,
            "test" => $test
            );
        
        return $return;
    }
    
    /**
     * Пакует роли в нужном формате
     *
     * @param  type   путь к запросу обычно это rule или sql
     * @return mixed  возвращает удаленные ID либо FALSE   
     */
    protected function pack_rule($type = "rule"){
        $return = Admin_Query::i()->sql("permission.rule.".$type,NULL);

        return $this->pack($return, "id_permission", "rule");
    }
    /**
     * Достает id с массива и передает их на удаление из базы
     *
     * @param  array  массив ролей для удаления
     * @param  type   это значение метода $this->pack_rule($type)
     * @return array  возвращает запакованные роли   
     */
    protected function drop_rule($drop,$type = "all"){
        if(!empty($drop)){
            $ids = Arr::path($drop,"*.*.id");
            $ids = Arr::flatten($ids);
            $ids = $this->sql->insert_string($ids);
            $this->delete_rule($ids,"id");
        }

        return $this->pack_rule($type);
    }
    //////////////////////////////////////////////
    //////// MCM PROTECTED
    /////////////////////////////////////////////
    /**
     * Берет классы из базы
     *
     * @param  array   массив ролей для удаления
     * @param  string  как сортировать
     * @return array   возвращает запакованные роли   
     */
    protected function select_module(array $keys,$sort = NULL){
        $table = array(
            'type' => 't',
            // Для отрицательного запроса
            'not_type' => array(
                            "prefix" => "t",
                            "col_name" => "type",
                            "not" => 1,
                            ),
            'id' => 'm'
        );

        if(!$set = $this->sql->intersect($keys,$table)){
            throw new Core_Exception("Скорее всего не правильно оформлен массив, допустимые значения ячеек массива <b>:arr</b>, а пришло значение <b>:key</b>",
                                            array(
                                                ":arr"=> implode("|",$table),
                                                ":key"=> implode("|",$keys)
                                                ));
        }
        
        $set = $this->sql->where("AND",$set);
        return Admin_Query::i()->sql("permission.mcm.select",array(
                                                     ":set" => $set
                                                    ),$sort);
    }
    
    /**
     * Вставляет новый класс в базу
     *
     * @param  array   один класс
     * @return array      
     */
    protected function insert_class($module){
        if(!isset($module['description'])){
            $module['description'] = NULL;
        }
        return Admin_Query::i()->sql("permission.mcm.insert",array(
                                                ":description" => $module['description'],
                                                ":class_name" => $module['class_name'],
                                                ":id_type" => $this->type[$module['type']]["id"],
                                                ":id_class" => $module['id_class'],
                                                ":admin" => 1,
                                                ));
    }
    
    /**
     * Обновляет класс
     *
     * @param  array   поля для обновления
     * @param  array   id в базе
     * @return array   
     */
    protected function update_mcm(array $update,$key){
        if(empty($update)){
            return FALSE;
        }
        
        $set = $this->sql->update(",",$update);
        
        return Admin_Query::i()->sql("update",array(
                                                    ":set" => $set,
                                                    ":id" => $key,
                                                    ":table" => "mcm"
                                                    ));
    }
    
    /**
     * Удаляет класс
     *
     * @param  string  id
     * @param  array   таблица
     * @return array   
     */
    protected function delete_mcm($keys,$table){
        return Admin_Query::i()->sql("delete",array(
                                                    ":insert" => $keys,
                                                    ":where" => $table,
                                                    ":table" => "mcm",
                                                    ));
    }
    //////////////////////////////////////////////
    //////// PERMISSSION PROTECTED
    /////////////////////////////////////////////
    /**
     * Вставляет новое правило
     *
     * @param  array   один класс
     * @return array      
     */
    protected function insert_permission($module){
        if(!isset($module['description'])){
            $module['description'] = NULL;
        }
        return Admin_Query::i()->sql("permission.perm.insert",array(
                                                ":description" => $module['description'],
                                                ":method" => $module['method'],
                                                ":id_class" => $module['id_class']
                                                ));
    }
    /**
     * Обновляет правило
     *
     * @param  array   поля для обновления
     * @param  array   id в базе
     * @return array   
     */
    protected function update_permission(array $update,$key){
        if(empty($update)){
            return FALSE;
        }
        
        $set = $this->sql->update(",",$update);
        
        return Admin_Query::i()->sql("update",array(
                                                    ":set" => $set,
                                                    ":id" => $key,
                                                    ":table" => "permission"
                                                    ));
    }
    /**
     * Удаляет правило
     *
     * @param  string  id
     * @param  array   таблица
     * @return array   
     */
    protected function delete_permission($keys,$table){
        return Admin_Query::i()->sql("delete",array(
                                                    ":insert" => $keys,
                                                    ":where" => $table,
                                                    ":table" => "permission",
                                                    ));
    }
     //////////////////////////////////////////////
    //////// RULE PROTECTED
    /////////////////////////////////////////////
    /**
     * Вставляет новую роль
     *
     * @param  array   один класс
     * @return array      
     */
    protected function insert_rule($module,$sql = 0){
        if(!isset($module['description'])){
            $module['description'] = NULL;
        }
        return Admin_Query::i()->sql("permission.rule.insert",array(
                                                ":description" => 
                                                        empty($module['description'])
                                                                ? NULL : 
                                                                $module['description'],
                                                ":rule" => $module['rule'],
                                                ":id_permission" => $module['id_permission'],
                                                ":sql_stat" => $sql,
                                                ));
    }
    /**
     * Обновляет роль
     *
     * @param  array   поля для обновления
     * @param  array   id в базе
     * @return array   
     */
    protected function update_rule(array $update,$key){
        if(empty($update)){
            return FALSE;
        }
        
        $set = $this->sql->where(",",$update);
        
        return Admin_Query::i()->sql("update",array(
                                                    ":set" => $set,
                                                    ":id" => $key,
                                                    ":table" => "rule"
                                                    ));
    }
    /**
     * Удаляет роль
     *
     * @param  string  id
     * @param  array   таблица
     * @return array   
     */
    protected function delete_rule($keys,$table){
        return Admin_Query::i()->sql("delete",array(
                                                    ":insert" => $keys,
                                                    ":where" => $table,
                                                    ":table" => "rule",
                                                    ));
    }
    //////////////////////////////////
    //// ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ
    //////////////////////////////////
    
    /**
     * Пакует массив по заданных ключам
     *
     * @param  type   путь к запросу обычно это rule или sql
     * @return mixed  возвращает удаленные ID либо FALSE   
     */
    public function pack($return, $first, $second){
        $found = array(); 
        
        if(!empty($return)){
            foreach($return AS $value){
                $found[$value[$first]][$value[$second]] = $value;
            }
        }
        return $found;
    }
}