<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Template_System{
    
    protected $url;
    
    // Тема по умолчанию.
    protected $template_default = 'default';
    
    // Путь к теме
    protected $template_dir;
    
    function __construct(){
        $this->template_dir = APPPATH . 'template' . DIRECTORY_SEPARATOR;
    }
    
    function init(){
        // Инициализируем начальную тему.
        $template = $this->get_template();

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
    
    /********************
    ***Защищенные функции
    *********************/
     
    /*
     * Находим ипользуемую тему
     * @return array возвращает найденую тему
     */
    protected function get_template(){
        $sql = "SELECT id, name
                    FROM __template
                    WHERE status = 1
                    LIMIT 1";
                  
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        return $query->execute(NULL,TRUE);
    }
    /*
     * Проверяем существует ли тема
     * @param string имя темы
     * @return mixed если тема существует возвращаем путь либо FALSE
     */
    protected function check($template){
        $path = $this->template_dir . $template;
        if(is_dir($path))
            return $path;
        else
            return FALSE;
    }
    
    /*
     * Отключаем тему если ее не существует
     * @param string имя темы
     * @return void;
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
    /*
     * Подключаем тему по умолчанию.
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
    /*
     * Находим css файлы по умолчанию.
     * @param mixed id или имя темы
     * @return array массив css файлов.
     */
    function template_css($id){
        $join = '';
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

        return $query->execute('id');
    }
    
    /*
     * Находим css файлы по умолчанию.
     * @param array тема
     * @param string тип
     * @param string имя файла
     * @param string расширение файла
     * @param string альтернативный путь
     * @return string возвращает путь.
     */
    function template_file(array $template,$type,$file,$ext = NULL,$path = NULL){
        $content = 'content';
        if ($ext === NULL){
            // Используем расширение по умолчанию
            $ext = EXT;
        }
        elseif ($ext){
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
            throw new Core_exception('Файла <b>:file</b> нет, нельзя отобразить тему',array(':file'=>$file));
        }
    }
    /*
     * Находим css файлы по умолчанию.
     * @param mixed  если массив должен содержить слеющие значения 
     * title, description, meta_title, robots
     * @return array с перечисленными значениями.
     */
    function header($date = NULL){
        if(is_array($date)){
            //Преобразуем HTML сущности
            foreach($date as &$value){
                $value = Str::html_encode($value);
            }
            Registry::i()->header['title'] = (isset($date['title']))? $date['title']  : NULL;
            Registry::i()->header['meta_title'] = (isset($date['meta_title']))? $date['meta_title']  : NULL ;
            
            if(isset($date['title']) AND !isset($date['meta_title'])){
                Registry::i()->header['meta_title'] = $date['title'];
            }
            
            Registry::i()->header['meta']['description'] = (isset($date['description']))? $date['description']  : NULL ;
            Registry::i()->header['meta']['robots'] = (isset($date['robots']))? $date['robots']  : NULL;
            
            //Канонический URL
            Registry::i()->header['link']['canonical'] = (isset(Registry::i()->fonds['canonical']))? Core::$root_url.'/'.trim(Registry::i()->fonds['canonical'],'/')  : NULL;
        }else{
            return (isset(Registry::i()->header))? Registry::i()->header : NULL;
        }
    }
}