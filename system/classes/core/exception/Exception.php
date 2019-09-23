<?php defined('SYSPATH') OR exit();
/**
 * Tree exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Tree
 * @category   Exceptions
 */
class Core_Exception_Exception extends Exception {

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
     * @var  array  Дополнительные параметры, для вывода ошибки клиенту.
     */
    protected $error_role = array();
    
    
    /**
     * @var  string  директория ошибки
     */
    protected static $directory_error = 'error';
    
    /**
     * @var  string  для сохранения XML ошибки.
     */
    public static $directory_error_xml = 'error_xml';
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
    static function error_dir(){
        return self::$directory_error;
    }
    static function handler(Exception $e){
        try{
            // Получите информацию исключения
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();
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
            if(Core::$selected_mode == Core::PRODUCTION OR Core::$selected_mode == Core::STAGING){
                Model::factory('exception','system')->set_xml($e,array('client'=>'true'));

                if($error_file = Core::find_file(Core_Exception_Production::error_dir(),'error-production')){
                    include $error_file;
                }else{
                    echo '<b>Критическая ошибка.</b> Разработчики уведомлены.';
                }
            }else{
                // Include the exception HTML
                if ($error_file = Core::find_file(Core_Exception::$directory_error, 'error')){
                    include $error_file;
                }else{
                    exit('Нет даже файла ошибки!');
                }
            }
            // Выводим буфер
            echo ob_get_clean();
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
    
    /**
     * Получите одну строку текста, представляющий исключение:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   object  Exception
     * @return  string
     */
    public function error_reporting(){
        if(Core::$selected_mode == Core::DEVELOPMENT){
            self::handler($this);
        }else{
            $error = array(
                'message' => $this->getMessage()
            );
            
            $default = 'error';
            $error_type = 'error';
            
            $production_massage = $error['message'];
            $production_role = array();
            $system = FALSE;
            
            $code = $this->getCode();
            
            if($code == E_ERROR OR $code == E_USER_ERROR){
                    $system = TRUE;
                    $production_role['title'] = 'Критическая ошибка.';
                    $production_massage = 'Разработчики уже уведомлены и решат проблему в кротчайший срок.';
            }
            elseif($code == E_WARNING OR $code == E_USER_WARNING){
                    $system = TRUE;
                    $error_type = 'warning';
                    $production_role['title'] = 'Незначительная ошибка.';
                    $production_massage = 'Могут быть легкие искажения в отображении. Разработчики уже уведомлены.';
            }
            elseif($code == E_NOTICE OR $code == E_USER_NOTICE OR $code == E_STRICT){
                    $system = TRUE;
                    $error_type = 'info';
                    $default = 'message';
                    $production_role['title'] = 'Совсем незначительная ошибка.';
                    $production_massage = 'Можете продолжать работу. Разработчики уже уведомлены.';
            }
            
            if(Core::$selected_mode == Core::PRODUCTION){
                if(!empty($production_role))
                    $this->error_role = $production_role;
                if(!empty($production_massage))
                    $error['message'] = $production_massage;
                
            }
            if(Core::$selected_mode == Core::TESTING){
                $file = $this->getFile();
                $line = $this->getLine();
                $error['message'] .= "<br /> <b>В файле:</b> {$file} <br /> <b>На линии:</b> {$line}";
            }
            
            //Объедением сообщение с дополнительными указаниями.
            $error = Arr::merge($error, $this->error_role);
            
            //Если содержит системную ошибку.
            if($system)
                $error['system'] = $system;
            
            
            //Определяем как выводит ошибку.
            try{
                if(class_exists('Module')){
                    //Для разработчика
                    Model::factory('exception','system')->set_xml($this,array('client'=>'true'));
                    
                    //Для пользователя
                    Module::factory('error',TRUE)->set($default,$error_type,$error);
                }else{
                    throw new Core_Exception('Нет модулей для обработки данных');
                }
            }catch(Exception $e){
                if(Core::$selected_mode == Core::PRODUCTION OR Core::$selected_mode == Core::STAGING){
                    echo '<b>Не обрабатываемая ошибка.</b> Разработчики должны быть уведомлены. Но желательно сообщите им лично о ошибке.';
                }else{
                    throw new Core_Exception($error['message'].'<br />'.$e->getMessage());
                }
            }
        }
    }
    /**
     * Обработчик исключения
     * @param   string   $message   error message
     * @param   array    $variables translation variables
     * @param   integer  $code      the exception code
     * @param   array    $role      дополнительный параметр для вывода ошибки клиенту
     * @return  void
     */
    public function __construct($message, array $variables = NULL, $code = NULL,$role = array(),$client = FALSE){
        if(is_null($code)){
            $code = 0;
        }
        
        $this->error_role = $role;
        
        // Установка сообщения
        $message = STR::__($message, $variables);
        
        if($client){
            Model::factory('exception','system')->set_xml(new Core_Exception($message, NULL, $code, $role),array('client'=>'true'));
        }
        
        // Pass the message to the parent
        parent::__construct($message, intval($code));
    }
}
