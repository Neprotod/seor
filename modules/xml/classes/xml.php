<?php

/**
 * Модуль работает со стилями и типами создает и выдает XML файлы.
 * 
 * @package    module
 * @category   xml
 */
class Xml_Module{
    /**
     * @var object объект DOM
     */
    protected $xml;
    /**
     * @var array массив для XML строки
     */
    protected $array = array();
    
    /**
     * @var array последний используемый массив
     */
    protected $lastArray = array();
    
    
    /**
     * @var object объект XMLWriter
     */
    protected $write;
    
    /**
     * @var object объект DOM::Element
     */
    protected $root;
    
    /**
     * @var string ключ массива, перед вызовом метода dom
     */
    protected $key;
    
    /**
     * @var string разделитель для Arr::path()
     */
    protected $delimiter = '.';
    
    /**
     * @var string используется для сортировки. Ключ сортировки
     */
    protected $uasort_key;
    
    /**
     * @var array ячейки массива, которые не вошли при проверке.
     */
    protected $uasort_nokey;
    
    /**
     * @var string путь модуля
     */
    protected $path;
    
    /**
     * @var array параметры в XSL
     */
    protected $static_param = array();
    
    /**
     * @var string путь к XML файлам
     */
    protected $xml_path;
    
    /**
     * @var string путь к XSD файлам
     */
    protected $xsd_path;
    
    /**
     * @var string нужны ли отступы. TRUE просто включает, можно указать '  '
     */
    protected $indent = FALSE;
    
    /**
     * @var array допустимые значения для подключения методов
     */
    protected $action = array('template','module');
    
    /**
     * @var object модель для подключения xml и xsl файлов в модулях
     */
    public $module_xml;
    /**
     * @var object модель для подключения xml и xsl файлов в темах
     */
    public $template_xml;
    
    
    function index(){}
    /**
     * Загружает основные данные, проверяет XSD схему.
     * 
     * 
     * @return void
     * @throws Core_Exception если схемы нет
     */
    function __construct($settings = NULL){
        
        //Подключаемые модули
        $this->module_xml = Model::factory('module','xml');
        $this->template_xml = Model::factory('template','xml');
        
        if(!empty($settings) AND is_array($settings)){
            $authorized_model = array("module_xml","template_xml");
            $substitution = array(
                "xml_dir" => "default_xml_dir",
                "xsl_dir" => "default_xsl_dir",
                "xml_ext" => "xml_ext",
                "xsl_ext" => "xsl_ext"
            );
            foreach($settings AS $key => $value){
                if(in_array($key, $authorized_model)){
                    if(is_array($value)){
                        foreach($value AS $p => $v){
                            if(isset($substitution[$p])){
                                $p = $substitution[$p];
                                $this->$key->$p = $v;  
                            }else{
                                throw new Core_Exception("Нет значения <b>:p</b> допустимые значения <b>:substitution</b>",array(
                                    ":p"=>$p,
                                    ":substitution"=>implode(" | ",array_keys($substitution))
                                    ));
                            }
                        }
                    }else{
                        throw new Core_Exception("Параметры для модели должны прийти в массиве");
                    }
                }else{
                    throw new Core_Exception("Нет такой модели <b>:key</b> к которой пришли настройки", array(":key"=>$key));
                }
            }
        }
        
        
        //Корневая папка модуля.
        $this->path = Module::mod_path('xml');
        //Создаем путь к папкам.
        $this->xml_path = $this->path. 'xml'.DIRECTORY_SEPARATOR;
        $this->xsd_path = $this->path. 'xsd'.DIRECTORY_SEPARATOR;
        
        //Создаем папки если их по каким-то причинам нет.
        if(!is_dir($this->xml_path))
            mkdir($this->xml_path);
        
        if(!is_dir($this->xsd_path))
            mkdir($this->xsd_path);
        
        $this->shema = $this->xsd_path . 'module.xsd';
        if(!is_file($this->shema))
            throw new Core_Exception('Нельзя без схемы запускать этот модуль, проверь схему по пути <b>:xsd_path</b> схема должна иметь имя <b>module.xsd</b>',array(':xsd_path'=>$this->xsd_path));
    }
    
