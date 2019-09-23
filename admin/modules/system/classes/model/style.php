<?php defined('MODPATH') OR exit();

/*
 * Модель определяет типы данных и переписывает их
 */
class Model_Style_System_Admin{
    
    //$var array типы стиля
    public $style_type = array('html' => 'html',
            'css' => 'css',
            'php' => 'php',
            'js' => 'javascript',
            'xml' => 'xml',
            'sql' => 'sql',
            'tcl' => 'tcl'
        );
        
    //@var string если типа нет
    public $no_type = NULL;
    
    //@var object системная модель SQL
    public $sql = NULL;
    
    function __construct(){
        $this->sql = Admin_Model::factory('sql','system');
    }
    
    /*
     * Возвращает стили по теме
     *
     * @param  mixed  int если id и string если имя темы, array сложный запрос поле = значение
     * @param  mixed  тип данных как page,post,category. NULL принимается только в массиве
     * @return string путь к теме
     */
    function get_styles($id,$type = NULL,$return = FALSE){
        $join = '';
        $where = '';

        if(is_array($id)){
            $s = array(
                'id',
                'id_template',
                'id_type',
                'name',
                'default'
            );
            
            //Колонки с других таблиц
            $table = array(
                'template'=>array(
                                'prefix'=> 'template',
                                'col_name'=> 'name'
                            ),
            );
            
            if($s = Arr::intersect_key($id,$s)){
                $where .= $this->sql->where('AND','s',$s);
                
            }
            if($table = $this->sql->intersect($id,$table)){
                $where = Str::concat('AND',$where,$this->sql->where('AND',$table));
            }
            if(!empty($where))
                $where = "WHERE ".$where;

        }
        elseif(is_int($id)){
            //Если пришел одиночный id
            $where = "WHERE s.id_template = :id ";
        }else{
            //Если пришел не id ищем по имени темы
            $where = 'WHERE template.name = :id ';
        }
        
        if(!empty($type) AND is_string($type)){
            $where .= "AND t.type = :type ";
        }
        elseif(is_array($type)){
            $fond = '';
            foreach($type AS $value){
                if(!empty($value)){
                    $fond .= "t.type = '{$value}' OR ";
                }elseif($value == NULL){
                    $fond .= "t.type IS NULL OR ";
                }
            }
            if(!empty($fond)){
                $fond = rtrim($fond,'OR ');
                $where .= "AND ({$fond})";
            }
        }
        
        $sql = "SELECT s.id,s.id_template,template.name AS template, t.type, s.name, s.title, s.description, s.path, st.name AS style_type, s.default
            FROM __style s
            LEFT JOIN __type t ON t.id = s.id_type
            INNER JOIN __template ON template.id = s.id_template
            INNER JOIN __style_type st ON st.id = s.style_type
            $where
            ORDER BY s.default DESC,s.id_type, s.style_type, s.id DESC;";

        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);
        $query->param(':type',$type);
        
        $styles = (array)$query->execute('id');
        
        //Берем класс, для создания путей к теме
        $template = Admin_Controller::factory('method','templates');

        foreach($styles AS $key => $style){
            //Создаем путь к теме
            $path = $template->template_path($style['template']);
            
            //Соеденяем с остальным путем
            $dir = (empty($style['path']))? File::concat($path,$style['style_type'],$style['type']) :File::concat($path,$style['path']);

            //Если файла физически нет
            if(!$file = File::exist($dir,$style['name'],$style['style_type'],$return))
                $styles[$key]['no_exist'] = 1;
            elseif($return === TRUE)
                $styles[$key]['path'] = $file;
        }
        
