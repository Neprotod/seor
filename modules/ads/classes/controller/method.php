<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Method_Ads{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->account = Controller::factory("account","user");
        $this->orders = Controller::factory("orders","user");
    }
    
    /**
     * Покупка контактов
     * 
     * $param int id вакансии
     * $param int id покупателя
     * $param int id стоимость открытия 0 = за бесплатный клик, остальное это за seor.
     * 
     */
    function pay_ad($id_ad, $id_user, $id_user_employer, $cost){
        try{
            Query::i()->sql("transaction.savepoint",array(":set" => "ad"));
            
            
            //Снимаем со счета
            $set = "";
            if($cost == 0){
                $set = "clicks = clicks - 1";
            }else{
                $set = sprintf("seor = seor - %s", $cost);
            }

            Query::i()->sql("update_where",array(
                                        ":table" => "accounts",
                                        ":set"   => $set,
                                        ":where" => sprintf("id_user = %s", DB::escape($id_user))
                                    ));
            
            
            // Записываем купленную вакансию в базу.
            
            $insert = array($id_ad, $id_user, $id_user_employer);
            
            $set = $this->sql->insert_string($insert);

            Query::i()->sql("insert",array(
                                        ":table" => "pay_ads",
                                        ":where" => "id_ads, id_user, id_user_employer",
                                        ":set"   => $set
                                    ));
                                    
            
            if($this->pay_contact($id_user, $id_user_employer) AND $this->orders->set_orders($id_user, "ads", $cost)){
                Query::i()->sql("transaction.release_savepoint",array(":set" => "ad"));
            }else{
               throw new Core_Exception("Контакты не куплены"); 
            }
            
            return TRUE;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback_savepoint",array(":set" => "ad"));
            
            $this->error->set("error","warning",array("message" => 'Вакансия не куплена.'));
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
        }
    }
    
    /**
     * Покупка контактов
     * 
     * $param int   id пользователя
     * $param int   id продавца
     * $param mixed id стоимость открытия
     * 
     */
    function pay_contact($id_user, $id_user_employer, $cost = FALSE){
        try{
            Query::i()->sql("transaction.savepoint",array(":set" => "contact"));
            
            if($cost !== FALSE){
                //Снимаем со счета
                $set = "";
                if($cost == 0){
                    $set = "clicks = clicks - 1";
                }else{
                    $set = sprintf("seor = seor - %s", $cost);
                }

                Query::i()->sql("update_where",array(
                                            ":table" => "accounts",
                                            ":set"   => $set,
                                            ":where" => sprintf("id_user = %s", DB::escape($id_user))
                                        ));
            }
            
            Query::i()->sql("ad.pay_contact",array(
                                            ":id_user"          => $id_user,
                                            ":id_user_employer" => $id_user_employer
                                        ));
            
            if($this->orders->set_orders($id_user, "contacts", intval($cost)))
                Query::i()->sql("transaction.release_savepoint",array(":set" => "contact"));
            else
                throw new Core_Exception("Контакты не куплены");
            return TRUE;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback_savepoint",array(":set" => "contact"));
            
            $this->error->set("error","warning",array("message" => 'Контакты не куплены.'));
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
        }
    }
    
    function get($id, $user){
        // Нет ID выдаем сразу ошибку 404.
        if(!$id){
            Model::factory('error','system')->error();
        }
        // Нет пользователя, значит гость, упрощаем выдачу вакансии.
        if(!$user){
            return $this->simple_get($id);
        }else{
            $ad = $this->complex_get($id, $user);
        }

        return $ad;
    }
    
    protected function simple_get($id, $user = array()){
        $data = array();
        
        $lang_id = Registry::i()->user_language["id"];
        // Нет вакансии, ошибка 404
        if(!$ad = Query::i()->sql("ad.simple_get",array(":id" => $id), NULL, TRUE)){
            Model::factory('error','system')->error();
        }
        
        return $this->addition_ad($ad);
    }
    
    protected function complex_get($id, $user = array()){
        // Нет вакансии, ошибка 404
        if(!$ad = Query::i()->sql("ad.complex_get",array(":id" => $id, ":id_user" => $user["id"]), NULL, TRUE)){
            Model::factory('error','system')->error();
        }
        
        return $this->addition_ad($ad);
    }
    
    protected function addition_ad($ad){
        $id = $ad["id"];
        
        $data = array();
        
        $lang_id = Registry::i()->user_language["id"];
        
        if($ad["status"] == 3){
            if(empty($user) OR $ad["id_user"] != $user["id"]){
                Model::factory('error','system')->error();
            }
        }
        
        $ad["description"] = nl2br($ad["description"]);
        
        $data["ads"] = array($id => &$ad);
        
        $data["ads_language"] = Query::i()->sql("ads.language",array(":id" => $id));
        $data["ads_specialization"] = Query::i()->sql("ads.specialization",array(":id" => $id));
        $data["currency_name"] = Query::i()->sql("currency.name");
        $data["language"] = Query::i()->sql("lang.get",array(":lang_code" => $lang_id));
        $data["specialization"] = Query::i()->sql("spec.get",array(":lang_code" => $lang_id));
        $data["country"] = Query::i()->sql("country.get",array(":lang_code" => $lang_id));
        
        $user_method = Controller::factory("method","user");
        
        $id_user = $ad["id_user"];

        $data["ads_user"] = $user_method->get_user(array("id" => $id_user), TRUE);
        $data["fields"] = $this->account->get_fields($id_user);
        
        // Проверяем аккаунт на просроченнось 
       
        if($ad["status"] == 1){
            if($data["ads_user"]["status"] == 0){
                $ad["status"] = 2;
            }
            elseif($data["ads_user"]["days"] == 0){
                $ad["status"] = 0;
            }
        }
        
        return $data;
    }
    
    /*
     * Преобразование GET запроса
     */
    function pars_get(){
        $get = array();
        if(!empty($_GET)){
            foreach($_GET AS $key => &$value){
                $value = urldecode($value);
                if($key == 'search'){
                    $get[$key] = array($value);
                    continue;
                }
                $get[$key] = explode(",",$value);
            }
        }
        
        return $get;
    }
    /**
     * Получить вакансии
     */
    function get_ads($page = NULL, $user = array()){
        $xml_pars = "template|default::ads_adverts";
        $xsl_pars = "template|default::ads_adverts";
        
        $data = array();
        $data["user"] = $user;

        $set = '';
        $where = '';
        $colum = '';
        
        $get = $this->pars_get();
        $data["get"] = $get;
        
        if(!empty($get)){
            // Составляем сложный запрос
            if(isset($get["specialization"])){
                $ids = $this->sql->insert_string($get["specialization"]);
                $set .= " INNER JOIN ads_specialization `as` ON a.id = `as`.id_ads AND `as`.id_specialization IN".$ids;
            }
            if(isset($get["country"])){
                $ids = $this->sql->insert_string($get["country"]);
                $where .= " AND a.id_country IN".$ids;
            }
            if(isset($get["language"])){
                $ids = $this->sql->insert_string($get["language"]);
                $set .= " INNER JOIN ads_language `al` ON a.id = `al`.id_ads AND `al`.id_language IN".$ids;
            }
            
            if(isset($get["currency"])){
                $currency = current($get["currency"]);
                $currency_rate = Query::i()->sql("currency.get",array(),"id_currency_name");
                $set .= " INNER JOIN currency c ON c.id = a.id_currency";
                if(isset($get["price_from"])){
                    $val   = current($get["price_from"]);
                    $price = $val * $currency_rate[$currency]["rate"];
                    $where .= " AND (a.salary * c.rate) >= " . DB::escape($price);
                }
                if(isset($get["price_to"])){
                    $val   = current($get["price_to"]);
                    $price = $val * $currency_rate[$currency]["rate"];
                    $where .= " AND (a.salary * c.rate) <= " . DB::escape($price);
                }
            }
            
            if(isset($get["search"])){
                $search = "%".current($get["search"])."%";
                $where .= " AND a.title LIKE ".DB::escape($search);
            }
        }
        
        if(!empty($user)){
            $id = DB::escape($user["id"]);
            $set .= Str::__(" LEFT JOIN __pay_ads pa ON pa.id_ads = a.id AND pa.id_user = :id",array(":id" => $id));
            $set .= Str::__(" LEFT JOIN __pay_contacts pc ON pc.id_user = :id AND pc.id_user_employer = a.id_user AND pc.expiration > NOW()", array(":id" => $id));
            
            $colum = ", if(pa.id, 1, 0) AS pay, if(pc.id AND pa.id IS NULL, TIMEDIFF(pc.expiration, NOW()), NULL) AS expiration, pc.expiration AS expiration_time ";
        }
        
        $data["currency_name"] = Query::i()->sql("currency.name",array(),"id");
        unset($data["currency_name"][1]);

        
        $data["ads"] = Query::i()->sql("ads.all",array(
                                                        ":set"   => $set,
                                                        ":where" => $where,
                                                        ":table" => $colum,
                                                    ),"id");
        
        $ids_ads = array_keys($data["ads"]);
        
        if(!empty($ids_ads)){
            $ids_ads = $this->sql->insert_string($ids_ads,FALSE);
            
            $data["ads_language"] = Query::i()->sql("ads.language",array(":id" => $ids_ads));

            $data["ads_specialization"] = Query::i()->sql("ads.specialization",array(":id" => $ids_ads));
        }
        $lang_id = Registry::i()->user_language["id"];
        $data["language"] = Query::i()->sql("lang.get",array(":lang_code" => $lang_id));
        $data["specialization"] = Query::i()->sql("spec.get",array(":lang_code" => $lang_id));
        $data["country"] = Query::i()->sql("country.get",array(":lang_code" => $lang_id));
        
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "return" => base64_encode(Url::query(array(),'auto')),
        );

        $data["content"] = array($this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech));

        return $data;
    }
}