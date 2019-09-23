<?php defined('MODPATH') OR exit();

/*
 * Модель подключает и загружает стили
 */
class Model_Style_System{
    
    public $style_string = array();
    public $style_path = array();
    public $error = array();
    
    function __construct(){}
    
    /*
     * Находит и возвращает css
     *
     * @param  mixed id в таблице либо массив с id
     * @param  mixed тип данных
     * @return array массив стилей
     */
    function get_style($id,$type,$extends = FALSE){
        $where = '';
        
        if($extends === TRUE)
            $where = "AND sc.extends = 1";
        elseif($extends === FALSE)
            $where = "AND sc.extends = 0";
        
        $table_type = "";
        //Если нужно указать тип данных
        if(is_array($type)){
            $table_type = key($type);
            $type = reset($type);
        }
        
        $sql = "SELECT s.id, s.id_template, st.type, s.name, s.path, t.name AS style_type, t.folder, sc.status, sc.extends
                    FROM __style s
                    INNER JOIN __style_type t ON s.style_type = t.id
                    INNER JOIN __style_content sc ON s.id = sc.id_style
                    LEFT JOIN __type type ON sc.id_type = type.id
                    LEFT JOIN __type st ON s.id_type = st.id
                    WHERE sc.id_table IN(:id_table)  AND type.type = :type {$where};";
        
        $sql = DB::placehold($sql,array(':id_table'=>implode(',',(array)$id)));

        $query = DB::query(Database::SELECT, $sql);
        $query->param(':type',$type);
        
        return (array)$query->execute('id');
    }
    /*
     * Создает пути для css и js
     *
     * Должен быть правельный массив содержащий style_type - тип стиля, path - путь от темы
     * folder - папка по умолчанию, type - тип как page или category, name - имя файла.
     *
     * @param  array  массив путей
     * @param  string путь темы
     * @return array  правельные пути к файлам.
     */
    function style_path(array $style,$template_path){
        $fonds = '';
        $template_path = trim($template_path,'/');
        foreach($style as $value){
            if( isset($value['style_type']) AND !empty($value['style_type']) AND
                isset($value['name']) AND !empty($value['name']) AND
                (isset($value['path']) OR  is_null($value['path'])) AND
                (isset($value['type']) OR is_null($value['type'])) AND
                isset($value['folder'])){
                
                // Создаем начальные пути.
                if(!empty($value['path'])){
                    $path = "/{$value['path']}";
                }else{
                    $folder = (!empty($value['folder']))? "/{$value['folder']}" : '';
                    $type = (!empty($value['type']))? "/{$value['type']}" : '';
                    $path = $folder . $type;
                }
                $path .= "/{$value['name']}.{$value['style_type']}";
                //Создаем путь для проверки
                $realpath = realpath($template_path);
                //Проверяем файл
                if(is_file($realpath.$path)){
                    $fonds[$value['style_type']][] = '/'.$template_path.$path;
                }else{
                    $this->error[$value['style_type']] = '/'.$template_path.$path;
                }
                
            }else{
                throw new Core_exception('Не правильно оформленный массив');
            }
        }
        return $this->style_path = $fonds;
    }
    
    /*
     * Собирает массив стилей в строке
     *
     * @param  array  массив стилей
     * @return void
     */
    function style_string(array $style,array $date = array()){
        foreach($style AS $key => $value){
            if(!empty($value)){
                $this->style_string[$key] = ($key == 'css')? Str::__($value,$date) :$value;
            }
        }
    }
    /*
     * Сравниват стандартные стили с установленными
     *
     * @param array стили основные стили
     * @param array стили заменяемые стили
     * @return string
     */
    function valid_css(array $page_style, array $template_style){
        $page_style = Arr::merge($page_style,$template_style);

        if(!empty($page_style))
            foreach((array)$page_style AS $key => $value)
                if(isset($value['status']) AND $value['status'] == '0')
                    unset($page_style[$key]);
        
        return $page_style;
    }
    /*
     * Преобразует все стили в нужные строки
     *
     * @return string
     */
    function __toString(){
        $return = '';
        //Подключаем все CSS
        if(isset($this->style_path['css']) AND $css = $this->style_path['css']){
            foreach($css AS $value){
                $return .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$value}\" />\n";
            }
        }
        //Подключаем все JS
        if(isset($this->style_path['js']) AND $js = $this->style_path['js']){
            foreach($js AS $value){
                $return .= "<script type=\"text/javascript\" src=\"$value\"></script>\n";
            }
        }
        
        //Подключаем если есть строковые стили
        if(isset($this->style_string['css']) AND !empty($this->style_string['css'])){
            $return .= "\n";
            $return .= "<style type=\"text/css\">\n";
            $return .= "{$this->style_string['css']}\n";
            $return .= "</style>\n";
        }
        if(isset($this->style_string['js']) AND !empty($this->style_string['js'])){
            $return .= "\n";
            $return .= "<script type=\"text/javascript\">\n";
            $return .= "{$this->style_string['js']}\n";
            $return .= "</script>\n";
        }
        return $return;
    }
}