    /**
     * Создает XML строку из массива, по XML образцу
     * 
     * @param  string $template имя темы 
     * @param  array  технический массив, например передать имя сессии
     * @return string xml строку
     */
    function preg_load($array = NULL, $xml, $xsl = NULL, $technical = array(), $static = array()){
        //Добавляем статические параметры
        $this->static_param = $static;
        
        $xml = $this->preg_parse($xml);
        $xsl = (!empty($xsl))
                ? $this->preg_parse($xsl)
                : $xsl;
        
        return $this->load($array,$xml,$xsl,$technical);
    }
    /**
     * Создает XML строку из массива, по XML образцу
     * 
     * @param  string $template имя темы 
     * @return string xml строку
     */
    function simple_load($xml){
        $xml = $this->preg_parse($xml);
        
        $xml_action = key($xml);
        $xml = current($xml);
        $path = Model::factory($xml_action,'xml')->xml_path($xml['name'],$xml['file'],$xml['admin']);
        
        return simplexml_load_file($path);
    }
    /**
     * Парсит строку и возвращает путь к файлу. 
     * 
     * 
     * @param  string строка типа module|system::pattern
     * @param  string тип данных, xml или xsl
     * @return array  xml строку
     */
    function preg_file_path($string,$type = "xsl"){
        $math = $this->preg_cheack($string);
        
        $key = $math["key"];
        
        $action = $key . "_file_path";
        
        return $this->$action($math["name"],$math["file"],(bool)$math["admin"],$type);
    }
    /**
     * Возвращает путь из темы. 
     * 
     * 
     * @param  string строка типа module|system::pattern
     * @return array  xml строку
     */
    function template_file_path($name,$file,$admin,$type){
        $in = array("xsl","xml");
        if(!in_array($type,$in)){
            throw new Core_Exception("Нет такого типа данных, может быть только <b>:type</b>",array(":type"=>implode(" | ",$in))); 
        }
        $type = $type . "_path";

        return $this->template_xml->$type($name,$file,$admin);
    }
    
    /**
     * Парсит строку и возвращает массив для методов. 
     * Проверка происходит через self::preg_cheack()
     * 
     * admin_template|default::dir_file
     * template|default::dir_file
     * module|system::dir_file
     * admin_module|system::dir_file
     * 
     * @param  string строка типа module|system::pattern
     * @return array  xml строку
     */
    function preg_parse($string){
        $math = $this->preg_cheack($string);
        
        $fond = array();
        
        $admin = (boolean)$math['admin'];
        
        //Заполняем массив
        $fond[$math['key']] = array('name'=>$math['name'],'file'=>$math['file'],'admin'=>$admin);
        
        return $fond;
    }
    /**
     * Парсит строку проверяет и возвращает массив 
     * 
     * admin_template|default::dir_file
     * template|default::dir_file
     * module|system::dir_file
     * admin_module|system::dir_file
     * 
     * @param  string строка типа module|system::pattern
     * @return array  xml строку
     */
    protected function preg_cheack($string){
        // Для того, что бы вернуть оригинальное имя файла 
        $original = $string;
        //Чтоб не было ошибок связанных с регистром
        $string = strtolower($string);
        
        //Массив значений с выборки
        $math = array();
        
        //Потерн на составление правильного массива
        $pattern = '/((?P<admin>[a-zA-Z]{0,})_)?(?P<key>[a-zA-Z]{0,})\|((?P<name>[a-zA-Z]{0,}):{2})?(?P<file>.{0,})/';
        
        preg_match($pattern,$string,$math);
        
        
        //Если строка не прошла регулярное выражение
        if(empty($math)){
            throw new Core_Exception('Строка не прошла регулярное выражение, пришла строка <b>:string</b>. Пример строки <b><i>module|system::pattern</i></b>',array(':string'=>$string));
        }
        
        //Не правильный ключ
        if(!in_array($math['key'],$this->action)){
            throw new Core_Exception('Не верное значения <b>:key</b>, могут быть только следующие значения <b><i>:action</i></b>',array(':key'=>$math['key'],':action'=>implode(',',$this->action)));
        }
        
        //Отсутствие имени может быль только у темы
        if(empty($math['name']) AND $math['key'] != 'template'){
            throw new Core_Exception('Пришла строка <b>:string</b>. Отсутствие имени может быль только у template. Пример: <i>template|file</i>, у модуля всегда должно быть имя <i>module|<b>name</b>::file</i>',array(':string'=>$string));
        }
        
        // Находим имя файла в строке
        $original_file = strripos($original,$math["file"]);
        $original_file = substr($original,$original_file);

        // Заменяем имя файла на оригинал
        if($strrchr = strrchr($original_file,"_")){
            $original_file = $strrchr;
            $explode = explode("_",$math["file"]);
            array_pop($explode);
            $explode = implode("_",$explode);
            $math["file"] = $explode . $original_file;
        }else{
            $math["file"] = $original_file;
        }
        
        return $math;
    }
    
