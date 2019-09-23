<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Mail_Module{
    
    public $html = FALSE;
    public $to = array();
    public $from = array("address"=>FALSE,"name"=>NULL);
    public $subject = '';
    public $body = '';
    public $driver = '';
    
    public $config = array();
    
    function __construct(){}
    /**
     * Через что обрабатывать почту. SMTP, POP3 итп.
     *
     * @param   string  Тип драйвера, SMTP , POP3 итп.
     * @param   array   Дополнительные параметры, если например подключаемся к нестандартному SMTP серверу
     * @return  void
     */
    function driver($driver,$params = array()){
        $config = Core::config("email");
        
        $driver = strtolower($driver);
        
        if(!isset($config[$driver])){
            throw new Core_Exception("Нет драйвера <b>:driver</b>",array(":driver"=>$driver));
        }
        $params = Arr::merge($config[$driver], $params);
        
        $this->config = $params;
        
        $this->from["address"] = $params["username"];
        
        $this->driver = Controller::factory($driver,"mail",array($params));
    }
    /**
     * Использовать ли HTML в письме.
     *
     * @param   bool  TRUE значит будет использоваться HTML в письме.
     * @return  void
     */
    function isHTML($type = TRUE){
        $this->html = (bool)$type;
    }
    /**
     * Адреса клиентов.
     *
     * @param   string  почта клиента.
     * @return  void
     */
    function to($email){
        $this->to[$email] = $email;
    }
    /**
     * От кого пришло письмо.
     *
     * @param   string  почта отправителя, если не указать, будет использоваться ящик с SMTP
     * @param   string  от кого, приписка.
     * @return  void
     */
    function from($email = NULL,$name = NULL){
        if(isset($email) AND !empty($email))
            $this->from["address"] = $email;
        
        $this->from["name"] = $name;
    }
    /**
     * Тема письма.
     *
     * @param   string  тема письма
     * @return  void
     */
    function subject($text){
        $this->subject = $text;
    }
    
    /**
     * Тело письма. Если используется $this->view() этот метод не нужен.
     *
     * @param   string  тело письма
     * @return  void
     */
    function body($body){
        $this->body = $body;
    }
    /**
     * Тело письма, используется файл отображения в модуле mail.
     *
     * @param   string  файл отображения.
     * @param   array   параметры письма. 
     * @param   bool    true просмотреть вывод содержимого. 
     * @return  void
     */
    function view($file,$data = array(), $return = FALSE){
        $this->body = View::factory($file,"mail",$data);
        if($return)
            return $this->body;
        //exit;
        //$this->body = $body;
    }
    
    /**
     * Отправляем письмо используя драйвер.
     *
     * @return  mixed
     */
    function send(){
        $params = array();
        $params["html"] = $this->html;
        $params["to"] = $this->to;
        $params["from"] = $this->from;
        $params["subject"] = $this->subject;
        $params["body"] = $this->body;
        
        return $this->driver->send($params);
    }
}