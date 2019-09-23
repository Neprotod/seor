<?php defined('MODPATH') OR exit();

/**
 * Модуль для отображения ошибок
 */
class Error_Module{
    
    /**
     * @var array все сообщения которые нужно записать в COOKIE
     */
    protected static $cookie = array();
    /**
     * @var array все сообщения
     */
    protected $alert = array();
    
    /**
     * @var array путь к модулю
     */
    protected $path = array();
    
    /**
     * @var object все сообщения
     */
    protected $xml = array();
    
    /**
     * @var array начальная ячейка массива
     */
    protected $default = array('error','message');
    
    /**
     * @var array типы ячейки
     */
    protected $type = array(
                'error' => array('error','warning','danger'),
                'message' => array('success','info','primary','secondary','light','dark')
            );

    /**
     * @var array ячейки для которых нужна ячейка role
     */
    protected $key = array('tooltip','select','valid','select');
    /**
     * @var array все поля
     */
    protected $all_key = array('title','message','role');
    
    /**
     * Конструктор, загружает из регистра данные в класс
     *
     * @return void
     */
    function __construct(){
        //Сообщения и ошибки
        $this->alert = &Registry::i()->alert;
        
        //Основной модуль преобразования
        $this->xml = Module::factory('xml',TRUE);
        
        //Относительный путь к модулю
        $this->path = Module::mod_path('error',TRUE);
    }