        return $styles;
    }
    
    /*
     * Возвращает стиль по id
     *
     * @param  mixed  id стиля или массив с полями
     * @param  bool   TRUE если path нужно заполнить даже если файла нет
     * @return string путь к теме
     */
    function get_style($id,$path_return = FALSE){

        if(!is_array($id)){
            $where = "WHERE s.id = :id ";
        }else{
            //Корневные колонки
            $s = array(
                'id',
                'id_template',
                'id_type',
                'name'
            );
            //Колонки с других таблиц
            $table = array(
                'style_type'=>array(
                                'prefix'=> 'st',
                                'col_name'=> 'name'
                            ),
                'id_style_type'=>array(
                                'prefix'=> 's',
                                'col_name'=> 'style_type'
                            ),
                'type' => 't',
            );
            
            //Определяем WHERE
            $where = '';
            if($s = Arr::intersect_key($id,$s)){
                $where .= $this->sql->where('AND','s',$s);
            }
            if($table = $this->sql->intersect($id,$table)){
                $where = Str::concat('AND',$where,$this->sql->where('AND',$table));
            }
            if(!empty($where))
                $where = "WHERE ".$where;
        }
        
        $sql = "SELECT s.id, s.id_template, template.name AS template, t.type, s.id_type, s.name, s.title, s.description, s.path, st.name AS style_type, s.default
            FROM __style s
            LEFT JOIN __type t ON t.id = s.id_type
            INNER JOIN __template ON template.id = s.id_template
            INNER JOIN __style_type st ON st.id = s.style_type
            $where
            LIMIT 1;";
            
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);

        if($style = (array)$query->execute(NULL,TRUE)){
            //Берем класс, для создания путей к теме
            $template = Admin_Controller::factory('method','templates');
            
            //Создаем путь к теме
            $path = $template->template_path($style['template']);
            
            //Соеденяем с остальным путем
            $dir = (empty($style['path']))? File::concat($path,$style['style_type'],$style['type']) :File::concat($path,$style['path']);
            
            //Ищем и возвращаем путь к файлу
            $style['path'] = File::exist($dir,$style['name'],$style['style_type'],$path_return);
            
            //Заполняем для редактора
            $style['type_name'] = (isset($this->style_type[$style['style_type']]))? $this->style_type[$style['style_type']] : NULL ;
            
            return $style;
        }else{
            return array();
        }
    }
    
    /*
     * Возвращает использованые стили
     *
     * @param  mixed  id стиля или массив с полями
     * @param  bool   TRUE если нужно вернуть только расширения типа
     * @return string путь к теме
     */
    function style_contents($id, $extends = NULL,$sort = "id"){

        if(!is_array($id)){
            $where = "WHERE sc.id_style = :id ";
        }else{
            //Массив всех нужных таблиц
            $sc = array(
                'id_table',
                'id_style',
                'id_type',
                'status',
                'extends',
            );
            //Колонки с других таблиц
            $table = array(
                'type' => 't',
            );
            
            $where = "";
            if($sc = Arr::intersect_key($id,$sc)){
                $where .= Str::key_value($sc,' AND ','sc');
            }
            if($table = $this->sql->intersect($id,$table)){
                $where = Str::concat('AND',$where,$this->sql->where('AND',$table));
            }
            if(!empty($where))
                $where = "WHERE ".$where;
        }
        
        $sql = "SELECT sc.id,sc.id_table, sc.id_style, sc.id_type, template.name AS template, t.type, s.name, s.title, s.description, s.path, st.name AS style_type, s.default, sc.status, sc.extends
            FROM __style_content sc
            INNER JOIN __style s ON s.id = sc.id_style
            LEFT JOIN __type t ON t.id = sc.id_type
            INNER JOIN __template ON template.id = s.id_template
            INNER JOIN __style_type st ON st.id = s.style_type
            $where;";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);
        
        return (array)$query->execute($sort);
    }
    
    /*
     * Сортирует массив
     *
     * @param  array массив стилей для сортировки
     * @param  bool  использовать ли поле default
     * @param  bool  сортировать ли по style_type
     * @return array сортированые типы
     */
    function style_sort($styles, $default = TRUE,$sore = FALSE){
        if(!empty($styles) AND is_array($styles)){
                foreach($styles AS $key => $style){
                    $type = (!empty($style['default']) AND $default === TRUE)? 'default' : $style['type'];
                    //Если пустой создаем по умолчанию
                    if(empty($style['type'])){
                        $style['type'] = $this->no_type;
                    }
                    /*if(!empty($style['default']) AND $default === TRUE){
                        $styles['default'][$style['style_type']][$key] = $style;
                    }
                    else{
                        $styles[$style['type']][$style['style_type']][$key] = $style;
                    }*/
                    $styles[$type][$style['style_type']][$key] = $style;
                    if($sore === TRUE){
                        ksort($styles[$type]);
                    }
                    unset($styles[$key]);
                }
            
            return $styles;
            
        }else{
            return array();
        }
    }
    
    /*
     * Возвращает все типы данных
     *
     * @return array типы данных
     */
    function style_list(){
        
        $sql = "SELECT id, name, folder
                    FROM __style_type 
                    ORDER BY id;";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        
        return (array)$query->execute('name');
    }
    
    /*
     * Записываем стиль в базу данных
     *
     * @param  array пареметры для вставки колонка => значение
     * @return array типы данных
     */
    function style_set(array $styles){
        
        $insert = Str::key_value($styles);
        $sql = Str::__("INSERT INTO __style SET $insert");

        $query = DB::query(Database::INSERT,  DB::placehold($sql));
        
        return (array)$query->execute();
    }
    /*
     * Записываем стиль в базу данных
     *
     * @param  array пареметры для вставки колонка => значение
     * @param  array таблицы
     * @param  bool  TRUE значит нужно отделить ключи
     * @return array типы данных
     */
    function style_content_set(array $styles,$table = NULL,$revers = FALSE){
        
        //Если нет в какие таблицы сливать. То через SET
        if($table === NULL){
            $insert = Str::key_value($styles);
            $sql = Str::__("INSERT INTO __style SET $insert");
        }else{
            $styles = $this->sql->insert_string($styles);
            //Отделять ли ключи
            if($revers)
                $table = array_keys($table);
            
            $sql = Str::__("INSERT INTO __style_content(:table) VALUES $styles",array(':table'=>implode(',',(array)$table)));

        }

        $query = DB::query(Database::INSERT,  DB::placehold($sql));
        
        return (array)$query->execute();
    }
    /*
     * Обнавляет стиль
     *
     * @param  int   id стиля
     * @param  array пареметры для обновления колонка => значение
     * @return array типы данных
     */
    function style_update($id, array $styles){
        
        $insert = Str::key_value($styles);
        
        $sql = Str::__("UPDATE __style SET $insert WHERE id in(:id)",array(':id'=>implode(',',(array)$id)));
        
        $query = DB::query(Database::UPDATE,  DB::placehold($sql));
        
        return (array)$query->execute();
    }
    /*
     * Обнавляет стили контента
     *
     * @param  array пареметры для обновления колонка => значение
     * @param  array колонка значение для WHERE
     * @param  array список ID стилей если есть. Не обязательное
     * @return array изменены ли поля
     */
    function style_content_update(array $styles,array $table,$id = NULL){
        
        $insert = Str::key_value($styles);
        
        
        $tables = array(
            'id',
            'id_table',
            'id_style',
            'id_type',
            'status',
            'extends',
        );
        $where = '';
        if($table = Arr::intersect_key($table,$tables)){
            $where .= $this->sql->where('AND',$table);
        }

        //Если нужно изменить группу стилей
        if(!empty($id)){
            $str = Str::__("id_style IN(:id)",array(':id'=>implode(',',(array)$id)));
            $where = Str::concat('AND',$where,$str);
        }
        
        if(!empty($where))
            $where = 'WHERE ' . $where;
        else
            throw new Core_Exception('Нет условия WHERE');

        $sql = "UPDATE __style_content SET $insert $where ";
        
        $query = DB::query(Database::UPDATE,  DB::placehold($sql));
        
        return (array)$query->execute();
    }
    
    /*
     * Удаляет запись стиля
     *
     * @param  int id стиля
     * @return array типы данных
     */
    function drop_style($id){
        
        $sql = "DELETE FROM __style WHERE id = :id LIMIT 1";
        
        $query = DB::query(Database::DELETE,  DB::placehold($sql));
        
        $query->param(':id',$id);
        
        if($query->execute()){
            $sql = "DELETE FROM __style_content WHERE id_style = :id";
        
            $query = DB::query(Database::DELETE,  DB::placehold($sql));
            
            $query->param(':id',$id);
            
            return TRUE;
        }
        return FALSE;
    }
    
    /*
     * Удаляет запись с style_content
     *
     * @param  int id стиля
     * @return array типы данных
     */
    function drop_style_content(array $table,$id = NULL){
        
        $tables = array(
            'id',
            'id_table',
            'id_style',
            'id_type',
            'status',
            'extends',
        );
        $where = '';
        if($table = Arr::intersect_key($table,$tables)){
            $where .= $this->sql->where('AND',$table);
        }

        //Если нужно изменить группу стилей
        if(!empty($id)){
            $str = Str::__("id_style IN(:id)",array(':id'=>implode(',',(array)$id)));
            $where = Str::concat('AND',$where,$str);
        }
        
        if(!empty($where))
            $where = 'WHERE ' . $where;
        else
            throw new Core_Exception('Нет условия WHERE');
        
        $sql = "DELETE FROM __style_content $where";

        $query = DB::query(Database::DELETE,  DB::placehold($sql));

        
        return $query->execute();
    }
    
}