    /**
     * Создает XML строку из массива, по XML образцу
     * 
     * Массив должен иметь следующий вид $xml['module'] = 
     * array('name' => 'имя','file' => 'путь_с_именем'[, admin => TRUE])
     *            $xml['module'] - начальная ячейка может иметь два значения
     *                             module   - запустит функцию xml_module()
     *                             template - запустит функцию xml_template()
     *                'name'  - имя модуля или темы
     *                'file'  - имя файла, или частичный путь через '_'
     *                'admin' - TRUE подключает административную тему или модуль
     *    
     * @param  array  массив для преобразования
     * @param  array  массив значение для xml
     * @param  array  массив значение для xsl
     * @param  array  технический массив, например передать имя сессии
     * @return string xml строку
     */
    function load($array = array(), array $xml, array $xsl = NULL,$technical = array()){
        if(!isset($array)){
            throw new Core_Exception('Для преобразования должен быть массив');
        }
        $xml_action = "xml_".key($xml);
        $xml = reset($xml);
        $this->check_array($xml,$xml_action);
        
        if(method_exists($this,$xml_action)){
            $found = $this->$xml_action($array,$xml['name'],$xml['file'],$xml['admin'],$technical);
        }else{
            throw new Core_Exception('Массив должен содержать начальную ячейку с ключом module или template');
        }

        if(!empty($xsl)){
            $xsl_action = "xsl_".key($xsl);
            $xsl = reset($xsl);
            $this->check_array($xsl,$xsl_action);
        
            if(method_exists($this,$xsl_action)){
                $found = $this->$xsl_action($found,$xsl['name'],$xsl['file'],$xsl['admin']);
            }else{
                throw new Core_Exception('Массив должен содержать начальную ячейку с ключом module или template');
            }
        }
        
        return $found;
    }
    
    /**
     * Проверяет массив на значения
     * 
     * @param  array  массив для проверки
     * @param  string строка, module или template
     * @return void
     */
    protected function check_array(array &$array, $key){
        
         if(!isset($array['name']))
              throw new Core_Exception('Массив должен содержать имя модуля');
         
         if(!isset($array['file']))
             throw new Core_Exception('Массив должен содержать имя файла');
         
         if(!isset($array['admin']))
             $xml['admin'] = FALSE;
     }
    
    /**
     * Создает XML строку из массива, по XML образцу
     *    
     * @param  array  массив для преобразования
     * @param  string имя модуля
     * @param  string файл, может быть указана дополнительная директория через '_'
     * @param  bool   TRUE - подключаем административный модуль
     * @param  array  технический массив, например передать имя сессии
     * @return string 
     */
    function xml_module($array = NULL,$module = NULL,$file = NULL,$admin = FALSE,$technical = array()){
        //Получаем путь к XML файлу
        $path = $this->module_xml->xml_path($module,$file,$admin);

        if(is_array($array)){
            return $this->xml_load($array,$path,$technical);
        }else{
            return file_get_contents($path);
        }
        
    }
    
    /**
     * Создает XML строку из массива, по XML образцу
     *    
     * @param  array  массив для преобразования
     * @param  string имя темы
     * @param  string файл, может быть указана дополнительная директория через '_'
     * @param  bool   TRUE - подключаем административную тему
     * @param  array  технический массив, например передать имя сессии
     * @return string 
     */
    function xml_template($array = array(),$template = NULL,$file = NULL,$admin = FALSE,$technical = array()){
        $path = $this->template_xml->xml_path($template,$file,$admin);

        if(is_array($array)){
            return $this->xml_load($array,$path,$technical);
        }else{
            return file_get_contents($path);
        }
    }
    
