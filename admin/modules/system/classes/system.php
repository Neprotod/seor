<?php defined('MODPATH') OR exit();

class System_Admin{

    function index(){}
    
    function __construct(){
        
    }
    
    function init(){
        $permission = Admin_Permission::i();
        
        // Сделать запуск по желанию (кнопкой).
        $permission->set_up();

        $permission->init();

        $permission->user_init(Auth_Admin::i()->user);
        /*$r = $permission->perm_controller("system","test","g");
        var_dump($r);
        exit;*/
        //exit;
        //Очищаем URL
        //$route = new Route();
        //$route->init();
        //Инициализировать пользователя
        
        //Тип даты
        Registry::i()->date_format = "%Y-%m-%d";
        //Инициализируем тему
        Registry::i()->template = "default";
        
        //Файл в теме
        $file_template = "index";
        //Корень темы
        Registry::i()->root = str_replace(array(DOCROOT,'\\'),'/',DESIGN_ADMIN) .'template/'. Registry::i()->template;

        //Регистрируем сессию.
        //$this->session = Session::instance();
        
        // Проверка сессии для защиты от xss
        if(!Request::check_session()){
            unset($_POST);
            Cookie::delete('session_admin');
            exit('Session expired');
        }

        //Настройки
        $this->settings();

        // Определяем тип адреса
        $this->route = Admin_Model::factory('route','system');
        $fonds = $this->route->init();

        Registry::i()->fonds = &$this->route->fonds;
        
        //Путь к теме отображения
        Registry::i()->template_view = $this->route->template_view();
        
        //Определяем какая тема используется
        Registry::i()->root_template = Model::factory('template','system')->get_template();
        
        
        //Генерируем меню.
        $menu = Admin_Model::factory("menu","system");
        
        //Определяем какой модуль подключить
        $content = '';
        if(isset($fonds['module'])){
            $result = $permission->perm_module($fonds['module'], $fonds['action']);
            if($result["permission"]){
               $content = Admin_Module::load($fonds['module'],$fonds['action'],$fonds['param']); 
            }else{
                $error = Module::factory("error",TRUE);
                $error->set("error","warning",array("title"=>"У вас нету прав!","message"=>"Вот так вот."));
                Registry::i()->errors = $error->output();
            }
            
        }else{
            //Загружаем стандартную модель отображения
            $content = Admin_Model::factory('default','system')->fetch();
            //Сохраняем для темы
            $fonds['module'] = 'default';
        }
        
        /*if(Request::in_ajax()){
            $test = array();
            $test["test"] = 200;
            print json_encode($test);
            return;
        }*/
        
        //Запрос для AJAX, только контент
        if(Request::in_ajax() OR isset($_GET['return'])){
            if(isset($_GET['ajax'])){
                $file_template = "ajax";
            }else{
                echo $content;
                return TRUE;
            }
        }
        //Собираем данные
        $date = array();
        $date['content'] = $content;
        $date['root'] = Registry::i()->root;
        $date['fonds'] = $this->route->default_fonds();
        $date['title'] = (isset(Registry::i()->title))? Registry::i()->title : NULL ;
        $date['meta_title'] = (isset(Registry::i()->meta_title))? Registry::i()->meta_title : $date['title'] ;
 
        $date['svg'] =  Admin_Model::factory('svg','system',array($date['root']));

        $date['menu'] = $menu->general();
        
        $date['errors'] = (isset(Registry::i()->errors))
                            ? Registry::i()->errors : NULL ;
        
        //Если есть пункты меню
        /*if(isset(Registry::i()->menu)){
            if(is_string(Registry::i()->menu))
                $date['menu'] = Admin_Template::factory(Registry::i()->template,'menu_'.Registry::i()->menu);
            elseif(is_array(Registry::i()->menu))
                $date['menu'] = Admin_Template::factory(Registry::i()->template,'menu_'.Registry::i()->menu['menu'],Registry::i()->menu);
        }*/
        
        //Загружаем тему
        echo Admin_Template::factory(Registry::i()->template,$file_template,$date);
    }
    
    protected function settings(){
        $settings = Admin_Query::i()->sql("settings.get",NULL,"name");
        
        Registry::i()->settings = $settings;
    }
    
