<?php defined('MODPATH') OR exit();


class Model_Url_System_Admin{
    
    function __construct(){
        $this->sql = Admin_Model::factory('sql','system');
    }
    
    /*
     * Выдает URL
     *
     * @param  string  url адрес
     * @return array   массив содержащий адрес
     * @return null    NULL если ничего нет
     */
    function get($search){
        
        $tables = array(
            'id',
            'url',
            'id_type',
            'id_table',
            'id_canonical',
        );
        
        $where = '';
        if($table = Arr::intersect_key($search,$tables)){
            $where = Str::concat('AND',$this->sql->where('AND','u',$table),$where);
        }else{
            throw new Core_Exception('Нет условия WHERE');
        }
        
        $sql = "SELECT u.url, t.type, u.id_table,(SELECT c.url FROM __url c WHERE c.id = u.id_canonical ) AS canonical
                    FROM __url u
                    INNER JOIN __type t ON u.id_type = t.id
                    WHERE $where LIMIT 1;";

        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        return $query->execute();
    }
    
    /*
     * Обновляет URL
     *
     * @return string  адрес
     * @param  array   массив значений для поиска
     * @return array   измененные поля
     */
    function update($url,array $search){
        $tables = array(
            'id',
            'url',
            'id_type',
            'id_table',
            'id_canonical',
        );
        
        $select = array(
            'type'
        );
        
        $where = '';
        
        if($table = Arr::intersect_key($search,$select)){
            $where = "id_type = (SELECT id FROM __type where type = '{$table['type']}')";
        }
        
        if($table = Arr::intersect_key($search,$tables)){
            $where = Str::concat('AND',$this->sql->where('AND',$table),$where);
        }else{
            throw new Core_Exception('Нет условия WHERE');
        }

        $sql = "UPDATE __url SET url = :url WHERE $where LIMIT 1;";
    
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->param(':url',$url,TRUE);
        
        return (bool)$query->execute();
    }
    
    /*
     * Обновляет URL
     *
     * @return string  адрес
     * @param  array   массив значений для поиска
     * @return array   измененные поля
     */
    function insert(array $insert){
        
        $important = array(
            'url',
            'id_type',
            'id_table',
        );
        
        $tables = array(
            'url',
            'id_type',
            'id_table',
            'id_canonical',
        );
        
        $select = array(
            'type'
        );
        
        $to_insert = '';
        if($table = Arr::intersect_key($insert,$select)){
            $type = Admin_Model::factory('type','system');
            
            if(!$type = $type->type_list($table['type'])){
                throw new Core_Exception('Нет такого типа данных как <b>:type</b>',array(':type'=>$table['type']));
            }
            $insert['id_type'] = $type['id'];
        }
        

        if($table = Arr::intersect_key($insert,$tables) AND Arr::intersect_match($insert,$important)){
            $to_insert = Str::key_value($table);
        }else{
            throw new Core_Exception('Не пришли поля для добавления');
        }
        
        if($url = $this->get(array('url'=>$insert['url']))){
            throw new Core_Exception('URL <b>:url</b> уже есть в базе',array(':url'=>$insert['url']));
        }
        
        
        $sql = "INSERT __url SET $to_insert;";
    
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        $query->param(':url',$url,TRUE);
        
        return $query->execute();
    }
}