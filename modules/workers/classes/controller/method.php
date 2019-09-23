<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Method_Workers{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->user_method = Controller::factory("method","user");
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
    function pay_worker($id_user, $id_user_worker, $cost){
        try{
            Query::i()->sql("transaction.savepoint",array(":set" => "user"));
            
            
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
            
            
            // Записываем купленные вакансии в базу.
            
            $insert = array($id_user, $id_user_worker);
            
            $set = $this->sql->insert_string($insert);

            Query::i()->sql("insert",array(
                                        ":table" => "pay_user",
                                        ":where" => "id_user, id_user_worker",
                                        ":set"   => $set
                                    ));
                                    
            
            if($this->orders->set_orders($id_user, "user", $cost)){
                Query::i()->sql("transaction.release_savepoint",array(":set" => "user"));
            }else{
               throw new Core_Exception("Контакты не куплены"); 
            }
            
            return TRUE;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback_savepoint",array(":set" => "user"));
            
            $this->error->set("error","warning",array("message" => 'Вакансия не куплена.'));
            
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
            $worker = $this->complex_get($id, $user);
        }

        return $worker;
    }
    
    protected function simple_get($id, $user = array()){
        $data = array();
        
        $lang_id = Registry::i()->user_language["id"];
        // Нет вакансии, ошибка 404
        if(!$worker = $this->user_method->get_user(array("id" => $id), TRUE)){
            Model::factory('error','system')->error();
        }
        
        $get = Query::i()->sql("workers.simple_get",array(":id" => $id), NULL, TRUE);
        $worker = Arr::merge($worker, $get);
        
        return $this->addition_worker($worker);
    }
    
    protected function complex_get($id, $user = array()){
        // Нет вакансии, ошибка 404
        if(!$worker = $this->user_method->get_user(array("id" => $id), TRUE)){
           Model::factory('error','system')->error();
        }

        $get = Query::i()->sql("workers.complex_get",array(":id" => $id, ":id_user" => $user["id"]), NULL, TRUE);
        
        $worker = Arr::merge($worker, $get);
        
        return $this->addition_worker($worker);
    }
    
    protected function addition_worker($worker){
        $id = $worker["id"];
        
        $data = array();
        
        $lang_id = Registry::i()->user_language["id"];
        
        //$ad["description"] = nl2br($ad["description"]);
        // Определяем дату в возврасте
        $date = new DateTime();
        $took_days = new DateTime($worker["birthday"]);
        
        $worker["all_year"] = $took_days->diff($date)->y; 
        
        
        $data["worker"] = $worker;
        
        $data["user_language"] = Query::i()->sql("workers.language",array(":id" => $id));
        $data["user_specialization"] = Query::i()->sql("workers.specialization",array(":id" => $id));
        
        $data["language"] = Query::i()->sql("lang.get",array(":lang_code" => $lang_id));
        $data["specialization"] = Query::i()->sql("spec.get",array(":lang_code" => $lang_id));
        $data["country"] = Query::i()->sql("country.get",array(":lang_code" => $lang_id));

        $data["fields"] = $this->account->get_fields($id);
        
        // Добавляем двойные отступы
        $field = Arr::search(array("name"=>"description"),$data["fields"]);
        
        $key = key($field);
        
        $field[$key]["var"] = nl2br($field[$key]["var"]);
        
        $data["fields"] = Arr::merge($data["fields"], $field);
        
        // Проверяем аккаунт на просроченнось 
        /*
        if($ad["status"] == 1){
            if($data["ads_user"]["status"] == 0){
                $ad["status"] = 2;
            }
            elseif($data["ads_user"]["days"] == 0){
                $ad["status"] = 0;
            }
        }*/
        
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
    function get_workers($page = NULL, $user = array()){
        $xml_pars = "template|default::workers_user";
        $xsl_pars = "template|default::workers_user";
        
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
                $set .= " INNER JOIN user_specialization `us` ON u.id = `us`.id_user AND `us`.id_specialization IN".$ids;
            }
            if(isset($get["country"])){
                $ids = $this->sql->insert_string($get["country"]);
                $where .= " AND a.id_country IN".$ids;
            }
            if(isset($get["language"])){
                $ids = $this->sql->insert_string($get["language"]);
                $set .= " INNER JOIN user_language `ul` ON u.id = `ul`.id_user AND `ul`.id_language IN".$ids;
            }
            
            if(isset($get["search"])){
                $search = "%".current($get["search"])."%";
                $where .= " AND a.name LIKE ".DB::escape($search);
            }
        }
        
        if(!empty($user)){
            $id = DB::escape($user["id"]);
            $set .= Str::__(" LEFT JOIN __pay_user pu ON pu.id_user_worker = u.id AND pu.id_user = :id",array(":id" => $id));
            
            $colum = ", if(pu.id, 1, 0) AS pay";
        }
        
        $data["workers"] = Query::i()->sql("workers.all",array(
                                                        ":set"   => $set,
                                                        ":where" => $where,
                                                        ":table" => $colum,
                                                    ),"id");
                                                    
        $ids = array_keys($data["workers"]);
        
        if(!empty($ids)){
            $ids = $this->sql->insert_string($ids,FALSE);
            
            $data["user_language"] = Query::i()->sql("workers.language",array(":id" => $ids));

            $data["user_specialization"] = Query::i()->sql("workers.specialization",array(":id" => $ids));
        }
        
        $lang_id = Registry::i()->user_language["id"];
        $data["language"] = Query::i()->sql("lang.get",array(":lang_code" => $lang_id));
        $data["specialization"] = Query::i()->sql("spec.get",array(":lang_code" => $lang_id));
        $data["country"] = Query::i()->sql("country.get",array(":lang_code" => $lang_id));
        
        $data["fields"] = $this->account->get_fields($ids);
        
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