    /**
     * Преобразует XML с помощью XSL
     *    
     * @param  string xml строка для преобразования
     * @param  string имя модуля
     * @param  string файл, может быть указана дополнительная директория через '_'
     * @param  bool   TRUE - подключаем административный модуль
     * @return string 
     */
    function xsl_module($xml_string,$module = NULL,$file = NULL,$admin = FALSE){

        if(is_object($xml_string)){
            if($xml_string instanceof DOMDocument)
                $xml = $xml_string;
            else
                throw new Core_Exception('Класс должен быть DOMDocument');
        }else{
            $xml = new DOMDocument();
            $xml->loadXML($xml_string);
        }
        
        //Получаем путь к XML файлу
        $path = $this->module_xml->xsl_path($module,$file,$admin);
        
        $xsl = new DOMDocument();
        $xsl->load($path);
        
        $proc = new XSLTProcessor();
        
        //Загрузка XSL объекта
        $proc->importStylesheet($xsl);
        
        return $this->proc_transform($proc, $xml);
        
        //return $proc->transformToXML($xml);
    }
    
    /**
     * Преобразует XML с помощью XSL
     *    
     * @param  string xml строка для преобразования
     * @param  string имя темы
     * @param  string файл, может быть указана дополнительная директория через '_'
     * @param  bool   TRUE - подключаем административную тему
     * @return string 
     */
    function xsl_template($xml_string,$template = NULL,$file = NULL,$admin = FALSE){
        if(is_object($xml_string)){
            if($xml_string instanceof DOMDocument)
                $xml = $xml_string;
            else
                throw new Core_Exception('Класс должен быть DOMDocument');
        }else{
            $xml = new DOMDocument();
            $xml->loadXML($xml_string);
        }
        
        //Получаем путь к XML файлу
        $path = $this->template_xml->xsl_path($template,$file,$admin);
        
        $xsl = new DOMDocument();
        
        $proc = $this->get_xslt($xsl, $path);

        return $this->proc_transform($proc, $xml);
        
        //return $proc->transformToXML($xml);
    }
    
    /**
     * Подключает файл, либо создает из строки в зависимости от переменной static
     *    
     * @param  object  XSLTProcessor
     * @param  string  строка для преобразования
     * @return object  XSLTProcessor
     */
    protected function get_xslt($dom, $path){
        
        // Добавляем статические переменные в XSLT шаблон. 
        // Использование в шаблоне не отличается от xsl:param
        if(!empty($this->static_param)){
            $text = file_get_contents($path);
            $text = Str::__($text,$this->static_param);
            $this->static_param = array();
            
            $dom->loadXML($text); 
        }else{
            $dom->load($path); 
        }
        
        
        
        $proc = new XSLTProcessor();
        
        //Загрузка XSL объекта
        $proc->importStylesheet($dom);
        
        return $proc;
    }
    
    /**
     * Удаляет декларацию если она есть.
     *    
     * @param  object  XSLTProcessor
     * @param  string  строка для преобразования
     * @return string  XML файл
     */
    protected function proc_transform(XSLTProcessor $proc, $xml){
        $test = $proc->transformToXML($xml);
        
        if(substr($test, 0, 2) != "<!"){
            return $test;
        }else{
            $pos = strpos($test,">",2);
            return ltrim(substr($test,$pos+1));
        }
        
    }
    