    public function customer($type = "view"){
        // Активное меню
        Registry::i()->active_menu = "custom";
        
        $permission = Admin_Permission::i()->perm_module("system","customer");
        $error = Module::factory("error",TRUE);
        $menu = Admin_Model::factory("menu","system");
        $xml = Module::factory("xml",TRUE);
        
        if(!$permission["permission"] OR (isset($permission["rule"][$type]) AND !$permission["rule"][$type])){
            $error->set("error","warning",array("title"=>"У вас нету прав!","message"=>"Вот так вот."));
            Registry::i()->errors = $error->output();
            return FALSE;
        }
        if($error->success()){
            Registry::i()->errors = $error->output();
        }
        
        
        
        Registry::i()->title = "Настройки";
        
        $date = array();
        
        $menu->attach("system_customer","view","Отображение","");
        $menu->attach("system_customer","update","Изменение","update");
        $menu->attach("system_customer","delete","Удаление","delete");
        
        $array_menu = $menu->get_array();
        
        $date["menu"] = $menu->get($permission,"view");
       
        // Генерируем форму
        //var_dump("<pre>",Registry::i()->settings);
        //exit;
        $tech = array(
            "session_id" => Session::i()->id(),
            "action" => $array_menu[$type]["link"],
            array("svg" => Admin_Model::factory('svg','system',array(Registry::i()->root))->get_svg("plus"))
        );
        
        $settings = Admin_Query::i()->sql("settings.all",NULL,"name");

        if(Request::method("post")){
            $message = array();
            $sql = Admin_Model::factory("sql","system");
            if($type == strtolower("update")){
                $new_settings = Request::post("settings");
                $insert = Request::post("insert");
                
                // Находим пустые значения и заменяем их на NULL
                $null = Arr::value_return("",$new_settings);
                $null = Arr::fill_recurs($null, NULL);

                // Сливаем массив
                $new_settings = Arr::merge($new_settings, $null);
        
                // Формирует строку для обновления
                $update = Arr::array_diff_assoc($new_settings,$settings);
                
                $ids = Arr::intersect_match($settings,array_keys($update));
                $ids = Arr::search("id",$ids);
                $merge = Arr::merge($update,$ids);
                
                $valid = array(
                        "id" => array(
                            "type" => "int",
                        ),
                        "value" => array(
                            "type" => "str",
                            "required" => TRUE,
                        ),
                        "name" => array(
                            "type" => "str",
                            "required" => TRUE,
                        )
                );
                $validator = Model::factory("validator","system");
                if($merge){
                    // Создаем строку для тестов, нам нужно иметь имя ячейки как ID
                    // что бы было правильное имя у active-role
                    $ids = Arr::search("id",$settings);
                    $to_merge = Arr::intersect_key($ids,array_keys($merge));
                    $ids = Arr::merge($merge,$to_merge);
                    $ids = Arr::value_key($ids,"id");
        
                    $t = $validator->valids($valid,$ids);
                    
                    // Если есть ошибки
                    if($t){
                        $settings = Arr::merge($settings,$merge);
                        
                        $error->set("error","warning",array("message"=>"Неправильно заполнено поле"));

                        foreach($error->role_array($t) AS $value){
                            $error->set("error","danger",array("role"=>$value,"select"=>1));
                        }  
                        
                        Registry::i()->errors = $error->output();
                        
                    }
                    else{
                        // Ошибок нет, заполняем
                        foreach($merge AS $key => $value){
                            $id = $value["id"];
                            unset($value["id"]);
                            
                            $set = $sql->where(",",$value);

                            // Вставляем в базу
                            Admin_Query::i()->sql("update",array(
                                                                ":table" => "settings",
                                                                ":set" => $set,
                                                                ":id" => $id,
                                                                ));
                        }
                        $message[] = "Данные обновлены";
                    }
                }
                
                // Если не пустой, добавляем новую запись.
                if(!Arr::emptys($insert)){
                    if($t = $validator->valids($valid,$insert)){
                        $t = array("insert"=>$t);
                        
                        $error->set("error","warning",array("message"=>"Неправильно заполнено поле"));
         
                        foreach($error->role_array($t) AS $value){
                            $error->set("error","danger",array("role"=>$value,"select"=>1));
                        }  
                        
                        Registry::i()->errors = $error->output();
                        $tech += $insert;
                    }else{
                        $null = Arr::value_return("",$insert);
                        $null = Arr::fill_recurs($null, NULL);

                        // Сливаем массив
                        $insert = Arr::merge($insert, $null);
                        
                        $insert = current($insert);
                        
                        Admin_Query::i()->sql("settings.insert",array(
                                                            ":name" => $insert["name"],
                                                            ":value" => $insert["value"],
                                                            ":title" => $insert["title"],
                                                            ":description" => $insert["description"],
                                                            ":status" => $insert["status"],
                                                            ));
                                                            
                         $message[] = "Было добавлены настройки";
                    }
                }
                
                
            }
            elseif($type == strtolower("delete")){
                if($ids = Request::post("delete")){
                    $ids = array_keys($ids);
                    $ids = $sql->insert_string($ids);

                    Admin_Query::i()->sql("delete",array(
                                                    ":table" => "settings",
                                                    ":insert" => $ids,
                                                    ":where" => "id",
                                                    ));
                    $message[] = "Значение было удалено";
                }
                
            }
        }
        if(isset($message) AND !empty($message)){
            Cookie::set("success",serialize($message));
            Request::redirect(Url::root(FALSE));
        }
        $date["content"] = NULL;
        
        $xml_pars = "admin_template|default::customer_customer";
        // Подключаем отображение
        if($type == "update"){
            $form =  $xml->preg_load($settings,$xml_pars,"admin_template|default::customer_update",$tech);
            
            $date["content"] = $form; 
        }else if($type == "delete"){
            $date["content"] =  $xml->preg_load($settings,$xml_pars,"admin_template|default::customer_delete",$tech);
        }else{
            $date["content"] =  $xml->preg_load($settings,$xml_pars,"admin_template|default::customer_view",$tech);
        }
        
        return Admin_Template::factory(Registry::i()->template,"content_customer_customer",$date);
    }
}