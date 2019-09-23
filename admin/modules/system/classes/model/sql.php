<?php defined('MODPATH') OR exit();

/*
 * Модель определяет типы данных и переписывает их
 */
class Model_Sql_System_Admin{
    
    //$var array таблицы для кеша
    protected $table = array(
        'style',
        'style_content',
        'style_type',
        'content_type',
    );
    
    //$var array допустимость статуса
    protected $status = array(
        0,
        1,
    );
    
    /*
     * Заполняет WHERE для SQL запроса
     *
     * sql_where($separator,[$table,array,array...]);
     * 
     * @param  string разделитель как AND или OR
     * @param  string префикс таблицы
     * @param  array множественное значение массивы в виде Array(key=>value)
     * @return string строка для вставки в базу данных 
     */
    function where(){
        $where = "";
        $args = func_get_args();
        //Разделитель
        $separator = array_shift($args);
        //Заданный префикс таблиц
        $set_table = array_shift($args);
        
        if(!is_string($separator))
            throw new Core_Exception('Не пришел разделитель');
        
        //Если нет префикса, возвращаем массив на место.
        if(is_array($set_table)){
            array_unshift($args,$set_table);
            $set_table = '';
        }else{
            $set_table = "{$set_table}.";
        }
            
        foreach($args as $tables){
            $table = $set_table;
            foreach($tables AS $key => $value){

                $equal = ' = ';
                
                //Если массив, значит у него есть личный префикс таблицы
                if(is_array($value)){
                    // Уточняем есть ли указание, что должно быть не равно
                    $equal = isset($value['equal'])? ' <> ' :$equal;
                    
                    if(isset($value['table']))
                        $table = "{$value['table']}.";
                    
                    if(isset($value['value']) OR is_null($value['value'])){
                        $value = $value['value'];
                    }
                }
                if(is_array($value)){
                    //Если значение является массивом, значит перебор IN или NOT IN
                    $equal = ($equal != "=")? 'NOT IN' : 'IN';
                    $value = "(".implode(',',(array)$value).")";
                }
                elseif(!is_null($value)){
                    $value = DB::escape($value);
                }
                else{
                    //Добавляется IS NULL, для корректного поиска в базе данных
                    $value = ' IS NULL';
                    $equal = '';
                }
                $where .= "{$table}{$key}$equal{$value} {$separator}";
            }
        }
        //Уберем лишний разделитель
        $where = rtrim($where,$separator.' ');
        return $where;
    }
    /*
     * Заполняет UPDATE для SQL запроса
     *
     * sql_where($separator,[$table,array,array...]);
     * 
     * @param  string разделитель как AND или OR
     * @param  string префикс таблицы
     * @param  array множественное значение массивы в виде Array(key=>value)
     * @return string строка для вставки в базу данных 
     */
     function update(){
        $where = "";
        $args = func_get_args();
        //Разделитель
        $separator = array_shift($args);
        //Заданный префикс таблиц
        $set_table = array_shift($args);
        
        if(!is_string($separator))
            throw new Core_Exception('Не пришел разделитель');
        
        //Если нет префикса, возвращаем массив на место.
        if(is_array($set_table)){
            array_unshift($args,$set_table);
            $set_table = '';
        }else{
            $set_table = "{$set_table}.";
        }
            
        foreach($args as $tables){
            $table = $set_table;
            foreach($tables AS $key => $value){

                $equal = ' = ';
                
                //Если массив, значит у него есть личный префикс таблицы
                if(is_array($value)){
                    
                    if(isset($value['table']))
                        $table = "{$value['table']}.";
                    
                    if(isset($value['value']) OR is_null($value['value'])){
                        $value = $value['value'];
                    }else{
                        $value['value'] = NULL;
                    }
                }
                if(is_array($value)){
                    throw new Core_Exception('Для UPDATE значение не допускается как массив');
                }
                elseif(!is_null($value)){
                    $value = DB::escape($value);
                }
                else{
                    //Добавляется = NULL, для вставки пустого значения
                    $value = ' = NULL';
                    $equal = '';
                }
                $where .= "{$table}{$key}$equal{$value}{$separator} ";
            }
        }
        //Уберем лишний разделитель
        $where = rtrim($where,$separator.' ');
        return $where;
    }
    /*function update(){
        
        $where = "";
        $args = func_get_args();
        //Разделитель
        $separator = array_shift($args);
        //Заданный префикс таблиц
        $set_table = array_shift($args);
        
        if(!is_string($separator))
            throw new Core_Exception('Не пришел разделитель');
        
        //Если нет префикса, возвращаем массив на место.
        if(is_array($set_table)){
            array_unshift($args,$set_table);
            $set_table = '';
        }else{
            throw new Core_Exception('Пришел не массив. Ключ массива это таблица, ячейка значение для вставки.');
        }
            
        foreach($args as $tables){
            $table = $set_table;
            foreach($tables AS $key => $value){
                
                $equal = '=';
                
                if(!is_null($value)){
                    $value = DB::escape($value);
                }else{
                    //Добавляется IS NULL, для корректного поиска в базе данных
                    $value = '= NULL';
                    $equal = '';
                }
                $where .= "{$table}{$key} $equal {$value} {$separator} ";
            }
        }
        //Уберем лишний разделитель
        $where = rtrim($where,$separator.' ');
        return $where;
    }*/
    
