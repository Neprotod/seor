<?php defined('MODPATH') OR exit();

/**
 * Обработчик исключений
 * 
 * @package    module/system
 * @category   exception
 */
class Model_Exception_System{
    
    /**
     * @var string начальный элемент
     */
    public $root_element = "<errors />";

    /**
     * Сохраняет ошибку в XML формате
     * 
     * @param  object $e      Exception
     * @param  array  $client массив дополнительных атрибутов name=>value
     * @return bool           TRUE если все хорошо либо исключение
    */
    function set_xml(Exception $e,array $client = array('client'=>'true')){
        try{
            // Получите информацию исключения
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();
            $date = date("d-m-Y H:i:s O",time());
            // Получить след исключения
            $trace = $e->getTrace();
            if ($e instanceof Core_Exception){
                if (isset(Core_Exception::$php_errors[$code])){
                    // Use the human-readable error name
                    $code = Core_Exception::$php_errors[$code];
                }
            }

            // Create a text version of the exception
            $error = Core_Exception::text($e);
            
            // Создаем файл если его нет
            if (!$error_xml_file = Core::find_file(Core_Exception::$directory_error_xml,'error','xml') OR (filesize($error_xml_file) === 0)){
                $dir = str_replace('_','/',Core_Exception::$directory_error_xml);
                if(!is_dir(SYSPATH.$dir))
                    mkdir(SYSPATH.$dir);
                $fopen = fopen(SYSPATH.$dir.'/'.'error.xml',"w+");
                /*fwrite($fopen,"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n\r<errors></errors>");*/
                fwrite($fopen,$this->root_element);
                fclose($fopen);
                // Снова загружаем файл
                $error_xml_file = Core::find_file(Core_Exception::$directory_error_xml,'error','xml');
            }else{
                //Редкий случай нарушения целостности файла
                $XMLReader = new XMLReader();
                if(!@$XMLReader->open($error_xml_file) OR !@$XMLReader->next()){
                    file_put_contents($error_xml_file,$this->root_element);
                }
            }
            // Создаем дом модель
            $dom = new DOMDocument();
            
            //Убираем лишние отступы
            $dom->preserveWhiteSpace = FALSE;
            
            $dom->load($error_xml_file);
            /*$doc->formatOutput = true;
            $doc->encoding = "UTF-8";*/
            // Корневой элемент
            $root = $dom->documentElement;
            
            // Берем узлы ошибок
            $errors = $dom->getElementsByTagName('error');
            $ids = array();
            
            // Индикатор есть ли такая ошибка, проверяет до первого несовпадения
            $bool = TRUE;
            $test = array();
            if(!empty($errors)){
                foreach($errors as $error){
                    $test = array();
                    if($error->nodeType == 1){
                        $ids[] = $error->getAttribute('id');
                        $childs = $error->childNodes;
                        if(!empty($childs))
                            foreach($childs as $child){
                                if($child->nodeType == 1){
                                    switch($child->nodeName){
                                        case 'type':
                                            $test['type'] = ($type == $child->nodeValue)? TRUE : FALSE;
                                        break;
                                        
                                        case 'code': 
                                            $test['code'] = ($code == $child->nodeValue)? TRUE : FALSE;
                                        break;
                                            
                                        case 'message': 
                                            $test['message'] = ($message == $child->nodeValue)? TRUE : FALSE;
                                        break;
                                        
                                        case 'file': 
                                            $test['file'] = ($file == $child->nodeValue)? TRUE : FALSE;
                                        break;
                                        
                                        case 'line': 
                                            $test['line'] = ($line == $child->nodeValue)? TRUE : FALSE;
                                        break;
                                    }
                                } 
                            }
                    }
                    if(in_array(TRUE,$test)){
                        
                        foreach($test AS $value){
                            $continue = FALSE;
                            if($value === FALSE){
                                $continue = TRUE;
                                $bool = TRUE;
                                break;
                            }
                        }
                        if($continue === FALSE){
                            $bool = FALSE;
                            break;
                        }
                        
                    }
                }
                /*Создаем новый элемент*/
            }
            if(empty($ids))
                $ids[] = 0;
            if($bool === TRUE){
                // Основной элемент ошибки
                $create = $dom->createElement("error");
                $create->setAttribute('id',intval(end($ids))+1);
                // Дополнительные атрибуты
                if(!empty($client))
                    foreach($client as $attr => $value){
                        if(is_string($value))
                            $create->setAttribute($attr,$value);
                    }
                $error = $root->appendChild($create);
                
                // Тип ошибки
                $type_element = $dom->createElement("type");
                $text = $dom->createTextNode($type);
                $type_element->appendChild($text);
                $error->appendChild($type_element);
                
                // Код ошибки
                $code_element = $dom->createElement("code");
                $text = $dom->createTextNode($code);
                $code_element->appendChild($text);
                $error->appendChild($code_element);
                
                // Сообщение ошибки
                $message_element = $dom->createElement("message");
                $text = $dom->createCDATASection($message);
                $message_element->appendChild($text);
                $error->appendChild($message_element);
                
                // Файл в котором ошибка
                $file_element = $dom->createElement("file");
                $text = $dom->createTextNode($file);
                $file_element->appendChild($text);
                $error->appendChild($file_element);

                // Линия на которой ошибка
                $line_element = $dom->createElement("line");
                $text = $dom->createTextNode($line);
                $line_element->appendChild($text);
                $error->appendChild($line_element);

                // Класс в котором ошибка
                $class_element = $dom->createElement("class");
                $text = $dom->createTextNode((isset($trace[0]['class']))?$trace[0]['class']:'');
                $class_element->appendChild($text);
                $error->appendChild($class_element);
                
                // Кусок кода с ошибкой
                $debug_element = $dom->createElement("debug");
                $text = $dom->createCDATASection(Debug::source($file, $line));
                $debug_element->appendChild($text);
                $error->appendChild($debug_element);
                
                // Полный путь до ошибки
                $trace_element = $dom->createElement('trace');
                $text = $dom->createCDATASection(convert_uuencode(serialize((array)$trace)));
                $trace_element->appendChild($text);
                $error->appendChild($trace_element);
                
                // Дата ошибки
                $date_element = $dom->createElement('date');
                $text = $dom->createTextNode($date);
                $date_element->appendChild($text);
                $error->appendChild($date_element);
                
                $dom->save($error_xml_file);
            
            }
            return TRUE;
        }catch (Exception $e){
            throw new Core_Exception($e->getMessage());
        }
    }
}