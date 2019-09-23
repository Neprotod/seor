<?php defined('MODPATH') OR exit();

class Model_Mysql_Backup_Socket{
    
    private $table;
    
    public $db_name;
    
    function __construct(){
        // Узнаем имя базы данных
        $config = Core::config('database')->default['connection'];
        $db_name = explode(';',$config['dsn']);
        $db_name = array_pop($db_name);
        
        $db_name = explode('=',$db_name);
        $db_name = end($db_name);
        if(!empty($db_name))
            $this->db_name = $db_name;
    }
    
    function fetch(){
        if(isset($_POST['all'])){
            $this->create_file($this->show_table());
            exit();
        }
        // Таблици через запятую
        elseif(isset($_POST['selected']) AND $selected = Request::post('table')){
            $selected = explode(',',$selected);
            $tables = $this->show_table();
            $fonds = array();
            foreach($tables as $key => $table){
                foreach($selected as $select){
                    if($table['table_name'] == $select){
                        array_push($fonds,$tables[$key]);
                    }
                }
            }
            $this->create_file($fonds,$fonds);
            exit();
        }
        // Удаление всех таблиц
        elseif(isset($_POST['drop']) AND isset($_POST['drop_all'])){
            $tables = $this->show_table();
            $fonds = array();
            foreach($tables as $key => $table){
                $fonds[] = $table['table_name'];
            }
            $this->drop_tables($fonds);
            unset($this->table);
            Registry::i()->massage = 'Все таблицы удалены';
        }
        if(!empty($_POST['_FILES'])){
            $this->backup();
            Registry::i()->massage = 'Успешно восстановлено';
        }
        $result = $this->show_table();
        echo View::factory('mysql_backup_fetch','socket',array('show_table'=>$result,'database'=>$this->db_name));
    }
    private function backup(){
        $sql = reset($_POST['_FILES']['file']);
        $query = DB::query(Database::INSERT, $sql);
        
        $query->execute();
    }
    function show_table($bool = FALSE){
        if(!empty($this->table) AND $bool === FALSE)
            return $this->table;
        
        $sql = "SELECT table_name, table_comment
            FROM information_schema.tables
            WHERE table_schema = '{$this->db_name}'";
        $sql = DB::placehold($sql);
        
        //$sql = "SHOW TABLES";
        $query = DB::query(Database::SELECT, $sql);
        
        $this->table = $query->execute();
        
        
        return $this->table;
    }
    
    private function get_table_information($table){
        $sql = "SELECT TABLE_NAME, COLUMN_NAME,DATA_TYPE
                    FROM information_schema.columns
                    WHERE table_schema = '{$this->db_name}'
                    AND table_name = '{$table}'";/**/
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        $tables_info = $query->execute();
        $table_info_return = array();
        foreach($tables_info as $tables){
            $table_info_return[$tables['TABLE_NAME']]['COLUMN'][] = array(
                'name'=>$tables['COLUMN_NAME'],
                'type'=>$tables['DATA_TYPE'],
            );
        }
        $sql = "SHOW CREATE TABLE `{$table}`";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        $tables_create = reset($query->execute());
        $tables_create = str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$tables_create['Create Table']);
        
