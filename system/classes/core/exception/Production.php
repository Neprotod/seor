<?php defined('SYSPATH') OR exit();
/**
 * Исключение на подключение файла в клиентской версии
 *
 * @package    Tree
 * @category   Exceptions
 */
class Core_Exception_Production extends Core_Exception {

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = array(
        E_ERROR              => 'Fatal Error',
        E_USER_ERROR         => 'User Error',
        E_PARSE              => 'Parse Error',
        E_WARNING            => 'Warning',
        E_USER_WARNING       => 'User Warning',
        E_STRICT             => 'Strict',
        E_NOTICE             => 'Notice',
        E_RECOVERABLE_ERROR  => 'Recoverable Error',
    );
    public static $error_full = array();
    /**
     * Обработчик исключения
     * @param   string   error message
     * @param   array    translation variables
     * @param   integer  the exception code
     * @return  void
     */
    /*function __construct($message, array $variables = NULL, $code = 0)
    {

        // Pass the message to the parent
        parent::__construct($message, $code);
    }*/
    protected static $directory_error = 'error';
    public static $directory_error_xml = 'error_xml';
    
    static function handler(Exception $e){
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
            if ($e instanceof ErrorException){
                if (isset(Core_Exception::$php_errors[$code])){
                    // Use the human-readable error name
                    $code = Core_Exception::$php_errors[$code];
                }
            }

            // Create a text version of the exception
            $error = Core_Exception::text($e);

            // Убедится что заголовки отправлены
            /*
            if ( ! headers_sent())
            {
                // Убедитесь, что надлежащее http заголовок отправляется
                $http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;

                header('Content-Type: text/html; charset='.Kohana::$charset, TRUE, $http_header_status);
            }
            */
            // Включаем буфиринизацию
            ob_start();

            // Include the exception HTML
            if ($error_file = Core::find_file(Core_Exception_Production::$directory_error,'error-production')){
                include $error_file;
            }else{
                exit('Нет даже файла ошибки!');
            }

            // Выводим буфер
            echo ob_get_clean();
            
            // Создаем файл если его нет
            if (!$error_xml_file = Core::find_file(Core_Exception_Production::$directory_error_xml,'error','xml') OR (filesize($error_xml_file) == '0')){
                $dir = str_replace('_','/',Core_Exception_Production::$directory_error_xml);
                if(!is_dir(SYSPATH.$dir))
                    mkdir(SYSPATH.$dir);
                $fopen = fopen(SYSPATH.$dir.'/'.'error.xml',"a+");
                fwrite($fopen,"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n\r<errors>\n\r</errors>");
                fclose($fopen);
                // Снова загружаем файл
                $error_xml_file = Core::find_file(Core_Exception_Production::$directory_error_xml,'error','xml');
            }
            // Создаем дом модель
            $dom = new DOMDocument(); 
            
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
            if(!empty($errors)){
                foreach($errors as $error){
                    if($error->nodeType == 1){
                        $ids[] = $error->getAttribute('id');
                        $childs = $error->childNodes;
                        if(!empty($childs))
                            foreach($childs as $child){
                                if($bool === FALSE)
                                    continue;
                                if($child->nodeType == 1){
                                    switch($child->nodeName){
                                        case 'type': 
                                            $bool = ($type == $child->nodeValue)? FALSE : TRUE;
                                        break;
                                        
                                        case 'code': 
                                            $bool = ($code == $child->nodeValue)? FALSE : TRUE;
                                        break;
                                            
                                        case 'message': 
                                            $bool = ($message == $child->nodeValue)? FALSE : TRUE;
                                        break;
                                        
                                        case 'file': 
                                            $bool = ($file == $child->nodeValue)? FALSE : TRUE;
                                        break;
                                        
                                        case 'line': 
                                            $bool = ($line == $child->nodeValue)? FALSE : TRUE;
                                        break;
                                    }
                                } 
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
        }
        catch (Exception $e){
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();
            echo 1;
            // Покажите текст исключения
            echo Core_Exception::text($e), "\n";
            
            // Выход с состоянием ошибки
            exit(1);
        }
    }
    /**
     * Получите одну строку текста, представляющий исключение:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   object  Exception
     * @return  string
     */
    public static function text(Exception $e){
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }
    
    public function __construct($message = NULL, array $variables = NULL, $code = 0){

        // Set the message
        $message = STR::__($message, $variables);
        // Pass the message to the parent
        parent::__construct($message, intval($code));
    }
}
