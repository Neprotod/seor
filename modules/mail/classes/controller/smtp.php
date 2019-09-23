<?php defined('MODPATH') OR exit();

class Controller_Smtp_Mail{
    
    const VERSION = "1.0";
    
    const LE = "\r\n";
    
    /**
     * @var array кода ошибок
     */
    public $error_code = array(
                                "421" => "Обслуживание не доступно.",
                                "450" => "Требуемые почтовые действия, не предприняты.",
                                "451" => "Ошибка в обработке.",
                                "500" => "Синтаксическая ошибка, неправильная команда.",
                                "501" => "Синтаксическая ошибка в параметрах или переменных.",
                                "502" => "Несуществующая команда.",
                                "503" => "Неправильная последовательность команд.",
                                "504" => "Параметр Command, не осуществлен.",
                                "550" => "Требуемые действия, не предприняты: почтовый ящик недоступен (например, почтовый ящик, не найден, нет доступа).",
                                "551" => "Пользователь не местный; попробуйте еще раз.",
                                "552" => "Требуемые почтовые действия прервались: превышено распределение памяти.",
                                "553" => "Требуемые действия, не предприняты: имя почтового ящика, недопустимо (например, синтаксис почтового ящика неправильный).",
                                "554" => "Передача данных не удалась.",
                                );
    /**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
     *
     * @see http://tools.ietf.org/html/rfc2821#section-4.5.3.2
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * How long to wait for commands to complete, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     *
     * @var int
     */
    public $timelimit = 300;
    
    /**
     * The socket for the server connection.
     *
     * @var ?resource
     */
    protected $smtp_conn;
    
    /**
     * Email priority.
     * Options: null (default), 1 = High, 3 = Normal, 5 = low.
     * When null, the header is not set at all.
     *
     * @var int
     */
    public $priority = "3 (Normal)";
    
    /**
     * @var array настройки для сервера
     */
    public $config = array();
     
    /**
     * @var array настройки для пользователя
     */
    public $params = array();
    /**
     * Отправка письма.
     *
     * @param   array   параметры письма. 
     * @return  void
     */
    function __construct($params){
        if(!isset($params["timeout"]))
            $params["timeout"] = $this->timeout;
        $this->config = $params;
    }
    
    /**
     * Отправка письма.
     *
     * @param   array  параметры письма. 
     * @return  void
     */
    function send($params){
        foreach($params AS $key => $param){
            if(empty($param)){
                throw new Core_Exception("Поле <b>:param</b> пустое.",array(":param"=>$key),NULL,array(),TRUE);
            }
        }
        $this->params = $params;
        
        $header = $this->header();

        foreach($params["to"] AS $email){

            $pars_header = Str::__($header,array(
                    ":FROM_NAME" => $params["from"]["name"],
                    ":FROM" => $params["from"]["address"],
                    ":EMAIL_USER" => $email,
                    ":SUBJECT" => $params["subject"],
            ));

            $smtp_conn = fsockopen($this->config["hostname"], $this->config["port"],$errno, $errstr, $this->config["timeout"]);
            
            $head = array(
                "EHLO ".Core::$host,
                "AUTH LOGIN",
                base64_encode($this->config["username"]),
                base64_encode($this->config["password"]),
                "MAIL FROM:".$this->config["username"],
                "RCPT TO:".$email,
                "DATA",
                $pars_header.self::LE.$this->params["body"].self::LE.".",
                "QUIT"
            );
            
            $this->connector($head, $smtp_conn);
            /*exit;
            $data = $this->get_data($smtp_conn); 
            
            fputs($smtp_conn,"EHLO ".Core::$host.self::LE);
            $data = $this->get_data($smtp_conn);
            
            fputs($smtp_conn,"AUTH LOGIN".self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,base64_encode($this->config["username"]).self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,base64_encode($this->config["password"]).self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,"MAIL FROM:".$this->config["username"].self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,"RCPT TO:".$email.self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,"DATA".self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,$pars_header.self::LE.$this->params["body"].self::LE.".".self::LE);
            $data = $this->get_data($smtp_conn);

            fputs($smtp_conn,"QUIT".self::LE);
            $data = $this->get_data($smtp_conn);

            fclose($smtp_conn);*/
        }
    }
    
    /**
     * Читает ответ с сервера
     *
     * @param   mixed  ресурс
     * @return  array  сообщение и код от сервера
     */
    function get_data($smtp_conn){
        $data="";
        while($str = fgets($smtp_conn,515)){
            $data .= $str;
            if(substr($str,3,1) == " ") { break; }
        }
        $return = array();
        $return["message"] = $data;
        $return["code"] = substr($data,0,3);
        return $return;
    }
    
    /**
     * Создает заголовок для письма
     *
     * @return  array  загоолвки для письма
     */
    function header(){
        $header="Date: ".date("D, j M Y G:i:s")." +0700".self::LE;
        $header.="From: :FROM_NAME <:FROM>".self::LE;
        $header.="X-Mailer: ThreeMail 1.0".self::LE;
       // $header.="Reply-To: test <login@mail.ru>\r\n";
        $header.="X-Priority: ".$this->priority."\r\n";
        $header.="Message-ID: <".md5(date("YmjHis")+Core::$host)."@mail>".self::LE;
        $header.="To: :EMAIL_USER <:EMAIL_USER>".self::LE;
        $header.="Subject: :SUBJECT\r\n";
        $header.="MIME-Version: 1.0\r\n";
        
        $html = (!empty($this->params["html"]))?"html":"plain";
        
        $header.="Content-Type: text/$html; charset=UTF-8".self::LE;
        $header.="Content-Transfer-Encoding: 8bit".self::LE;
        
        return $header;
    }
    
    /**
     * Отправляет заголовки на сервер
     *
     * @param   array  заголовки для сервера
     * @param   mixed  ресурс
     * @return  void
     */
    function connector($head,$smtp_conn){
        foreach($head AS $h){
            $data = $this->get_data($smtp_conn);
            $this->check($data);
            fputs($smtp_conn,$h.self::LE);
        }
        fclose($smtp_conn);
    }
    
    /**
     * Проверка ответов сервеа
     *
     * @param   mixed  массив с ключом code либо просто код
     * @return  void
     */
    function check($code){
        if(is_array($code)){
            if(isset($code["code"])){
                $code = $code["code"];
            }else{
                Model::factory('exception','system')->set_xml(new Core_Exception("Массив не содержит ячейки <b>code</b>"),array('client'=>'true'));
                throw new Core_Exception("Массив не содержит ячейки <b>code</b>");
            }
            
        }
        
        if(isset($this->error_code[$code])){
            Model::factory('exception','system')->set_xml(new Core_Exception("Письмо не отправлено. Код ошибки сервера: <b>:code</b> :message",array(
                    ":code" => $code,
                    ":message" => $this->error_code[$code]
                    )),array('client'=>'true'));
                    
            throw new Core_Exception($this->error_code[$code]);
        }
    }
}