        $table_info_return[$table]['table_shema'] = $tables_create;
        
        
        return $table_info_return;
    }
    
    private function get_insert($table){
        $sql = "SELECT *
                    FROM {$table}";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        return $query->execute();
    }
    
    private function get_trigger($tables = array()){
        if(empty($tables)){
            $sql = "SHOW TRIGGERS;";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::SELECT, $sql);
            return $query->execute();
        }
        $all_trigers = array();
        foreach($tables as $table){
            $sql = "SHOW TRIGGERS FROM `{$this->db_name}` LIKE '{$table['table_name']}'";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::SELECT, $sql);
            $result = $query->execute();
            if(!empty($result))
                $all_trigers = Arr::merge($all_trigers,$result);
        }
        return $all_trigers;
    }
    
    function create_file($show_table,$trigger = array()){
        $sql_return = '';
        $first = TRUE;
        foreach($show_table as $show){
            $tables = $this->get_table_information($show['table_name']);
            
            $key_for_insert = array();
            foreach($tables as $name => $table){
                if($first == FALSE){
                    $sql_return .= "\r\n";
                    $sql_return .= "\r\n";
                }
                $first = FALSE;
                $sql_return .= "--\r\n";
                $sql_return .= "-- Структура таблицы `{$name}`\r\n";
                $sql_return .= "--\r\n";
                $sql_return .= "\r\n";
                
                $sql_return .= $table['table_shema'].';';
                $key_for_insert = $table['COLUMN'];
                
            }
            
            if($inserts = $this->get_insert($show['table_name'])){
                $sql_return .= "\r\n";
                $sql_return .= "\r\n";
                $sql_return .= "--\r\n";
                $sql_return .= "-- Дамп данных таблицы `{$name}`\r\n";
                $sql_return .= "--\r\n";
                $sql_return .= "\r\n";
                
                // Создаем строку для запроса
                $insert_query = $this->get_insert_query($key_for_insert);
                
                $sql_return .= "INSERT INTO `{$name}` ({$insert_query}) VALUES\r\n";
                
                $insert_value = '';
                foreach($inserts as $nums => $insert){
                    if(empty($inserts[$nums+1])){
                        $delimiter = ';';
                    }else{
                        $delimiter = ',';
                    }
                    $insert_value .= "\t(";
                    foreach($key_for_insert as $num => $key){
                        if(!empty($key_for_insert[$num+1]))
                            $char = ',';
                        else
                            $char = '';
                        
                        if($insert[$key['name']] === NULL){
                            $insert_value .= "NULL{$char}";
                        }
                        elseif($key['type'] == 'int' OR $key['type'] == 'float'){
                            if($insert[$key['name']] === '')
                                $insert[$key['name']] = "''";
                            $insert_value .= "{$insert[$key['name']]}{$char}";
                        }else{
                            $insert[$key['name']] = Db::escape($insert[$key['name']]);
                            $insert_value .= "{$insert[$key['name']]}{$char}";
                        }
                    }
                        $insert_value .= "){$delimiter}\r\n";
                }
                $sql_return .= $insert_value;
                unset($inserts,$insert_value);
            }
        }
        if($triggers = $this->get_trigger($trigger)){
            
            foreach($triggers as $trigger){
                $sql_return .= "\r\n";
                $sql_return .= "\r\n";
                $sql_return .= "--\r\n";
                $sql_return .= "-- Триггер `{$trigger['Trigger']}`\r\n";
                $sql_return .= "--\r\n";
                $sql_return .= "\r\n";
                
                $sql_return .= "DROP TRIGGER IF EXISTS `{$trigger['Trigger']}`;\r\n";
                $sql_return .= "DELIMITER |\r\n";
                $sql_return .= "CREATE TRIGGER `{$trigger['Trigger']}` {$trigger['Timing']} {$trigger['Event']} ON `{$trigger['Table']}`\r\n";
                $sql_return .= "\tFOR EACH ROW\r\n";
                $sql_return .= "\tBEGIN\r\n";
                $sql_return .= "\t\t{$trigger['Statement']}\r\n";
                $sql_return .= "\tEND|\r\n";
                $sql_return .= "DELIMITER ;\r\n";
            }
        }
        echo $sql_return;
    }
    
    private function get_insert_query($key_for_insert){
        foreach($key_for_insert as $key){
            $insert_query .= "`{$key['name']}`,";
        }
        return rtrim($insert_query,',');
    }
    
    function drop_tables($tables){
        foreach((array)$tables as $table){
            $sql = "DROP TABLE IF EXISTS $table;";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
            $query->execute();
        }
    }
}