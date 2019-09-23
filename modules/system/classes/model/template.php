<?php defined('MODPATH') OR exit();

/**
 * Модель подключает тему
 * 
 * @package    module/system
 * @category   template
 */
class Model_Template_System{
    /**
     * @var object экземпляр URL
     */
    protected $url;
    
    /**
     * @var Тема по умолчанию.
     */
    protected $template_default = 'default';
    
    /**
     * @var Путь к теме.
     */
    protected $template_dir;
    
    function __construct(){
        $this->template_dir = APPPATH . 'template' . DIRECTORY_SEPARATOR;
        $this->xml = Module::factory("xml",TRUE);
    }
    
    /**
     * Инициализируем тему.
     *
     * @return void
     * @throws Core_Exception
     */
    function init(){
        // Инициализируем начальную тему.
        //$template = $this->get_template();

        if($template = $this->get_template()){
            //Инициализируем тему.
            $path = $this->check($template['name']);
            
            try{
                //Если темы нет, подключить стандартную и записать ошибку в XML
                if(!$path){
                    $this->template_disable($template['name']);
                    $path = $this->set_default();
                    throw new Exception("Нет темы <b>{$template['name']}</b>");
                }
            }catch(Exception $e){
                $exception = Model::factory('exception','system');
                $exception->set_xml($e,array('client'=>'true'));
            }
        }else{
            $path = $this->set_default();
            $template = array('name'=>$this->template_default);
        }
        // Подключаем стандартную тему если другой нет.
        if(!empty($path)){
            Registry::i()->template = $template;
            Registry::i()->path = $path;
            return Registry::i()->root = str_replace(array(DOCROOT,"\\"),'/',$path);
        }
        throw new Core_Exception('Не существует стандартной темы');
        
    }
     
    /**
     * Находим используемую тему
     * 
     * @return array возвращает найденную тему
     */
    function get_template(){
        return Query::i()->sql("template.get_template",NULL,NULL,TRUE);;
    }
    /**
     * Находим используемую тему
     * 
     * @return array возвращает найденную тему
     */
    function path($data){
        // Файл для подключения отображения.
        $file = $data['file_name'];
        $ext = isset($data['ext'])
            ? $data['ext']
            : NULL;
        // Отдельный путь если есть.
        $path = isset($data['path'])
            ? $data['path']
            : "";
        //Определяем файл
        $type = isset($data["type"])
            ? $data["type"]
            : Registry::i()->founds['type'];
        
        return $this->template_file(Registry::i()->template,$type,$file,$ext,$path);
    }
    
    /********************
    ***Защищенные функции
    *********************/
    /**
     * Проверяем существует ли тема
     *
     * @param  string $template имя темы
     * @return mixed            если тема существует возвращаем путь либо FALSE
     */
    protected function check($template){
        $path = $this->template_dir . $template;
        if(is_dir($path))
            return $path;
        else
            return FALSE;
    }
    
    /**
     * Отключаем тему если ее не существует
     *
     * @param string $template имя темы
     * @return void
     */
    protected function template_disable($template){
        // Убираем не существующую тему.
        $sql = "UPDATE __template SET status = 0
                    WHERE name = :template";
                  
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->param(':template',$template);
        
        $query->execute();
    }
    /**
     * Подключаем тему по умолчанию.
     *
     * @return mixed путь в теме по умолчанию либо FALSE
     */
    protected function set_default(){
        if($path = $this->check($this->template_default)){
            return $path;
        }
        return FALSE;
    }
    /********************
    ***Публичные функции
    *********************/
    /**
     * Находим css файлы по умолчанию.
     *
     * @param mixed $id id или имя темы
     * @return array    массив css файлов.
     */
    function template_css($id){
        /*$join = '';
        if(is_int($id)){
            $where = "WHERE s.id_template";
        }else{
            $join = "INNER JOIN __template template ON s.id_template = template.id";
            $where = "WHERE template.name";
        }
        // Тема по умолчанию
        $sql = "SELECT s.id, s.id_template, type.type, s.name, s.path, t.name AS style_type, t.folder
                    FROM style s
                    INNER JOIN __style_type t ON s.style_type = t.id
                    LEFT JOIN __type type ON s.id_type = type.id
                    $join
                    $where = :id_template AND s.default = 1;";
        
        $query = DB::query(Database::SELECT, DB::placehold($sql));
        
        $query->param(':id_template',$id);
        */
        $where = "s.default = 1 AND s.id_type IS NULL AND (t.name = 'css' OR t.name = 'js')";
        
        return Query::i()->sql("style.get_default_style",array(":where"=>$where),"id");
    }
    
    /**
     * Находим css файлы по умолчанию.
     *
     * @param  array  $template тема
     * @param  string $type     тип
     * @param  string $file     имя файла
     * @param  string $ext      расширение файла
     * @param  string $path     альтернативный путь
     * @return string           возвращает путь.
     */
    function template_file(array $template,$type,$file,$ext = NULL,$path = NULL){
        $content = 'content';
        if ($ext === NULL){
            // Используем расширение по умолчанию
            $ext = EXT;
        }
        elseif ($ext){
            $ext = trim($ext,".");
            // Используем заданное расширение
            $ext = ".{$ext}";
        }else{
            // без расширения
            $ext = '';
        }
        if(!isset($template['name'])){
            if(isset(Registry::i()->template))
                $template = Registry::i()->template;
            else
                throw new Core_exception('Файл с темой не пришел, для подключения файла <b>:file</b> ',array(':file'=>$file));
        }
        
        $template = $this->template_dir . $template['name'];

        $path = (!empty($path))? trim($path,'/'): "{$content}" ;
        
        $type = (!empty($type))? "/{$type}": "" ;
        
        $file = $file.$ext;
        $path = $path.$type.'/'.$file;
        
        if(is_file($template.'/'.$path)){
            return $path;
        }else{
            throw new Core_exception('Файла <b>:file</b> нет, нельзя отобразить тему',array(':file'=>$path));
        }
    }
    /**
     * Находим css файлы по умолчанию.
     *
     * @param mixed $date если массив должен содержать следующие значения title, description, meta_title, robots
     * @return array      с перечисленными значениями.
     */
    function header($data = NULL, Model_Style_System $style = NULL){
        if(is_array($data)){
            $xml_pars = "module|system::head";
            $xsl_pars = "module|system::head";
            foreach($data as &$value){
                $value = Str::html_encode($value);
            }

            if(isset($data['title']) AND !isset($data['meta_title'])){
                $data['meta_title'] = $data['title'];
            }
            
            //Канонический URL
            $data['canonical'] = (isset(Registry::i()->founds['canonical']))? Core::$root_url.'/'.trim(Registry::i()->founds['canonical'],'/')  : NULL;
            
            if($style){
              $style = $style->get();
            }else{
                if(isset($style))
                    $style = Registry::i()->style->get();
            }
            $data['style'] = $style;  
            
            return Registry::i()->header = $this->xml->preg_load($data,$xml_pars,$xsl_pars);
        }else{
            return (isset(Registry::i()->header))? Registry::i()->header : NULL;
        }
    }
}