<?php defined('MODPATH') OR exit();

/*
 * Модель определяет типы данных и переписывает их
 */
class Model_Type_System_Admin{
    
    //@var string основная директория типов данных
    public $content = "content";
    
    //@var string если типа нет
    public $no_type = NULL;
    
    //$var array типы
    public $type = array('html' => 'html',
            'css' => 'css',
            'php' => 'php',
            'js' => 'javascript',
            'xml' => 'xml',
            'sql' => 'sql',
            'tcl' => 'tcl'
        );
    
    function __construct(){
        $this->sql = Admin_Model::factory('sql','system');
    }
    
    /*
     * Возвращает типы данных по теме
     *
     * @param  mixed  int если id и string если имя темы 
     * @param  string тип данных как page,post,category
     * @return string путь к теме
     */
    function get_types($id,$type = NULL){
        $join = '';
        $where = '';
        
        //Если пришел не id ищем по имени темы
        if(is_int($id)){
            $where = "WHERE ct.id_template = :id";
        }
        else{
            $where = 'WHERE template.name = :id ';
        }
        
        if(!empty($type)){
            $where .= 'AND t.type = :type ';
        }
        
        $sql = "SELECT ct.id, t.type, ct.name, ct.ext, ct.title, ct.description, ct.path,template.name AS template
                    FROM __content_type ct
                    LEFT JOIN __type t ON t.id = ct.id_type
                    INNER JOIN __template ON template.id = ct.id_template
                    $where
                    ORDER BY ct.id_type, ct.id;";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);
        $query->param(':type',$type);
        
        $types = (array)$query->execute('id');
        
        //Берем класс, для создания путей к теме
        $template = Admin_Controller::factory('method','templates');
        
        foreach($types as $key => $type){
            //Создаем путь к теме
            $path = $template->template_path($type['template']);
            
            //Соеденяем с остальным путем
            $dir = (empty($type['path']))? File::concat($path,$this->content,$type['type']) :File::concat($path,$type['path']) ;
            
            //Если файла физически нет
            if(!File::exist($dir,$type['name'],$type['ext']))
                $types[$key]['no_exist'] = 1;
        }
        
        return $types;
    }
    /*
     * Возвращает все типы данных
     *
     * [!!!] Функция регистрозависимая
     *
     * @param  string имя типа что бы получить его ID !не обязательное
     * @return array  типы данных
     */
    function type_list($get = NULL){
        
        $sql = "SELECT id, type, description
                    FROM __type 
                    ORDER BY id;";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        
        $return = (array)$query->execute('type');
        if(is_null($get)){
            return $return;
        }else{
            return (array_key_exists($get,$return))? $return[$get]  : FALSE;
        }
    }
    
    /*
     * Возвращает стиль по id
     *
     * @param  int   id типа
     * @return array содержимое типа
     */
    function get_type($id,$path_return = FALSE){
        
        $where = '';
        
        if(!is_array($id)){
            $where = "WHERE ct.id = :id ";
        }else{
            //Корневные колонки
            $ct = array(
                'id_template',
                'id_type',
                'name',
                'ext'
            );
            //Колонки с других таблиц
            $table = array(
                'type' => 't',
                'template'=>array(
                                    'prefix'=> 'template',
                                    'col_name'=> 'name'
                                ),
            );
            
            //Определяем WHERE
            if($ct = Arr::intersect_key($id,$ct)){
                $where .= $this->sql->where('AND','ct',$ct);
            }
            if($table = $this->sql->intersect($id,$table)){
                $where = Str::concat('AND',$where,$this->sql->where('AND',$table));
            }
            if(!empty($where))
                $where = "WHERE ".$where;
        }
        
        $sql = "SELECT ct.id,ct.id_template, template.name AS template, t.type, ct.id_type, ct.name, ct.ext, ct.title, ct.description, ct.path
            FROM __content_type ct
            LEFT JOIN __type t ON t.id = ct.id_type
            INNER JOIN __template ON template.id = ct.id_template
            $where
            LIMIT 1;";
            
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);
        
        if($type = (array)$query->execute(NULL,TRUE)){
        
            //Берем класс, для создания путей к теме
            $template = Admin_Controller::factory('method','templates');
            
            //Создаем путь к теме
            $path = $template->template_path($type['template']);
            
            //Соеденяем с остальным путем
            $dir = (empty($type['path']))? File::concat($path,$this->content,$type['type']) :File::concat($path,$type['path']) ;
            
            //Ищем и возвращаем путь к файлу
            $type['path'] = File::exist($dir,$type['name'],$type['ext'],$path_return);
            
            //Берем расширение для редактора
            if(empty($type['ext'])){
                $type['ext'] = trim(EXT,'.');
            }
            //Заполняем для редактора
            $type['type_name'] = (isset($this->type[$type['ext']]))? $this->type[$type['ext']] : NULL ;
            
            return $type;
        }else{
            return array();
        }
    }
    
    /*
     * Сортирует массив
     *
     * @param  array массив типов для сортировки
     * @return array сортированые типы
     */
    function type_sort($types){
        
        if(!empty($types) AND is_array($types)){
                foreach($types AS $key => $type){
                    $types[(!empty($type['type']))? $type['type'] :$this->no_type ][$key] = $type;
                    
                    unset($types[$key]);
                }
                
            return $types;
            
        }else{
            return array();
        }
    }
    
    /*
     * Обнавляет тип
     *
     * @param  int   id типа
     * @param  array пареметры для обновления колонка => значение
     * @return array типы данных
     */
    function type_update($id, array $types){
        
        $update = Str::key_value($types);
        $where = '';
        if(!is_array($id)){
            $where = 'WHERE id = :id';
        }
        
        $sql = "UPDATE __content_type SET $update $where LIMIT 1";
        
        $query = DB::query(Database::UPDATE,  DB::placehold($sql));
        $query->param(':id',$id);
        
        return (array)$query->execute();
    }
    
    /*
     * Обнавляет тип контента
     *
     * @param  array поля WHERE
     * @param  array пареметры для обновления колонка => значение
     * @return array типы данных
     */
    function type_content_update(array $ids, array $update){
        
        $table_update = array(
            'content_type',
            'extends_content',
        );
        
        if($table = Arr::intersect_key($update,$table_update)){
            $update = Str::key_value($update);
        }else{
            throw new Core_Exception('Не пришли поля для UPDATE');
        }
        
        $where = '';
        $where = $this->sql->where('AND',$ids);
        
        if(!empty($where)){
            $where = 'WHERE '. $where;
        }else{
            throw new Core_Exception('Нет условия WHERE');
        }
        
        $sql = "UPDATE __type_content SET $update $where LIMIT 1";
        
        $query = DB::query(Database::UPDATE,  DB::placehold($sql));
        
        return (array)$query->execute();
    }
}