    /**
     * Преобразует XML с помощью XSL
     *    
     * @param  array  массив для преобразования
     * @param  string путь к файлу преобразователю
     * @return string XML файл
     */
    protected function xml_load(array $array, $path,$technical = array()){
        $this->array = $array;
            
        $this->xml = new DOMDocument(); 
        $this->xml->presserveWhiteSpase = false;
        
        $this->xml->load($path);
        if(is_file($this->shema)){
            $this->xml->schemaValidate($this->shema);
        }
        
        $this->root = $this->xml->documentElement;
        
        //Для записи
        $this->write = new XMLWriter();
        
        $this->write->openMemory();
        //Создает отступы
        if($this->indent){
            $this->write->setIndent(TRUE);
            if(!is_bool($this->indent))
                $this->write->setIndentString($this->indent);
        }
        ///////////////////////////////////////
        $this->write->startElement($this->root->getAttribute('name'));
        
        // Если есть технические данные если массив многомерный создается элемент technical
        if(!empty($technical)){
            $technical_element = array();

            // Записываем атрибуты и передаем в $technical_element 
            // массивы где нужно сделать элементы
            foreach($technical AS $name => $value){
                if(is_array($value)){
                    if(!is_numeric($name)){
                       $technical_element[$name] = $value;
                    }else{
                        $technical_element += $value;
                        //throw new Core_Exception("Массив должен быть ассоциативным");
                    }
                    
                }else{
                    $this->write->writeAttribute($name,$value);
                }
            }

            // Если нужно сделать элемент technical
            if(!empty($technical_element)){
                $this->write->startElement("technical");
                foreach($technical_element AS $name => $value){
                    $this->write->startElement($name);
                     if(is_array($value)){
                        $this->technical($value);
                    }else{
                        $this->write->writeCData($value);
                    }
                    $this->write->endElement();
                }
                $this->write->endElement();
            }
        }
        
        $this->dom($array,$this->root);
        $this->write->endElement();
        
        return $this->write->outputMemory();
    }
    
    /**
     * Создает технический элемент в XML файле
     * 
     * @param  array  значения для технического массива
     * @return void
     */
    protected function technical($technical_element){
        foreach($technical_element AS $name => $value){
            $this->write->startElement($name);
            if(is_array($value)){
                $this->technical($value);
            }else{
                $this->write->writeCData($value);
            }
            $this->write->endElement();
        }
    }
    
    /**
     * Основной метод, для погружения и определения действий согласно XML файлу
     * 
     * @param  array  массив для преобразования
     * @param  object DOMNode
     * @return void
     */
    protected function dom($array,$root,$setting = array()){
        if($childs = $root->childNodes){
            foreach($childs AS $child){
                if($child->nodeType == 1){
                    $this->fonds['action'] = $child->nodeName;
                    $fonds = array();
                    if($attrs = $child->attributes){
                        foreach($attrs AS $name => $value){
                            $fonds[$name] = $value->value;
                        }
                    }
                    $action = 'type_'.$this->fonds['action'];
                    $this->$action($array,$child,$fonds,$setting);
                }
            }
        }
    }
    
    /**
     * Создает XML элемент
     * 
     * @param  array  массив для преобразования
     * @param  object DOMNode
     * @param  array  массив атрибутов
     * @return void
     */
    protected function type_element($array,$child,$attrs,$setting){
        //Берем имя элемента
        if(isset($attrs['name'])){
            $name = $attrs['name'];
        }else{
            throw new Core_Exception('У элемента должно быть имя');
        }

        if(array_key_exists('key',$attrs)){
            if($attrs["key"] == 'last'){
                $name = $this->lastKey;
            }else{
                $name = $this->key;
            }
        }
        elseif(array_key_exists('cell',$attrs) OR array_key_exists('rename',$attrs)){
            if(is_array($array)){
                    $cell = (array_key_exists('cell',$attrs))?$attrs['cell']:$attrs['rename'];
                    if($name = Arr::path($array,$attrs['cell'],NULL,$this->delimiter) AND is_array($name)){
                        throw new Core_Exception('Ошибка выборки, пришел массив для имени элемента, выборка <b>:cell</b>',array(':cell'=>$attrs['cell']));
                    }
            }else{
                throw new Core_Exception('Что бы использовать ячейку массива, нужен массив');
            }
        }

        
        if(empty($name)){
            $name = $attrs['name'];
        }elseif(is_int($name)){
            throw new Core_Exception('Имя не может быть числом, пришло имя <b>:name</b>, , атрибут name="<b>:attr</b>"',array(':name'=>$name,':attr'=>$attrs['name']));
        }
        $this->write->startElement($name);
        $this->dom($array,$child,$setting);
        $this->write->endElement();
    }
    
