<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class User_Module{

    const VERSION = '1.0.0';
    
    /**
     * @var bool если в TRUE значит авторизация уже была проведена в прошлом
     */
    static $auth = FALSE;
    
    function __construct(){
        $this->method = Controller::factory("method","user");
        $this->xml = Module::factory("xml",TRUE);
    }
    
    
    /**
     * Данный метод определяет авторизирован ли пользователь, отправить его на страницу авторизации или перенаправить на пользовательский модуль. 
     *
     */
    function fetch(){
        //$data = array("type" => "user");
        $data = array();
        Registry::i()->data = array();
        
        Registry::i()->data["type"] = "user";
        // Проверка авторизации
        if(!$this->auth()){
            $login = Controller::factory("login","user");
            $data["content"] = $login->login();
        }else{
            // Обнулением, если пользователь хочет зарегистрироваться еще раз
            if(Registry::i()->founds["url"] == "registr"){
                $this->method->logout();
                Request::redirect(Url::site("",NULL),302);
            }
            
            $data["content"] = $this->rout();
            /*
            $user = Registry::i()->user;
            if(empty($user["type"])){
                $data["content"] = Controller::factory("regist","user")->fetch();
            }else{
                
            }*/
        }
        
        // Определяем подключаемый файл
        $template = Model::factory('template','system');
        $path = $template->path(Registry::i()->data);

         // Определяем стили
        $style = Model::factory('style','system');
        $style->init(Registry::i()->data);
        
        //Вывод содержимого
        return Template::factory(Registry::i()->template['name'],$path,$data);
    }
    
    
    /**
     * Авторизация пользователя
     *
     */
    function auth(){
        if(self::$auth){
            // Авторизация было уже проведена ранее
            return TRUE;
        }else{
            // Нет cookie значит авторизация не проводилась ранее
            if(Cookie::get("token")){
                if($token = $this->method->token()){
                    self::$auth = TRUE;

                    // Установка сессии пользователя
                    $this->session = Session::instance("db");
                    Registry::i()->session = $this->session;

                    // Определяем часовой пояс пользователя
                    $this->time_zone();
                    
                    // Проверка и съем дня использования аккаунта
                    $this->method->took_days(Registry::i()->user);
                    
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Получить переменную с пользователем.
     *
     */
    function get(){
        return (isset(Registry::i()->user))
            ? Registry::i()->user
            : array();
    }
    /**
     * Выйти пользователем.
     *
     */
    function logout(){
        $this->method->logout();
        Request::redirect(Url::site("account",TRUE),302);
    }
    
    /**
     * Метод перенаправляет на пользовательский модуль
     *
     */
    protected function rout(){
        $user = Registry::i()->user;
        $module = "user";
        $pars = Model::factory("route","system")->parse_url(Registry::i()->founds["url"]);
        
        if(Registry::i()->founds["id_content_type"]){
            Registry::i()->data["content_type"] = Registry::i()->founds["id_content_type"];
        }
        
        if(!empty(Registry::i()->founds["id_table"])){
            Registry::i()->data = $this->method->get_user_page(Registry::i()->founds);
        }

        if(empty(Registry::i()->founds["id_table"])){
            Registry::i()->data["file_name"] = $pars["module"];
        }

        //if(empty(Registry::i()->founds["id_table"])){
            // Создаем путь к модели
            $path = '';
            if($user["employer"]){
                $path = "employer_";
                
                if($user["legal"]){
                    $path .= "legal_";
                }else{
                    $path .= "nolegal_";
                }
            }else{
                $path = "user_";
            }
            
            $path .= $pars["module"];
            
            try{
                return Controller::load($path, $module, $pars["action"], $pars["param"]);
            }catch(Exception $e){
                if(Core::$selected_mode > 2){
                    Core_Exception::handler($e);
                }else{
                    // Обрабатываем ошибку
                    Core_Exception::client($e);
                    
                    Model::factory('error','system')->error();
                }
            }
        //}
    }
    
    function header(){
        $data = Registry::i()->user;

        $xml_pars = "template|default::user_header";
        $xsl_pars = "template|default::user_header";
        
        $menu = (isset($data["employer"]) AND $data["employer"] == 1)
                        ?"employer"
                        :"worker";
        
        $xml_menu = "module|user::menu";
        $xsl_menu = "module|user::menu_".$menu;

        // Создаем логотипы
        $image = Module::factory("image",TRUE);
        $logo_path = $this->method->media_user_path() . "logo";
        $logo_resize = $logo_path . "/resize";
        
        $image_settings = array(
            "original" => $logo_path,
            "no_image" => Registry::i()->settings["no_image_user"],
        );
        
        $image_param = array(
            "width" => 40,
            "resizeHeight" => 40,
            "offSetX" => 0,
            "offSetY" => 0,
            "resizeDir" => $logo_resize,
        );
        $logo = $data["logo"];
        $data["logo"] = array();
        
        $data["logo"]["40"] = $image->resize($logo,$image_param,$image_settings);
        
        $image_param["width"] = 80;
        $image_param["resizeHeight"] = 80;
        $data["logo"]["80"] = $image->resize($logo,$image_param,$image_settings);
       
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(FALSE),
        );
        
        $data["menu"] = $this->xml->preg_load(array(),$xml_menu,$xsl_menu,$tech);
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    /**
     * Загружает настройки с базы данных
     *
     * @return void 
     */
    function registr(){
        return 1;
    }
    /**
     * Загружает настройки с базы данных
     *
     * @return void 
     */
    function time_zone(){
        // Создаем образец
        if(isset($_COOKIE["UTC"])){
            $preg = "/^\+[0-9]{2}:[0-9]{2}$/";
            
            if(preg_match($preg, $_COOKIE["UTC"])){
                Registry::i()->time_zone = $_COOKIE["UTC"];
                
                Query::i()->sql("UTC",array(":UTC"=>$_COOKIE["UTC"]),NULL);
                
                Registry::i()->session->set("UTC",$_COOKIE["UTC"]);
            }else{
                Model::factory('exception','system')->set_xml(new Core_Exception("Неправильная cookie с именем UTC значение <b>:cook</b> не прошло регулярное выражение :preg у пользователя с ID <b>:id</b>",array(
                    ":cook" => $_COOKIE["UTC"],
                    ":preg" => $preg,
                    ":id" => isset(Registry::i()->user["id"])?Registry::i()->user["id"]:0,
                )),array('client'=>'true'));
                
                Cookie::delete("UTC");
            }
        }else{
            if(Registry::i()->session->get("UTC")){
                Query::i()->sql("UTC",array(":UTC"=>Registry::i()->session->get("UTC")),NULL);
            }
        }
    }
}