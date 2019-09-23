<?php defined('MODPATH') OR exit();
/**
 * Модель подключает и загружает стили
 * 
 * @package    module/system
 * @category   style
 */
class Model_Style_System{
    
    /**
     * @var все стили (для заполнение используется Style::style_string())
     */
    public $style_string = array();
    
    /**
     * @var пути стилей (для заполнение используется Style::style_path())
     */
    public $style_path = array();
    
    /**
     * @var собираем все ошибки
     */
    public $error = array();
    
    function __construct(){
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
    }
    
    /**
     * Инициализирует и заполняет стили
     *
     * @param  mixed $id      id в таблице либо массив с id
     * @return array          массив стилей
     */
    function init($data){
        $return = array();
        
        $template = Model::factory('template','system');
        //Определяем файл
        $type = (!isset($data["type"]))
            ? Registry::i()->founds['type']
            : $data["type"];
        //Находим стили темы (стандартные)
        $template_id = (isset(Registry::i()->template['id']))? intval(Registry::i()->template['id']) : Registry::i()->template['name'];

        $template_style = $template->template_css($template_id);

        //Находим стили относящиеся к данной странице.
        $style = $this;

        $data_style = array();

        $data_style = $style->get_style($data,$type);
        //Проверяем стандартные стили.
        $data_style = $style->valid_css($template_style,$data_style);

        //Создаем пути стилей
        $style_path = $style->style_path($data_style,Registry::i()->root);
        
        if(isset($data["id_type"])){
            $style_jc = Query::i()->sql("style.get_js_css",array(
                ":id_table"=> $data["id"],
                ":id_type"=> $data["id_type"]
                ),NULL,TRUE);
        }
        //Загружаем строки стиля для дальнейшего вывода
        if(isset($style_jc) AND !empty($style_jc))
            $style->style_string(array('css'=>$style_jc['css'],'js'=>$style_jc['js']),array('$root'=>Registry::i()->root));
        
        
        //Собираем контент на вывод
        Registry::i()->style = $style;
        
        return $template->header($data, $style);
    }    
    
    /**
     * Находит и возвращает css или JS
     *
     * @param  mixed $id      id в таблице либо массив с id
     * @param  mixed $type    тип данных
     * @param  mixed $extends расширение
     * @return array          массив стилей
     */
    function get_style($data,$type,$extends = FALSE){
        if(!isset($data["content_type"]) AND !isset($data["style_name"])){
            return array();
        }
        
        $where = '';
        
        if($extends === TRUE)
            $where = "AND sc.extends = 1";
        elseif($extends === FALSE)
            $where = "AND sc.extends = 0";
        

        $table_type = "";
        
        if(isset($data["content_type"])){
            unset($data["type"]);
        }else{
            //Если нужно указать тип данных
            if(is_array($type)){
                /*$table_type = key($type);
                $type = reset($type);*/
                $data += $type;
            }else{
                $data["type"] = $type;
            }
        }
        
        $table = array(
            "type" => "type",
            "content_type" => array(
                    "prefix" => "sc",
                    "col_name" => "id_content_type",
            ),
            "style_name" => array(
                    "prefix" => "ct",
                    "col_name" => "name",
            ),
        );
        $table = $this->sql->intersect($data, $table);
        $table = $this->sql->where("AND", $table);
        
        $where = "$table {$where} AND (t.name = 'css' OR t.name = 'js')";

        return Query::i()->sql("style.get_style",array(":where"=>$where),"id");
    }
    /**
     * Создает пути для css и js
     *
     * Должен быть правильный массив содержащий style_type - тип стиля, path - путь от темы
     * folder - папка по умолчанию, type - тип как page или category, name - имя файла.
     *
     * @param  array  $style         массив путей
     * @param  string $template_path путь темы
     * @return array                 правильные пути к файлам.
     */
    function style_path(array $style,$template_path){
        $founds = '';
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
                    $founds[$value['style_type']][] = '/'.$template_path.$path;
                }else{
                    $this->error[$value['style_type']] = '/'.$template_path.$path;
                }
                
            }else{
                throw new Core_exception('Не правильно оформленный массив');
            }
        }
        
        return $this->style_path = $founds;
    }
    
    /**
     * Собирает массив стилей в строке
     *
     * @param  array $style массив стилей
     * @param  array $date  
     * @return void
     */
    function style_string(array $style,array $date = array()){
        foreach($style AS $key => $value){
            if(!empty($value)){
                $this->style_string[$key] = ($key == 'css')
                                                ? Str::__($value,$date) 
                                                :$value;
            }
        }
    }
    /**
     * Сравнивает стандартные стили с установленными
     *
     * @param array $page_style     стили основные стили
     * @param array $template_style стили заменяемые стили
     * @return string
     */
    function valid_css(array $data_style, array $template_style){
        $data_style = Arr::merge($data_style,$template_style);

        if(!empty($data_style))
            foreach((array)$data_style AS $key => $value)
                if(isset($value['status']) AND $value['status'] == '0')
                    unset($data_style[$key]);
        
        return $data_style;
    }
    
    /**
     * Преобразует все стили в нужные строки
     *
     * @return string
     */
    function get(){
        $xml_pars = "module|system::style";
        $xsl_pars = "module|system::style";
        $data = array();
        $data["style_path"] = $this->style_path;
        $data["style_string"] = $this->style_string;
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars);
    }
    /**
     *
     * Запускает при выводе метод get
     */
    function __toString(){
        return  $this->get();
        /*
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
        */
    }
}