    /**
     * Создает динамический блок в XML (открывает foreach)
     * 
     * @param  array  массив для преобразования
     * @param  object DOMNode
     * @param  array  массив атрибутов
     * @return void
     */
    protected function type_dynamic($array,$child,$attrs,$setting){
        if(isset($attrs["cell"]))
            $this->key = $attrs["cell"];
        //Обнуление массива
        if(isset($attrs['reset_array'])){
            switch($attrs['reset_array']){
                case 'reset': $array = $this->array; break;
                case 'last'    : $array = isset($setting['lastArray'])?$setting['lastArray']:array(); break;
            }
            unset($attrs['reset_array']);
        }
        
        //Берем определенные ячейки если это нужно *::id взять только id у всех ячеек
        if(array_key_exists('cell',$attrs)){
            if(is_array($array)){
                    $array = Arr::path($array,$attrs['cell'],array(),$this->delimiter);
            }else{
                throw new Core_Exception('Для использования ячейки массива, нужен массив');
            }
        }
        ////////////////
        //Последовательность сортировки
        ////////////////
        
        //Сортировка по значению, не изменяя ключей массива
        if(array_key_exists('sort_value',$attrs)){
            $this->sort_value($array,$attrs['sort_value']);
        }
        
        //Сортированный, многомерный массив
        if(array_key_exists('sort',$attrs)){
            $this->sort($array,$attrs['sort']);
        }
        
        //Переворачиваем массив
        if(array_key_exists('revers',$attrs) AND is_array($array)){
            $array = array_reverse($array);
        }
        
        //Объедением массив
        if(array_key_exists('extract',$attrs) AND is_array($array)){
            $array = Arr::merge($array,$setting['lastArray']);
            var_dump($array);
        }
        
        //Преднастройки
        $setting = array();
        
        $setting['lastArray'] = $array;
        
        if(isset($this->key)){
            $this->lastKey = $this->key;
        }
        
        if(is_array($array) AND !empty($array)){
            if(!array_key_exists('one',$attrs)){
                foreach($array AS $key => $value){
                    $this->key = $key;
                    $setting['key'] = $key;
                    $this->dom($value,$child,$setting);
                }
            }else{
                $setting['key'] = key($array);
                $this->dom($array,$child,$setting);
            }
        }
        
    }
    
    /**
     * Создает атрибут
     * 
     * @param  array  массив для преобразования
     * @param  object DOMNode
     * @param  array  массив атрибутов
     * @return void
     */
    protected function type_attribute($array,$child,$attrs,$setting){
        //Берем имя атрибута
        if(isset($attrs['name'])){
            $name = $attrs['name'];
        }else{
            throw new Core_Exception('У атрибута должно быть имя');
        }
        
        $value = '';
        if(isset($attrs['key'])){
            //Изменение значений (использовать ключ массива)
            $value = $setting['key'];
        }
        elseif(array_key_exists('cell',$attrs)){
            //Изменение содержимое ячейки, если она есть
            if(is_array($array)){
                    if(array_key_exists($attrs['cell'],$array)){
                        if($value = Arr::path($array,$attrs['cell'],NULL,$this->delimiter) AND is_array($value)){
                            throw new Core_Exception('Ошибка выборки, пришел массив для имени атрибута, выборка <b>:cell</b>',array(':cell'=>$attrs['cell']));
                        }else{
                            if(empty($value)){
                                if(array_key_exists('not',$attrs) AND $attrs["not"] == "empty"){
                                    return;
                                }
                            }
                        }
                    }else{
                        //Если есть not не добавляем содержимое ячейки
                        if(array_key_exists('not',$attrs)){
                            return;
                        }
                        //Если есть replase пытаемся заменить
                        if(array_key_exists('replace',$attrs)){
                            if($value = Arr::path($array,$attrs['replace'],NULL,$this->delimiter) AND is_array($value)){
                                throw new Core_Exception('Ошибка выборки, пришел массив для имени атрибута, выборка <b>:cell</b>',array(':cell'=>$attrs['cell']));
                            }
                        }
                    }
            }else{
                $cell = isset($attrs['cell'])?$attrs['cell']:'';
                throw new Core_Exception('Для использования ячейки массива, нужен массив. Проблема с ячейкой: <b>:cell</b>',array(":cell"=>$cell));
            }
        }elseif(array_key_exists('current',$attrs)){
            if(!is_array($array)){
               $value = $array; 
            }else{
                throw new Core_Exception('Для атрибута <b>current</b> пришел массив, что является недопустимым значением.',array(":cell"=>$cell));
            }
            
        }
        
        //Устанавливаем значение по умолчанию если оно есть.
        if(empty($value) AND isset($attrs['default'])){
            $value = $attrs['default'];
        }
        
        if(empty($name))
            throw new Core_Exception('Проверьте имя аргумента, пришло пустое имя');
        
        //Создаем атрибут
        $this->write->writeAttribute($name,$value);
        //Погружаемся на уровень ниже
        $this->dom($array,$child,$setting);
    }