    /**
     * Загружает данные в массив для дальнейшего преобразования в XML
     *
     * [!]  set([string $default [, string $type]], array $setting)
     *      Допустимые значения:
     *            [string $default] - error | message
     *            [string $type]     - для $default[error] 
     *                                      error | warning | danger | success | info 
     *                                      success | info | primary | secondary | light | dark 
     *                                        деятельно смотри в массив $this->type
     *            array  $fond     - ключи массива title, message, role, tooltip, select, valid
     *          Работает следующим образом:
     *              $this->set('error','error',
     *                                  array(
     *                                          'title'   => "Заголовок",
     *                                          'message' => "сообщение ошибки",
     *                                          'role'    => 'name',
     *                                          'valid'   => 'new_name',
     *                                          'tooltip' => 'Это подсказка',
     *                                          'select'  => '1'
     *                                       ));
     *              Все эти значения используются в JS в файле error.js у этого модуля:
     *
     *              role    - нужно только для JS если у элемента есть active-role="name" 
     *                        то он задаст элементу еще один класс с именем $type
     *              valid   - переназначает класс у элементов с active-role
     *              select  - выделять ли элемент
     *              tooltip - подсказка, у родительского или этого же элемента должен 
     *                        быть класс control, что бы подсказка правильно добавилась
     * @return void
     */
    function set(){
        //Правильное количество
        $num_arg = func_num_args();
        if($num_arg < 3 OR $num_arg > 4){
            throw new Core_Exception('Не правильно заполненные аргументы <b>set()</b>. Структура: <b>set</b>([string <i>$default</i>(message|error)[, string <i>$type</i>]], array <i>$setting</i>, $marker)');
        }
        
        $args = func_get_args();
        if($num_arg == 4){
            $mark = array_pop($args);
        }else{
            $mark = FALSE;
        }
        //Ключ первой ячейки
        $default = ($num_arg > 1)
            ? array_shift($args)
            : 'error';
        
        //Проверяем на основную ячеку массива
        if(!in_array($default,$this->default)){
            throw new Core_Exception('Пришло значение <b>:arg</b>. В методе set() первое значение, может быть только (<b><i>:default</i></b>)',array(':arg'=>$default,':default'=>implode(', ',$this->default)));
        }
        
        $type = NULL;
        $fond = NULL;
        
        //Заполняем ячейки
        foreach($args AS $arg){
            if(is_array($arg)){
                $fond = array_change_key_case($arg);
            }else{
                $type = $arg;
            }
        }
        
        //Проверяем на тип
        if(!in_array($type,$this->type[$default]) AND !is_null($type)){
            throw new Core_Exception('Пришло тип <b>:arg</b>. Допустимые типы для <b>:default</b> это (<b><i>:type</i></b>) и <b>NULL</b>',array(':arg'=>$type,':default'=>$default,':type'=>implode(', ',$this->type[$default])));
        }
        
        //Проверяем на массив
        if(!is_array($fond)){
            throw new Core_Exception('Последний аргумент в <b>set()</b> должно быть <b>array</b>');
        }
        
        // Проверяем на лишние поля
        $key_test = Arr::merge($this->all_key, $this->key);

        if($key = array_diff(array_keys($fond),$key_test)){
            throw new Core_Exception('Лишняя ячейка <b>(:key)</b>. Допустимые ячейки <b>(:all_key)</b>',array(':key'=> implode(', ',$key),":all_key"=>implode(', ',$key_test)));
        }
        
        //Если есть tooltip, select, valid но нет role это не имеет смысла
        if($key = Arr::intersect_key($fond,$this->key) AND !isset($fond['role'])){
            throw new Core_Exception('Для полей  <b>(:key)</b> обязательным является поле <b>role</b>',array(':key'=> implode(', ',array_keys($key))));
        }
        
        $fond['type'] = $type;
        
        if(isset($this->alert[$default]))
            foreach($this->alert[$default] AS $test){
                if($test == $fond){
                    return;
                }
            }
        if($mark){
            self::$cookie[$default][] = $fond;
        }else{
            $this->alert[$default][] = $fond;
        }
    }
    /**
     * Выдает HTML в виде готовых ошибок и сообщений
     *
     * @param  string $xsl путь к XSL для функции xml::preg_load
     * @return string      HTML строка
     */
    function output($xsl = NULL, $alert = NULL){
        $xml = "module|error::error";
        if($xsl === NULL)
            $xsl = "module|error::error";
        
        if(!$alert){
            $alert = $this->alert;
            
            // Очищаем, что бы не смешивать.
            $this->alert = array();
        }
        
        if(!empty($alert)){
            ksort($alert);
            
            $alert = $alert;
            
            return $this->xml->preg_load($alert,$xml,$xsl);
        }
        
        return FALSE;
    }
    /**
     * Очистить вывод
     *
     * @return void
     */
    function clear(){
        $this->alert = array();
        return TRUE;
    }
    /**
     * Выдает HTML в виде готовых ошибок и сообщений
     *
     * @param  string $xsl путь к XSL для функции xml::preg_load
     * @return string      HTML строка
     */
    function success(){
       if($success = Cookie::get("success")){
            try{
                if($serialize = Arr::is_serialized($success)){
                    foreach($serialize AS $value){
                        if(!is_array($value)){
                            $this->set("message","success",array("message"=>$value));
                        }else{
                            $message = array();

                            foreach($value AS $key => $val){
                                
                                $message[$key] = $val;
                               
                            }
                           
                            $this->set("message","success",$message);
                        }
                    }
                }else{
                    $this->set("message","success",array("message"=>$success));
                }
                Cookie::delete("success");
                return TRUE;
            }catch(Exception $e){
                Cookie::delete("success");
                throw new Core_Exception($e->getMessage());
            }
        }else{
            return FALSE;
        }
    }
    /**
     * Возвращаем из COOKIE
     *
     * @param  bool   $xsl путь к XSL для функции xml::preg_load
     * @return mixed  HTML строка
     */
    function cookie($out = TRUE){
       if($errors = Cookie::get("error_cookie")){
           $errors = unserialize($errors);
           Cookie::delete("error_cookie");
           if($out){
               return $this->output($xsl = NULL, $errors);
           }else{
               if(empty($this->alert))
                   $this->alert = array();
               $this->alert = Arr::merge($this->alert,$errors);
               return TRUE;
           }
        }else{
            return FALSE;
        }
    }
    /**
     * Сохраняем в cookie
     *
     * @param  string $xsl путь к XSL для функции xml::preg_load
     * @return string      HTML строка
     */
    function save_cookie(){
       if($errors = $this->alert){
           Cookie::set("error_cookie",serialize($errors));
           return TRUE;
        }else{
            return FALSE;
        }
    }
    /**
     * Генерирует роли по ключам массива
     *
     * @param  array   
     * @param  bool   одиночный или нет
     * @return array  массив подготовленных строк
     */
    function role_array($role, $single = TRUE){
        $arr = array();
        while(!empty($role)){
            $found = array();
            $arr[] = &$found;
            $this->array_role($role,$found);
            unset($found);
        }

        $string_return = array();
        if(!Arr::emptys($arr)){
            foreach($arr AS $key => $value){
                $str = $this->transform_role($value["path"]);
                if(is_array($str) AND !empty($str)){
                    $str = current($str);
                    if($single){
                        $string_return[$key] = $str;
                    }else{
                        $string_return[$key]["path"] = $str;
                        $string_return[$key]["type"] = $value["type"];
                    }
                }
            }
        }
        return $string_return;
    }
    /**
     * Генерирует массив для дальнейшего преобразования в строку.
     *
     * @param  string $xsl путь к XSL для функции xml::preg_load
     * @return string      HTML строка
     */
    protected function array_role(&$role,&$found,&$current = array()){
        if(empty($found)){
            $found["path"] = &$current;
        }
        reset($role);
        $key = key($role);
        if(is_array($role[$key])){
            $current[$key] = array();
            $this->array_role($role[$key], $found, $current[$key]);
            if(Arr::emptys($role[$key])){
                unset($role[$key]);
            }
        }else{
            $found["type"][$key] =  $role[$key];
            unset($role[$key]);
        }
    }
    /**
     * Генерирует роли по ключам массива
     *
     * @param  string $xsl путь к XSL для функции xml::preg_load
     * @return string      HTML строка
     */
    protected function transform_role($role){
        $arr = array();
        
        foreach($role AS $name => $value){
            $string = "";
            $arr[] = &$string;
            $string = $name."_";
            if(is_array($value)){
                $found = $this->transform_role($value);
                $string .= implode("_",$found);
            }else{
                $string = "";
            }
            $string = rtrim($string,"_");
            unset($string);
        }
        return $arr;
    }
    
    
    /**
     * Выдает нужный css и javasript для отображения ошибки
     *
     * @param  bool   нужен ли bootstrap css
     * @param  bool   нужен ли error.js
     * @return string HTML строка
     */
    function header($bootstrap = TRUE, $error = TRUE, $error_js = FALSE){
        $header = '';
        if($bootstrap)
            $header .= '<link rel="stylesheet" type="text/css" href="'.$this->path.'css/mini.bootstrap.css" />'."\r\n";
        if($error)
            $header .= '<script type="text/javascript" src="'.$this->path.'js/error.js" ></script>'."\r\n";
        if($error_js)
            $header .= '<script type="text/javascript" src="'.$this->path.'js/bootstrap.js" ></script>'."\r\n";
        
        return $header;
    }
}