    /**
     * Возвращает массив с префиксами таблицы.
     * Метод используется для составления массива в функцию where();
     * Результат можно вставлять как $this->where('AND',$result_intersect);
     *
     * [!!!] Пример ключей
     *          'col_name' => 'prefix'
     *
     *         Так же ключ может быть сложным, с заменой имени колонки
     *          $keys = array(
     *                'template'=>array(
     *                                'prefix'=> 'template',
     *                                'col_name'=> 'name'
     *                            ),
     *            );
     *
     *      Никто не запрещает делать ключ еще сложнее:
     *          $keys = array(
     *                'style_type'=>array(
     *                                'prefix'=> 'st',
     *                                'col_name'=> 'name'
     *                            ),
       *                'id_style_type'=>array(
     *                                'prefix'=> 's',
     *                                'col_name'=> 'style_type'
     *                            ),
     *                'type' => 't',
     *            );
     *
     *      Работает следующим образом, если мы к выше написанным 
     *      ключам отправим массив:
     *          $array = array("type"=>"test")
     *      То получим следующее:
     *          array(1) {
     *              ["type"]=>
     *                   array(2) {
     *                       ["table"]=>
     *                          string(1) "t"
     *                       ["value"]=>
     *                           string(4) "test"
     *                       }
     *          }
     *      Если мы обратимся к более сложному ключу:
     *          $array = array("style_type"=>"test")
     *      Результат:
     *          array(1) {
     *               ["name"]=>
     *                   array(2) {
     *                       ["table"]=>
     *                           string(2) "st"
     *                       ["value"]=>
     *                           string(4) "test"
     *                   }
     *           }
     *      Имя ключа заменяется на значение с col_name
     *      Можно так же передавать значение с not, что бы where сделать отрицательное значение NOT IN
     *      $table = array(
     *               
     *               'not_type' => array(
     *                               "prefix" => "t",
     *                               "col_name" => "type",
     *                               // Для отрицательного запроса 0 - станет положительным
     *                               "not" => 1,
     *                               )
     *           );
     *
     * @param   array  сверяемый массив
     * @param   array  массив ключей
     * @return  array  массив искомых значений
     * @return  bool   FALSE если неудача
     */
    function intersect(array $array,array $keys){
        $fonds = array();
        
        //Берем таблицы 
        $values = array_intersect_key($array,$keys);
        
        if(!empty($values)){
            //Берем все ключи, по ним и будет заполнять
            $fonds = array_intersect_key($keys,$array);
            
            foreach($values AS $key => $value){
                //Если это массив, значит сложный ключ
                if(is_array($fonds[$key])){
                    
                    //Заменяем префикс таблицы
                    if(isset($fonds[$key]['prefix'])){
                    //Для замены имени колонки
                        $table = $fonds[$key]['prefix'];
                    }
                    //Если нужен NOT
                    if(isset($fonds[$key]['not']) AND !empty($fonds[$key]['not'])){
                        $equal = TRUE;
                    }
                    //Заменяем имя таблицы
                    if(isset($fonds[$key]['col_name'])){
                        $new_key = $fonds[$key]['col_name'];
                        //Удаляем для замены
                        unset($fonds[$key]);
                        $key = $new_key;
                    }
                    
                }else{
                    $table = $fonds[$key];
                }
                
                //Заполняем значения для вывода
                $fonds[$key] = array();
                if(isset($table))
                    $fonds[$key]['table'] = $table;
                $fonds[$key]['value'] = $value;
                if(isset($equal)){
                    unset($equal);
                    $fonds[$key]['equal'] = TRUE;
                }
            }
            
            return $fonds;
        }
        return FALSE;
    }
    