    /**
     * Создаем текстовый узел
     * 
     * @param  array  массив для преобразования
     * @param  object DOMNode
     * @param  array  массив атрибутов
     * @return void
     */
    protected function type_text($array,$child,$attrs,$setting){
        $value = '';
        
        //Если пришел массив, должна быть указана ячейка
        if(is_array($array)){
            if(array_key_exists('cell',$attrs)){
                if($value = Arr::path($array,$attrs['cell'],NULL,$this->delimiter) AND is_array($value)){
                        throw new Core_Exception('Ошибка выборки, пришел массив, ожидается текст, выборка <b>:cell</b>',array(':cell'=>$attrs['cell']));
                    }
            }else{
                throw new Core_Exception('Не указана ячейка массива');
            }
        }else{
            $value = $array;
        }
        
        if(array_key_exists('default',$attrs) AND empty($value)){
            $value = $attrs['default'];
        }
        //Определяем нужно ли использовать CDATA
        if(isset($attrs['other']) AND strtolower($attrs['other']) == 'cdata'){
            $this->write->writeCData($value);
        }else{
            $this->write->text($value);
        }
    }
    
    
     
    /**
     * Сортируем массив по указателю
     * 
     * @param  array  массив для преобразования
     * @param  string имя ячейки по которой нужно сортировать
     * @return void
     */
    protected function sort(&$array,$sort){
        if(is_array($array)){
            $fonds = array();
            foreach($array AS $key => $value){
                if(is_array($value) AND array_key_exists($sort,$value)){
                    $fonds[$value[$sort]][$key] = $value;
                }
            }
            $array = $fonds;
        }else{
            throw new Core_Exception('Нельзя сортировать не массив');
        }
    }
    
    /**
     * Сортируем массив по значению, не изменяя его ключи
     * 
     * @param  array  массив для преобразования
     * @param  string имя ячейки по которой нужно сортировать
     * @return void
     */
    protected function sort_value(array &$array,$sort){
        //Ключ для сортировки
        $this->uasort_key = $sort;
        
        //Ячейки массива, у которых нет этого ключа
        $this->uasort_nokey = array();
        
        //Отбираем нужные значения, что бы не было ошибки в методе uasort
        foreach($array AS $key => $value){
            if(!is_array($value)){
                    throw new Core_Exception('Ячейки массива не являются массивами, их нельзя сортировать');
            }
            if(!array_key_exists($this->uasort_key,$value)){
                $this->uasort_nokey[$key] = $value;
                unset($array[$key]);
            }
        }
        

        //Если есть что сортировать
        if(!empty($array)){
            uasort($array,array($this,'uasort'));
        }

        //Соединяем ячейки, которые небыли использованы
        if(!empty($this->uasort_nokey)){
            $array += $this->uasort_nokey;
        }
        
        unset($this->uasort_key,$this->uasort_nokey);
    }
    
    /**
     * Callbeak метод сортировки массива.
     * [USE] Используется в методе xml::sort_value
     * 
     * @param array ячейка массива
     * @param array ячейка массива для сравнения
     * @return void
     */
    protected function uasort($a,$b){
        return strnatcasecmp($a[$this->uasort_key],$b[$this->uasort_key]);
    }
    
