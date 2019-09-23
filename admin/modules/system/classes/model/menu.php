<?php defined('MODPATH') OR exit();


class Model_Menu_System_Admin{
    
    protected $xml;
    protected $svg;
    
    protected $dop_menu = array();
    
    function __construct(){
        $this->xml = Module::factory("xml",TRUE);
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function general(){
        $test = $this->xml->simple_load("admin_template|default::menu_menu");
       
        $menu = array();
        $int = 0;
        $active = "true";
        foreach($test AS $key => $value){
            if(isset($value->class)){
                $module = (string)$value->class;
                $method = (string)$value->method;
                if(!Admin_Permission::i()->perm_module($module,$method,TRUE)){
                    continue;
                }
            }
            $found = array();
            $menu[] = &$found;
            $found["name"] = current($value->name);
            $found["link"] = Url::root(TRUE) . current($value->link);
            $svg = current($value->svg);

            $found["svg"] =  $this->svg->get_svg($svg);
            
            if(isset(Registry::i()->active_menu)){
                if($value->attributes()->name == Registry::i()->active_menu){
                    $found["active"] = "true";
                }
            }else{
                // Сверяем, чем число больше, тем больше вероятность что это активная ссылка.
                if($int < ($t = strspn(Url::root(FALSE),$found["link"]))){
                    $active = "false";
                    unset($active);
                    $active = "true";
                    $found["active"] = &$active;
                    $int = $t;
                }else{
                    $found["active"] = "false";
                }
                
                
            }
            unset($found);
        }
        
        return $this->xml->preg_load($menu,"admin_template|default::menu_menuPars","admin_template|default::menu_menu");
    }
    
    function attach($module_action,$link,$name,$rule = NULL){
        $menu = array();
        $this->dop_menu[$link] = &$menu;
        
        $module_action = strtolower($module_action);
        
        $module_action = explode("_",$module_action,2);
        $module_action = "/".implode("/",$module_action)."/";
        
        $module_action = Url::root().$module_action;
        $module_action .= $link;
       
        $menu["link"] = rtrim($module_action,"/");
        $menu["name"] = $name;
        
        $test = current(Arr::search_one(array("rule"=>$rule),$this->dop_menu));
        if(!$test){
           $menu["rule"] = $rule; 
        }else{
            throw new Core_Exception("Не может быть два одинакового правила, пришло правило <b>:rule</b>",array(":rule"=>$rule));
        }
        
        return TRUE;
    }
    /**
     *     Нужно указать следующие ячейки. Все не обязательные будут взяты у родителя
     *          "action" => "moderator_user",   // Выполняемый модуль и метод
     *
     *          //[!] Обязательно одно из двух
     *          "link" => "group?delete='all'", // Полный путь
     *          "to_link" => "?delete='all'",   // Добавить к родительскому адресу 
     *                                          // (если есть link этот атрибут исключен)
     *          //[!] Обязательно
     *           "name" => "Группы",            // Имя ссылки 
     *
     *           "rule" => "delete",            // Правило
     *
     *
     */
    function attach_to($from_link,$dop_menu){
        $all_name = array("action","link","to_link","name","rule");
        $link_test = array("link","to_link");
        
        if(!isset($this->dop_menu[$from_link])){
            throw new Core_Exception("Не установлено основное меню <b>:from_link</b>",array(":from_link"=>$from_link));
        }
        // Ячеек не должно быть больше чем указано.
        if($pars = Arr::diff_key($dop_menu,$all_name)){
            throw new Core_Exception("Лишние ключи массива <b>:pars</b>",array(":pars"=>implode(" | ",array_keys($pars))));
        }
        
        // Проверяем что бы была хотя бы одна из ссылок, и не две сразу
        if(!$pars = Arr::diff_key(array_flip($link_test),array_keys($dop_menu))){
            throw new Core_Exception("Должно быть одно из двух значений <b>:pars</b>, а не оба сразу.",array(":pars"=>implode(" | ",$link_test)));
        }else{
            if(count($pars) == 2){
                throw new Core_Exception("Должно быть указано хотя бы одно из <b>:pars</b>.",array(":pars"=>implode(" | ",$link_test)));
            }
        }
        // Лишние ключи нам тоже не нужны.
        if($pars = Arr::diff_key($dop_menu,$all_name)){
            throw new Core_Exception("Лишние ключи массива <b>:pars</b>",array(":pars"=>implode(" | ",array_keys($pars))));
        }
        
        // Проверяем на обязательное поле name
        if($pars = Arr::diff_key(array_flip(array("name")),array_keys($dop_menu))){
            throw new Core_Exception("Не хватает значений <b>:pars</b>",array(":pars"=>implode(" | ",array_keys($pars))));
        }
        extract($dop_menu);
        
        $parent = $this->dop_menu[$from_link];
        $menu = array();
        
        if(!isset($link)){
            $link = $from_link . $to_link;
        }
        
        $this->dop_menu[$from_link]["dop"][$link] = &$menu;
        
        if(isset($action)){
            $module_action = $action;
            $module_action = strtolower($module_action);
            
            $module_action = explode("_",$module_action,2);
            $module_action = "/".implode("/",$module_action)."/";
            
            $module_action = Url::root().$module_action;
            $module_action .= $link;
           
            $menu["link"] = rtrim($module_action,"/");
        }else{
            $menu["link"] = $parent["link"] . $link;
        }
        $menu["name"] = $name;
        
        
        if(!isset($rule)){
           $menu["rule"] = $parent["rule"]; 
        }else{
            $menu["rule"] = $rule;
        }

        return TRUE;
    }
    /**
     * Выдает меню которое было создано через функцию $this->attach().
     *
     *
     * @param   string   имя переменной
     * @param   string   значение по умолчанию для возвращения.
     * @param   string   значение по умолчанию для возвращения.
     * @return  string
     */
    function get($array_rule = NULL,$active = NULL){
        $array_rule = $array_rule["rule"];
        
        $menu = array();
        
        foreach($this->dop_menu AS $key => $value){
           if(isset($array_rule[$value["rule"]]) AND !$array_rule[$value["rule"]]){
              continue;
           }
           // Определяем подключенную ссылку
           if(URL::root(FALSE) == $value["link"]){
               $value["active"] = "true";
           }
           elseif(isset($value["dop"])){
               foreach($value["dop"] AS $k => $v){
                   if(URL::root(FALSE) == $v["link"]){
                       $value["active"] = "true";
                   }
               }
           }else{
               $value["active"] = "false";
           }
           
           $menu[$key] = $value;
        }
        
        // Если есть ссылка по умолчанию и нет уже активной
        if(!empty($active)){
            if(!Arr::search_one(array("active"=>"true"),$menu)){
                if(isset($menu[$active])){
                    $menu[$active]["active"] = "true";
                }else{
                    $active = FALSE;
                }
            }
        }
        
        $this->dop_menu = array();
        
        return $this->xml->preg_load($menu,"admin_template|default::menu_menuDop","admin_template|default::menu_menuDop");
    }
    /**
     * Возвращает массив
     *
     *
     * @return  array
     */
    function get_array(){
        return $this->dop_menu;
    }
    
    
    
}