    /**
     * Возвращает массив только если все поля совпали.
     *
     *
     * @param   array  сверяемый массив
     * @param   array  массив ключей
     * @param   bool   TRUE по ключам, FALSE по содержимому $keys
     * @return  bool   FALSE если неудача
     */
    function intersect_match(array $array,array $keys,$intersect = FALSE){
        $fonds = array();

        if($intersect === FALSE){
            $keys = array_flip($keys);
        }
        
        $fonds = array_intersect_key($array,$keys);

        if(!empty($fonds) AND !array_diff_key($keys,$fonds)){
            return $fonds;
        }
        return FALSE;
    }
    
    /*
     * Показывает были ли изменены таблицы.
     * Записывает статус таблицы - изменена или нет, если есть второй параметр.
     *
     * @param  string имя таблицы
     * @param  int    статус, 1 изменение было, 0 не было.
     * @return bool   TRUE если изменение было, FALSE если не было
     */
    function cache($table, $status = NULL){
        
        //Возвращает boolean по статусу изменения.
        $fond = FALSE;
        
        //Проверяем допустимость значения.
        if(!in_array($table,$this->table))
            throw new Core_Exception("Не верное имя таблицы <b>:table</b>",array(':table'=>$table));
        
        if($status !== NULL){
            $status = (int)$status;
            //Проверяем допустимость статуса.
            if(!in_array($status,$this->status))
                throw new Core_Exception("Не верный статус <b>:status</b> статус может быть либо 1 либо 0",array(':status'=>$status));
            
            $sql = "UPDATE __cache SET status = :status WHERE table_name = :table";
            
            $query = DB::query(Database::UPDATE,  DB::placehold($sql));
            $query->param(':status',$status);
            $query->param(':table',$table);
            
            $cache = $query->execute();
            
            $fond = (bool)$cache;
        }else{
            $sql = "SELECT status FROM __cache WHERE table_name = :table";
            
            $query = DB::query(Database::SELECT,  DB::placehold($sql));
            $query->param(':table',$table);
            
            $cache = $query->execute(NULL,TRUE);
            
            //Избовляемся от имени таблицы
            $fond = (bool)reset($cache);
        }
        
        return $fond;
    }
    
    /*
     * Формирует строку для множественного INSERT (value,"value",NULL)
     *
     * @param  array   массив значений для вставки
     * @return string  строка для INSERT
     */
    function insert_string(array $values){
        $insert = '';

        $fond = '';
        foreach($values AS $value){
            if(is_array($value)){
                $insert .= $this->insert_string($value) . ', ';
            }else{
                if(is_null($value)){
                    $fond .= 'NULL,';
                }else{
                    $fond .= "'{$value}',";
                }
            }
        }
        
        if(!empty($fond)){
            $insert .= "(".rtrim($fond,', ').")";
        }
        
        return rtrim($insert,', ');
    }
}