    /**
     * Находим XML стили
     *
     * @param  string имя темы
     * @return array типы данных
     */
    function style($template,$xsl = NULL){
        if(!$this->sql->cache('style'))
            $this->create_style($template);
        
        //Работаем с XML
        $dom = new DOMDocument();
        
        $dom->load($this->xml_path.'/'.$template.'.xml');
        
        // ХМ, с этим местом что-то не так.
        $xml = Admin_Template::factory(Registry::i()->template,'xsl/page/page.xsl');
        
        $xsl = new DOMDocument();
        $xsl->loadXML($xml);
        
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);
        
        

        
        var_dump($xslt->transformToXML($dom));
    }
    
    /**
     * Создаем XML файл стилей
     *
     * @param  string имя темы
     * @return array типы данных
     */
    protected function create_style($template){
        $style = Admin_Model::factory('style','system');
        
        $styles = $style->get_styles($template,NULL,TRUE);
        
        //$styles = $style->style_sort($styles);

        //Работаем с ХМЛ
        $dom = new DOMDocument();

        
        $root = $dom->createElement('styles');
        
        $dom->appendChild($root);
        
        foreach($styles AS $fond){
            //Создаем элементы
            $file = $dom->createElement('file');
            
            $name = $dom->createElement('name');
            
            $title = $dom->createElement('title');
            
            $description = $dom->createElement('description');
            
            $path = $dom->createElement('path');
            
            $elements = array();
            //Текстовое наполнение элементов
            $elements['name'] = $dom->createTextNode($fond['name']);
            $elements['title'] = $dom->createCDATASection($fond['title']);
            $elements['description'] = $dom->createCDATASection($fond['description']);
            $elements['path'] = $dom->createTextNode($fond['path']);

            $attrs = array();
            //Создаем атрибуты
            $attrs['file']['type']['text'] = $dom->createTextNode($fond['type']);
            $attrs['file']['type']['attr'] = $dom->createAttribute('type');
            
            $attrs['file']['default']['text'] = $dom->createTextNode($fond['default']);
            $attrs['file']['default']['attr'] = $dom->createAttribute('default');
            
            $attrs['file']['id']['text'] = $dom->createTextNode($fond['id']);
            $attrs['file']['id']['attr'] = $dom->createAttribute('id');
            
            $attrs['file']['style_type']['text'] = $dom->createTextNode($fond['style_type']);
            $attrs['file']['style_type']['attr'] = $dom->createAttribute('style_type');
                        
            //Вставляем атрибуты
            foreach($attrs AS $key => $attr){
                foreach($attr AS $value){
                    $value['attr']->appendChild($value['text']);
                    //в file
                    $$key->appendChild($value['attr']);
                }
            }
            
            //Вставляем элементы и их содержимое
            foreach($elements AS $key => $value){
                $$key->appendChild($value);
                
                $file->appendChild($$key);
            }

            
            
            $root->appendChild($file);
        }
        
        if($dom->save($this->xml_path.'/'.$template.'.xml')){
            $this->sql->cache('style',1);
        }
    }
    
    /**
     * Метод из (многомерного) массива создает XML строку
     *
     * @param  string  имя корневого тега
     * @param  array   массив для превращения в XML
     * @param  array   массив ячеек которые должны быть атрибутами
     * @return string  XML строку
     */
    function write($root,array $array,$attribute = array(),$numeric = 'num'){
        if(!empty($attribute)){
            $attribute = array_flip($attribute);
        }
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement($root);
        
        //Создаем XML
        $this->writer_array($xml,$array,$attribute,$numeric);
        
        $xml->endElement();
        return $xml->outputMemory();
    }
    
    
    /**
     * Используется XML::write для создания XML строки из массива
     *
     * @param  object  экземпляр класса XMLWriter
     * @param  array   массив для превращения в XML
     * @param  array   массив ячеек которые должны быть атрибутами
     * @return void
     */
    protected function writer_array(XMLWriter $xml, $array, $attribute = array(),$numeric){
        //Для корректной вставки атрибутов, они должны быть сверху
        foreach($array AS $key => $value){
            if(isset($attribute[$key])){
                unset($array[$key]);
                Arr::unshift($array,$key,$value);
            }
        }
        
        //Создаем элементы
        foreach($array AS $tag => $text){
            $tag = empty($tag)? 'null' : $tag ;
            if(is_int($tag)){
                $tag = $numeric;
            }
            
            if(!is_array($text)){
                //Если есть в списке атрибутов
                if(isset($attribute[$tag])){
                    if($xml->startAttribute($tag)){
                        $xml->text($text);
                        $xml->endAttribute();
                    }
                }else{
                    //Создаем элемент
                    $xml->startElement($tag);
                    //Проверяем нужна ли CDATA
                    !is_int($text)?$xml->writeCData($text): $xml->text($text) ;
                    $xml->endElement();
                }
            }else{
                $xml->startElement($tag);
                $this->writer_array($xml,$text,$attribute,$numeric);
                $xml->endElement();
            }
        }
    }
    
    
}