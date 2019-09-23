<?php

/*
 * Модуль работает со стилями и типами создает и выдает XML файлы.
 */
 
class Xml_Admin{
    
    //@var object объект DOM
    protected $xml;
    
    //@var array массив для XML строки
    protected $array = array();
    
    //@var array последний используемый массив
    protected $lastArray = array();
    
    
    //@var object объект XMLWriter
    protected $write;
    
    //@var object объект DOM::Element
    protected $root;
    
    //@var string ключь массива, перед вызовом метода dom
    protected $key;
    
    //@var string разделитель для Arr::path()
    protected $delimiter = '::';
    
    //@var string используется для сортировки. Ключь сортировки
    protected $uasort_key;
    
    //@var array ячейки массива, которые не вошли при проверке.
    protected $uasort_nokey;
    
    //@var string путь модуля
    protected $path;
    
    //@var string путь к XML файлам
    protected $xml_path;
    
    //@var string путь к XSD файлам
    protected $xsd_path;
    
    
    function index(){}
    
    function __construct(){
        $this->sql = Admin_Model::factory('sql','system');
        
        //Корневая папка модуля.
        $this->path = Admin_Module::mod_path('xml');
        //Создаем путь к папкам.
        $this->xml_path = $this->path. 'xml'.DIRECTORY_SEPARATOR;
        $this->xsd_path = $this->path. 'xsd'.DIRECTORY_SEPARATOR;
        
        //Создаем папки если их по каким-то причинам нет.
        if(!is_dir($this->xml_path))
            mkdir($this->xml_path);
        
        if(!is_dir($this->xsd_path))
            mkdir($this->xsd_path);
            
    }
    
    /*
     * Создает XML строку из массива, по XML образцу
     * 
     * 
     */
    function xml($array = array(),$modul = NULL,$file = NULL,$indent = TRUE){
        
        $this->array = $array;
        
        $dir = Admin_Module::mod_path($modul).'xml/';
        
        if($path = $dir.$file.'.xml' AND !is_file($path)){
            throw new Core_Exception('Нет файла преобразования <b>:file</b> на пути <b>:dir</b>',array(':file'=>$file,':dir'=>$dir));
        }

        $this->xml = new DOMDocument(); 
        $this->xml->presserveWhiteSpase = false;
        $shema = $this->xsd_path . 'module.xsd';
        
        $this->xml->load($path);
        if(is_file($shema)){
            $this->xml->schemaValidate($shema);
        }
        
        $this->root = $this->xml->documentElement;
        
        //Для записи
        $this->write = new XMLWriter();
        
        $this->write->openMemory();
        //Создает отступы
        if($indent){
            $this->write->setIndent(TRUE);
            if(!is_bool($indent))
                $this->write->setIndentString($indent);
        }
        ///////////////////////////////////////
        
        $this->write->startElement($this->root->getAttribute('name'));
        
        $this->dom($array,$this->root);
        $this->write->endElement();
        
        return $this->write->outputMemory();
    }
    
    /*
     * Основной метод, для погружения и определения действий согласно XML файлу
     * 
     * @param array  массив для преобразования
     * @param object DOMNode
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
    
    /*
     * Создает XML элемент
     * 
     * @param array  массив для преобразования
     * @param object DOMNode
     * @param array  массив атрибутов
     * @return void
     */
    protected function type_element($array,$child,$attrs,$setting){
        //Берем имя элемента
        if(isset($attrs['name'])){
            $name = $attrs['name'];
        }else{
            throw new Core_Exception('У элемента должно быть имя');
        }
        
        if(array_key_exists('key',$attrs) AND isset($setting['key'])){
            $name = $setting['key'];
        }
        elseif(array_key_exists('cell',$attrs)){
            if(is_array($array)){
                    if($name = Arr::path($array,$attrs['cell'],NULL,$this->delimiter) AND is_array($value)){
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
    
    /*
     * Создает динамический блок в XML (открывает foreach)
     * 
     * @param array  массив для преобразования
     * @param object DOMNode
     * @param array  массив атрибутов
     * @return void
     */
    protected function type_dynamic($array,$child,$attrs,$setting){
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
        
        if(is_array($array)){
            if(!array_key_exists('one',$attrs)){
                foreach($array AS $key => $value){
                    $setting['key'] = $key;
                    $this->dom($value,$child,$setting);
                }
            }else{
                $this->dom($array,$child,$setting);
            }
        }
        
        
    }
    
    /*
     * Создает атрибут
     * 
     * @param array  массив для преобразования
     * @param object DOMNode
     * @param array  массив атрибутов
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
                throw new Core_Exception('Для использования ячейки массива, нужен массив');
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

    /*
     * Создаем текстовый узел
     * 
     * @param array  массив для преобразования
     * @param object DOMNode
     * @param array  массив атрибутов
     * @return void
     */
    protected function type_text($array,$child,$attrs,$setting){
        $value = '';
        
        //Если указана чейка массива, одна относится к массиву
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
    
    
     
    /*
     * Сортируем массив по указателю
     * 
     * @param array  массив для преобразования
     * @param string имя ячейки по которой нужно сортировать
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
    
    /*
     * Сортируем массив по значению, не изменяя его ключи
     * 
     * @param array  массив для преобразования
     * @param string имя ячейки по которой нужно сортировать
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
    
    /*
     * Callbeak метод сортировки массива.
     * [USE] Используется в методе sort_value
     * 
     * @param array ячейка массива
     * @param array ячейка массива для сравнения
     * @return void
     */
    protected function uasort($a,$b){
        return strnatcasecmp($a[$this->uasort_key],$b[$this->uasort_key]);
    }
    
    
    
    
    
    
    
    
    /*
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
        
        $xml = Admin_Template::factory(Registry::i()->template,'xsl/page/page.xsl');
        
        $xsl = new DOMDocument();
        $xsl->loadXML($xml);
        
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);
        
        

        
        var_dump($xslt->transformToXML($dom));
    }
    
    /*
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
    
    /*
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
    
    
    /*
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