<?php defined('MODPATH') OR exit();

/*
 * Модель определ¤ет к какому типу относится URL  
 */
class Controller_Method_Templates_Admin{
    
    //@var string путь к теме
    public $path;
    
    //@var object модель для работы с типами
    public $type;
    
    //@var array расширение файлов для картинки темы
    protected $image_ext = array('jpg','jpeg','png');
    
    function __construct(){
        $this->path = str_replace(array(ADMINROOT,'\\'),'/',APPPATH).'template/';
    }
    
    /*
     * Находит и возвращает темы
     *
     * $sort[order] - по какому столбцу
     * $sort[sort] - ASC или DESC;
     *
     * @return array список тем
     */
    function get_templates(){
        
        //Создаем запрос
        $sql = "SELECT id, name, title, description, status
                    FROM __template
                    ORDER BY status";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        
        return $query->execute();
    }    
    
    /*
     * Возвращает все связанное с данной темой
     *
     * @param int id темы
     * @return array список тем
     */
    function get_template($id){
        
        $where = '';

        if(is_int($id))
            $where = 'WHERE id = :id';
        else
            $where = 'WHERE name = :id';
        
        //Создаем запрос
        $sql = "SELECT id, name, title, description, status
                    FROM __template
                    $where
                    LIMIT 1";
        
        $query = DB::query(Database::SELECT,  DB::placehold($sql));
        $query->param(':id',$id);
        
        return $query->execute(NULL,TRUE);
    }
    
    /*
     * Возвращает путь к теме по имени
     *
     * @param  string имя темы
     * @return string путь к теме
     */
    function template_path($name){
        //Если пришел id
        if(is_int($name)){
            if($template = $this->get_template($name))
                $name = $template['name'];
            else
                throw new Core_Exception('Нет темы с id :name',array(':name'=>$name));
        }
        
        $path = ltrim($this->path,'/').$name.'/';
        
        if(is_dir($path)){
            return $path;
        }else{
            throw new Core_Exception("Нет папки темы с именем $name");
        }
    }
    
    /*
     * Возвращает настройки если они существуют
     * В папке с темой должен быть xml файл с именем темы
     *
     * @param  string имя темы
     * @return array настройки либо пустой массив
     */
    function template_settings($template_name){
        $path = $this->template_path($template_name);
        
        //Путь к файлу конфигураций
        $xml = $path.$template_name.'.xml';
        
        if(is_file($xml)){
            //Путь к файлу валидации
            $validation = DESIGN_ADMIN . 'config' . DIRECTORY_SEPARATOR . 'template_validation.xsd';
            
            try{
                //Массив содержащий все найденные значения
                $fonds = array();
                $doc = new DOMDocument();
                
                //Убераем пустые строки
                $doc->preserveWhiteSpace = FALSE;
                
                //Загружаем файл и конфигурацию
                $doc->load($xml);
                if(is_file($validation))
                    $doc->schemaValidate($validation);
                
                //Класс для XPATH выражений
                $xpath = new DOMXPath($doc);
                
                //Ищем заголовок и описание темы
                $query = "/template/title|/template/description";
                $desriptions = $xpath->query($query);
                //Если найдены
                if(!empty($desriptions->length)){
                    foreach($desriptions AS $desription){
                        $fonds[trim($desription->nodeName)] = trim($desription->nodeValue);
                    }
                }
                
                //Ищем стили и типы
                $query = "/template/style/*|/template/type";
                $styles_types = $xpath->query($query);
                
                //Если есть стили или типы данных
                if(!empty($styles_types->length)){
                    foreach($styles_types AS $style_type){
                        if($style_type->nodeType != 1)
                            continue;
                        
                        //Имя для сортировки
                        $name = trim($style_type->nodeName);
                        
                        //Если есть родительский элемен дополнительно сортируем по ниму
                        $fond = array();
                        if($style_type->parentNode->nodeName != $doc->documentElement->nodeName){
                            $parent = trim($style_type->parentNode->nodeName);
                            $fonds[$parent][$name] = &$fond;
                        }else{
                            $fonds[$name] = &$fond;
                        }
                        
                        //Отбираем все файлы стиля или типа данных
                        foreach($style_type->childNodes AS $files){
                            
                            //Определяем тип файла и является ли файл по умолчанию
                            $extract = array();
                            if($files->hasAttributes())
                                foreach($files->attributes AS $key => $value){
                                    $extract[trim($key)] = trim($value->value);
                                }
                            
                            //Если есть тип, дополняем его
                            $fond_link = array();
                            if(!empty($extract['type'])){
                                $fond[$extract['type']][] = &$fond_link;
                                unset($extract['type']);
                            }else{
                                $fond['NULL'][] = &$fond_link;
                            }
                            
                            //Отбираем все характиристики файла
                            foreach($files->childNodes as $file){
                                if($file->nodeType != 1)
                                    continue;
                                
                                $fond_link[$file->nodeName] = trim($file->nodeValue);
                                //Если есть дополнительные аргументы, дополняем
                                if($file->hasAttributes())
                                    foreach($file->attributes AS $key => $value){
                                        $fond_link[$key] = trim($value->value);
                                    }
                            }
                            
                            $fond_link = Arr::merge($fond_link,$extract);
                            unset($fond_link);
                        }
                        unset($fond);
                    }
                }
            }catch(Exception $e){
                $error = "<div>В настроках темы $template_name следующая ошибка: </div>";
                $error .= "<div>".$e->getMessage()."</div>";
            }
            return $fonds;
        }else{
            return array();
        }
    }
    
    /*
     * Возвращает все найденные файлы распределяя их по типам
     *
     * @param  string имя темы
     * @return array все найденные файлы
     */
    function template_file($template_name){
        $path = $this->template_path($template_name);
        
        $fonds = array();
        
        //Создаем запрос на определния типов данных
        $sql = "SELECT type FROM __type ORDER BY id;";
        
        $query = DB::query(Database::SELECT,DB::placehold($sql));
        
        $types = (array)$query->execute('type');
        
        //Создаем запрос на определния стилей
        $sql = "SELECT name, folder FROM __style_type ORDER BY id;";
        
        $query = DB::query(Database::SELECT,DB::placehold($sql));
        
        $style_types = $query->execute('name');

        $content = $path . 'content/';
        //Опредляем все типы данных
        if(!empty($types) AND is_dir($content))
            foreach($types AS $name => $type){
                $dir = $content.$name.'/';
                $scans = scandir($dir);
                unset($scans[0],$scans[1]);
                if(!empty($scans))
                    foreach($scans as $scan){
                        if(is_file($dir.$scan)){
                            $info = pathinfo($scan);
                            
                            $fonds['style'][$name][]['name'] = $info['filename'];
                            if($info['extension'] != 'php')
                                $fonds['style'][$name][]['ext'] = $info['extension'];
                        }
                    }
            }
            
        array_unshift($types,NULL);
        //Определяем все стили
        if(!empty($style_types))
            foreach($style_types AS $style => $type){
                $dir = $path.$type['folder'].'/';
                foreach($types as $name => $file){
                    
                }
            }
    }
    
    /*
     * Возвращает картинку темы
     *
     * @param  string имя темы
     * @return mixed путь к изображению темы либо FALSE
     */
    function template_image($template_name){
        $path = $this->template_path($template_name).$template_name;
        
        foreach($this->image_ext AS $ext){
            $image = $path.'.'.$ext;
            if(is_file($image))
                return $image;
        }
        return FALSE;
        
    }
}