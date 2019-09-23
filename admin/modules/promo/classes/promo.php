<?php

class Promo_Admin implements I_Module{
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
        $this->account = Controller::factory("account","user");
    }
    
    function fetch($type = "promo"){

        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        // Активное меню
        Registry::i()->active_menu = "promo";
        
        Registry::i()->title = "Промокоды";
        
        $data = array();
        
        $menu = Admin_Model::factory("menu","system");
        
        $menu->attach("promo","fetch","Промокоды","");
        $menu->attach("promo","price","Цены","");
        
        $data["menu"] = $menu->get();
        
        $data["content"] = $this->$type();

        return Admin_Template::factory(Registry::i()->template,"content_users_fetch",$data);
    }
    
    function price(){
        Registry::i()->active_menu = "promo";
        // Активное меню
        Registry::i()->active_menu = "promo";
        
        Registry::i()->title = "Настройка цен";
        
        //Создаем отображение
        $xml_pars = "admin_template|default::promo_price";
        $xsl_pars = "admin_template|default::promo_price";
        
        if(Request::method("post")){
            Query::i()->sql("transaction.start");
            
            Query::i()->sql("transaction.commit");
        }
        
        $data = array();
        
        if(Request::method("post")){
            $post = $_POST;
            
            unset($post["session_id"]);
            
            if($post["currency"]){
                foreach(current($post) AS $key => $value){
                    Query::i()->sql("update",array(
                                                ":table" => "currency",
                                                ":set"   => $this->sql->update(",",$value),
                                                ":id"   => $key,
                                            ));
                }
            }
            elseif($post["price"]){
                foreach(current($post) AS $key => $value){
                    Query::i()->sql("update",array(
                                                ":table" => "price",
                                                ":set"   => $this->sql->update(",",$value),
                                                ":id"   => $key,
                                            ));
                } 
            }
        }
        
        $data["price"] = Admin_Query::i()->sql("price.get");
        $data["currency"] = Admin_Query::i()->sql("currency.get");
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        
        $menu = Admin_Model::factory("menu","system");
        
        $menu->attach("promo","fetch","Промокоды","");
        $menu->attach("promo","price","Цены","");
        
        $data["menu"] = $menu->get();
        
        $data["content"] = $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);

        return Admin_Template::factory(Registry::i()->template,"content_users_fetch",$data);
    }
    function promo(){
        //Создаем отображение
        $xml_pars = "admin_template|default::promo_promo";
        $xsl_pars = "admin_template|default::promo_promo";
        
        $data = array();
        
        if(Request::method("post")){
            $create = Request::post("create");
            $drop = Request::post("drop");
            
            $_POST = Arr::replace_value($_POST, '', NULL, TRUE);
            
            $insert = array();
            $update = array();
            if($create){
                
                $insert["promo"]  = hash("crc32b",time()).hash("crc32b",time()+1);
                $insert["days"]   = empty($i = Request::post("days"))?0:$i;
                $insert["seor"]   = empty($i = Request::post("seor"))?0:$i;
                $insert["clicks"] = empty($i = Request::post("clicks"))?0:$i;
                $insert["ads"]    = empty($i = Request::post("ads"))?0:$i;
                $insert["time"]   = Request::post("time");
                if(Request::post("once")){
                    $insert["once"] = Request::post("once");
                }

                // Устанавливаем промокод.
                Query::i()->sql("insert",array(
                                                    ":table" => "promo",
                                                    ":where" => implode(",",array_keys($insert)),
                                                    ":set"   => $this->sql->insert_string($insert)
                                               ));
                
                Request::redirect(Url::site());
            }
            
            if($drop){
                Query::i()->sql("delete",array(
                                                    ":table" => "promo",
                                                    ":where" => "id",
                                                    ":insert"   => sprintf("(%s)",$drop)
                                               ));
                                               
                Request::redirect(Url::site());
            }
        }
        
        $data["promo"] = Admin_Query::i()->sql("promo.get");
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}