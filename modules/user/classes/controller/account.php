<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Account_User{

    const VERSION = '1.0.0';
    
    static $fields_name = array();
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->method = Controller::factory("method","user");
        $this->orders = Controller::factory("orders","user");
    }
    
    function ads_post($id, &$data){
        // Обрабатываем пост запрос, на покупку или сохранение вакансии
        $user = Registry::i()->user;
        $mark = "all";
        
        $message = "Вакансия отправлена на модерацию.";
        
        $to_ads = array();
        $to_specialization = array();
        $to_language = array();
        $to_post = array();
        
        $submit = Request::post("submit", "int");
        
        $to_ads["id_user"] = $to_post["id_user"]           = $user["id"];
        $to_ads["title"] = $to_post["title"]               = Request::post("title", "string");
        $to_ads["salary"] = $to_post["salary"]             = Request::post("salary", "string");
        $to_ads["id_currency"] = $to_post["id_currency"]   = Request::post("currency_name", "string");
        $to_ads["description"] = $to_post["description"]   = Request::post("description");
        $to_ads["approved"] = $to_post["approved"]         = 0;
        $to_ads["id_country"] = $to_post["id_country"]     = Request::post("id_country", "string");
        
        $to_specialization = $to_post["to_specialization"] = Request::post("specialization", NULL, array());
        $to_language = $to_post["to_language"]             = Request::post("language", NULL, array());

        // Проверяем данные.
        $validator = Model::factory("validator","system");
        $valid = array(
                        "to_specialization" => array(
                            "required" => TRUE,
                            "type" => "array",
                        ),
                        "title" => array(
                            "required" => TRUE,
                            "type" => "str",
                        ),
                        "description" => array(
                            "required" => TRUE,
                        ),
                        "salary" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "[0-9]*",
                        ),
                        "id_currency" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "[0-9]*",
                        ),
                        "id_country" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "[0-9]*",
                        ),
                );

        // Проверяем форму на ошибки.
        $t = $validator->valids_around($valid,$to_post);
       
        if($t){
            $this->repost_ad($data, $to_post);
            
            $this->error->set("error","warning",array("message"=>"Неправильно заполнено поле"));
            
            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["required"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Поле не должно быть пустым"));
                }
                elseif(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Только числа"));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }
                
            }  
            
            return FALSE;
        }
        
        $ad = isset($data["ads"])
                            ? $data["ads"]
                            : array();
        try{
            Query::i()->sql("transaction.start");
            $result = FALSE;
            
            if($submit == 1){
                if(!isset($ad["pay"]) OR (isset($ad["pay"]) AND $ad["pay"] != 1)){
                    if($user["ads"]){
                        // Перед покупкой сохраняем вакансию.
                        $to_post["pay"] = 1;
                        if(!$id = $this->save_ad($to_post)){
                            throw new Core_Exception("Вакансия не создана");
                        }
                        $result = $this->pay_ad($id, $user["id"], 0);

                        $mark = "moder";
                    }
                    else{
                        $cost = 0;
                        
                        $price = Arr::search(array("type_name" => "create"), $data["price"]);
                        $price = Arr::value_key($price,"name");
                        
                        $cost = $price["ads_create"]["amount"];
                        
                        if($cost > $user["seor"]){
                            $this->error->set("error","warning",array("message" => 'Недостаточно средств, <a href="/account/pay">пополните счет</a>.'));
                            $this->repost_ad($data, $to_post);
                        }else{
                            // Перед покупкой сохраняем вакансию.
                            $to_post["pay"] = 1;
                            if(isset($data["ads"])){
                                if(!$result = $this->update_ad($data, $to_post, 1)){
                                    throw new Core_Exception("Вакансия не создана");
                                }
                                $id = $result["id"];
                            }else{
                                if(!$id = $this->save_ad($to_post)){
                                    throw new Core_Exception("Вакансия не создана");
                                }
                            }

                            $result = $this->pay_ad($id, $user["id"], $cost);

                            $mark = "moder";
                        }
                    }
                }else{
                    // Покупка была проведена ранее
                    $result = $this->update_ad($data, $to_post, 1);

                    if($result["status"] == 1){
                        $message = "Вакансия обновлена.";
                        $mark = "active";
                    }elseif($result["status"] == 3){
                        $message = "Вакансия обновлена в черновике.";
                        $mark = "draft";
                    }else{
                        if($result["approved"] == 0 OR $result["approved"] == 2){
                            $mark = "moder";
                        }
                    }
                }
            }
            elseif($submit == 3){
                $mark = "draft";
                $message = "Вакансия сохранена в черновик.";
                if(empty($ad)){
                    $to_post["seen"] = 1;
                    $to_post["status"] = 3;
                    $result = $this->save_ad($to_post);
                }else{
                    $message = "Вакансия обновлена в черновике.";
                    $result = $this->update_ad($data, $to_post, 3);
                }
            }
            
            
            if($result){
                Query::i()->sql("transaction.commit");
                $this->error->set("message","info",array("message" => $message));
                $this->error->save_cookie();
                
                Request::redirect(Core::$root_url."/account/ads?mark=".$mark);
            }else{
                Query::i()->sql("transaction.rollback");
                $this->error->set("error","warning",array("message" => "Вакансия не создана"));
                $this->repost_ad($data, $to_post);
            }
            
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");

            $this->repost_ad($data, $to_post);
            $this->error->set("error","warning",array("message" => $e->getMessage()));
        }
    }
    
    protected function repost_ad(&$data, $post){

        $data["ads"]["title"]       = $post["title"];
        $data["ads"]["salary"]      = $post["salary"];
        $data["ads"]["id_currency"] = $post["id_currency"];
        $data["ads"]["description"] = $post["description"];
        $data["ads"]["id_country"]  = $post["id_country"];
        
        foreach($post["to_specialization"] AS $value){
            if(!isset($data["ads_specialization"]) 
                OR (isset($data["ads_specialization"])
                AND !isset($data["ads_specialization"][$value]))){
                $data["ads_specialization"][$value]["id_specialization"] = $value;
            }
        }

        foreach($post["to_language"] AS $value){
            if(!isset($data["ads_language"]) 
                OR (isset($data["ads_language"])
                AND !isset($data["ads_language"][$value]))){
                $data["ads_language"][$value]["id_language"] = $value;
            }
        }
    }
    protected function update_ad(&$data, $post, $status = NULL){
        $ad = $data["ads"];
        $post["id"] = $ad["id"];
        $post["id_user"] = $ad["id_user"];
        $post["time"] = $ad["time"];
        $post["time"] = $ad["time"];
        if(!isset($post["pay"]))
            $post["pay"] = $ad["pay"];
        $post["approved"] = $ad["approved"];
        if(!$status){
            $post["status"] = $ad["status"];
        }else{
            $post["status"] = (string)$status;
        }
        
        
        $to_specialization = $post["to_specialization"];
        $ads_specialization = $data["ads_specialization"];
        $to_language = $post["to_language"];
        $ads_language = $data["ads_language"];
        
        unset($post["to_specialization"],$post["to_language"]);
        
        
        $result = $this->sql->insert_update($ad, $post);
        
        $update = $result["update"];
        try{
            if($status == 3){
                $update["approved"] = $post["approved"] = 0;
                $update["seen"]     = 1;
            }else{
                if((isset($update["title"]) OR isset($update["description"]))){
                    $update["approved"] = $post["approved"] = 0;
                    $update["status"]   = $post["status"]   = 0;
                    $update["seen"]     = 0;
                    // СНЯТЬ ЗАМЕТКУ МОДЕРАТОРА ЕСЛИ ОНА ЕСТЬ.
                    Query::i()->sql("delete",array(
                                            ":table" => "moderator_notes",
                                            ":where" => "id_ads",
                                            ":insert"   => sprintf("(%s)",DB::escape($ad["id"])),
                                        ));
                }
                
                if($ad["approved"] == 0 OR $ad["approved"] == 2){
                    $update["status"]   = $post["status"]   = 0;
                    $update["seen"]     = 0;
                }
                
            }
            
            // Сохраняем в базу.
            $set = $this->sql->update(",",$update);
            if($set){
                Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => $set,
                                                ":id" => $ad["id"],
                                            ));
            }
        
            
            
            // Проверяем специализации.
            $test = array();
            foreach($ads_specialization AS $value){
                $id = $value["id_specialization"];
                $test[$id] = $id;
            }
            $result = $this->sql->insert_update_delete($test, $to_specialization);
            $insert_spec = $this->array_dop($result["insert"], $ad["id"]);
            $delete_spec = $result["delete"];
            

            // Удаляем ненужные специализации
            if($delete_spec){
                $ids = $this->sql->insert_string($delete_spec);
                $sql = sprintf("id_specialization IN %s AND id_ads = %s",$ids, DB::escape($ad["id"]));
                Query::i()->sql("delete_where",array(
                                    ":table" => "ads_specialization",
                                    ":where" => $sql,
                                ));
            }
            // Добавляем специализации
            if($insert_spec){
                $set = $this->sql->insert_string($insert_spec);

                Query::i()->sql("insert",array(
                                    ":table" => "ads_specialization",
                                    ":where" => "id_specialization,id_ads",
                                    ":set" => $set
                                ));
            }
            
            // Проверяем языки.
            $test = array();
            foreach($ads_language AS $value){
                $id = $value["id_language"];
                $test[$id] = $id;
            }
            $result = $this->sql->insert_update_delete($test, $to_language);
            $insert_lang = $this->array_dop($result["insert"], $ad["id"]);
            $delete_lang = $result["delete"];
            
            // Удаляем языки
            if($delete_lang){
                $ids = $this->sql->insert_string($delete_lang);
                $sql = sprintf("id_language IN %s AND id_ads = %s",$ids, DB::escape($ad["id"]));
                Query::i()->sql("delete_where",array(
                                    ":table" => "ads_language",
                                    ":where" => $sql,
                                ));
            }
            // Добавляем языки
            if($insert_lang){
                $set = $this->sql->insert_string($insert_lang);

                Query::i()->sql("insert",array(
                                    ":table" => "ads_language",
                                    ":where" => "id_language,id_ads",
                                    ":set" => $set
                                ));
            }
            
            return $post;
        }catch(Exception $e){
            Core_Exception::client($e);
            return FALSE;
        }
    }
    protected function save_ad($post){
        $to_language = array();
        
        $to_specialization = $post["to_specialization"];
        unset($post["to_specialization"]);
        
        if(isset($post["to_language"])){
            $to_language = $post["to_language"];
            unset($post["to_language"]);
        }
        $to_ads = $post;
        try{
            $where = implode(",",array_keys($to_ads));
            $set = $this->sql->insert_string($to_ads);
            $id = Query::i()->sql("insert",array(
                                                ":table" => "ads",
                                                ":where" => $where,
                                                ":set"   => $set,
                                            ));
                
            $id = current($id);
            /*
            if($id == "new"){
                
            }else{
                $set = $this->sql->update(",",$to_ads);

                Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => $set,
                                                ":id"   => $id,
                                            ));
                Query::i()->sql("delete",array(
                                                ":table" => "ads_language",
                                                ":where" => "id_ads",
                                                ":insert"   => "(".$id.")",
                                            ));
                Query::i()->sql("delete",array(
                                                ":table" => "ads_specialization",
                                                ":where" => "id_ads",
                                                ":insert"   => "(".$id.")",
                                            ));
            }*/
            $insert_specialization = array();
            foreach($to_specialization AS $value){
                $arr = array();
                $insert_specialization[] = &$arr;
                $arr["id_ads"] = $id;
                $arr["id_specialization"] = $value;
                unset($arr);
            }
            $insert_language = array();
            foreach($to_language AS $value){
                $arr = array();
                $insert_language[] = &$arr;
                $arr["id_ads"] = $id;
                $arr["id_language"] = $value;
                unset($arr);
            }
        
            $set__specialization = $this->sql->insert_string($insert_specialization);
            Query::i()->sql("insert",array(
                                                    ":table" => "ads_specialization",
                                                    ":where" => "id_ads, id_specialization",
                                                    ":set"   => $set__specialization,
                                                ));
            if($insert_language){
                $set__language = $this->sql->insert_string($insert_language);
                Query::i()->sql("insert",array(
                                                        ":table" => "ads_language",
                                                        ":where" => "id_ads, id_language",
                                                        ":set"   => $set__language,
                                                    ));
            }
            return $id;
        }catch(Exception $e){
            Core_Exception::client($e);
            return FALSE;
        }
    }
    
    /**
     * Покупка контактов
     * 
     * $param int id вакансии
     * $param int id покупателя
     * $param int id стоимость открытия 0 = за бесплатный клик, остальное это за seor.
     * 
     */
    function pay_ad($id_ad, $id_user, $cost, $order = "create"){
        try{
            Query::i()->sql("transaction.savepoint",array(":set" => "ad"));
            
            
            //Снимаем со счета
            $set = "";
            if($cost == 0){
                $set = "ads = ads - 1";
            }else{
                $set = sprintf("seor = seor - %s", $cost);
            }

            Query::i()->sql("update_where",array(
                                        ":table" => "accounts",
                                        ":set"   => $set,
                                        ":where" => sprintf("id_user = %s", DB::escape($id_user))
                                    ));
                                    
            
            if($this->orders->set_orders($id_user, "create", $cost)){
                Query::i()->sql("transaction.release_savepoint",array(":set" => "ad"));
            }else{
                throw new Core_Exception("Вакансия не создана."); 
            }
            
            return TRUE;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback_savepoint",array(":set" => "ad"));
            
            $this->error->set("error","warning",array("message" => 'Вакансия не создана.'));
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
            return FALSE;
        }
    }
    
    function user_post(&$data){
        $to_account = array();
        $to_field = array();

        $to_account["name"] = Request::post("name");
        if(Request::post("birthday"))
            $to_account["birthday"] = implode("-",array_reverse(Request::post("birthday")));
        
        $to_account["id_country"] = Request::post("id_country");
       
        $to_account["name"] = Request::post("name");
        
        $to_field = Request::post("fields");
        $to_account = Arr::replace_value($to_account,"",NULL);
        $to_field = Arr::replace_value($to_field,"",NULL);
        
        // ВРЕМЕННО СТАВИМ 2.
        if(!isset($data["user"]["complete"]) OR $data["user"]["complete"] == 0){
            $to_account["complete"] = 2;
            // ВЕРНУТЬ НА 1.
            //$to_account["complete"] = 1;
        }
        
        $bas_account = $this->sql->insert_update($data["user"], $to_account);

        $set = $this->sql->update(",",$bas_account["update"]);
        try{
            Query::i()->sql("transaction.start");
            if($set){
                Query::i()->sql("update_where", array(
                                            ":table" => "accounts",
                                            ":set" => $set,
                                            ":where" => sprintf("id_user = %s",DB::escape($data["user"]["id"])),
                                          ));
            }
            
            $filed_name = $this->get_fields_name();
            
            $fields_inser = array();
            echo "<pre>";
            foreach($to_field AS $key => $value){
                foreach($value AS $v){
                    $arr = array();
                    $fields_inser[] = &$arr;
                    
                    $arr["id_user"] = $data["user"]["id"];
                    $arr["id_name"] = $filed_name[$key]["id"];

                    if($key == "description"){
                        $arr["var"]  = NULL;
                        $arr["text"] = $v;
                    }else{
                        $arr["var"]  = $v;
                        $arr["text"] = NULL;
                    }
                    unset($arr);
                }
            }
            
            // Удаляем все поля, перед добавлением. КАК ТОЛЬКО БУДЕТ ВРЕМЯ ПЕРЕПИСАТЬ ЭТОТ БРЕД
            Query::i()->sql("delete", array(
                                        ":table" => "fields_user",
                                        ":where" => "id_user",
                                        ":insert" => Str::__("(:id)",array(":id"=>$data["user"]["id"]))
                                      ));
                                      
            $sql = $this->sql->insert_string($fields_inser);
            if($sql){
                Query::i()->sql("insert", array(
                                            ":table" => "fields_user",
                                            ":where" => "id_user, id_name, var, text",
                                            ":set" => $sql,
                                          ));
            }
            Query::i()->sql("transaction.commit");

            Request::redirect(Url::root(FALSE));
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback");
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
        }
        $data["user"]["id_country"] = Request::post("id_country");
        $data["user"]["name"] = Request::post("name");
        
        $data["fields"] = $to_field;
        $data = Arr::merge($data, $_POST);
    }
    
    
    
    function get_fields_name(){
        if(self::$fields_name){
            return self::$fields_name;
        }else{
            $return = Query::i()->sql("user.field.name",array(),"id");   
            foreach($return AS $value){
                $return[$value["name"]] = $value;
            }
            return self::$fields_name = $return;
        }
    }
    
    function get_fields($id){
        if(is_array($id)){
            $id = $this->sql->insert_string($id, FALSE);
        }
        if(empty($id)){
            return array();
        }
        $return = Query::i()->sql("user.field.get",array(
                                ":id_user" => $id
                            ),"id");
        foreach($return AS &$value){
            if(isset($value["text"])){
                $value["var"] = $value["text"];
            }
            unset($value["text"]);
        }
        return $return;
    }
    
    /**
     * Дополняет массив значением
     */
    protected function array_dop($array,$val){
        $tmp = array();
        foreach($array AS $key => $value){
            $tmp[$key][] = $value;
            $tmp[$key][] = $val;
        }
        return $tmp;
    }    
    function simplification_fields($fields, $ids = TRUE){
        $return = array();
        if(isset($fields)){
            foreach($fields AS $id => $value){
               if($ids){
                   $return[$value["name"]][$id] = $value["var"];
               }else{
                   $return[$value["name"]][] = $value["var"];
               }
               
            }
        }
        return $return;
    }
    
    
    
    /********************************************/
    ///////////////Работа с профилем
    /********************************************/
    function prepare_fields($fields, $data){
        $filed_name = $this->get_fields_name();
        
        $fields_inser = array();
        if(!empty($fields))
            foreach($fields AS $key => $value){
                foreach($value AS $v){
                    $arr = array();
                    $fields_inser[] = &$arr;
                    
                    $arr["id_user"] = $data["user"]["id"];
                    $arr["id_name"] = $filed_name[$key]["id"];

                    if($key == "description"){
                        $arr["var"]  = NULL;
                        $arr["text"] = $v;
                    }else{
                        $arr["var"]  = $v;
                        $arr["text"] = NULL;
                    }
                    unset($arr);
                }
            }
        return $fields_inser;
    }
      
    function user_info_post(&$data){
        $user = $data["user"];
        $to_account = array();
        
        
        $name = Request::post("name", NULL);

        if($user["complete"] == 0){
            if(!empty($name)){
                if(is_array($name)){
                    foreach($name AS &$value){
                        $value = trim($value);
                        unset($value);
                    }
                    $to_account["name"] =  implode(" ",$name);
                }else{
                    $to_account["name"] = trim($name);
                }
            }
        }else{
             $to_account["name"] = $user["name"];
        }
        
        
        $to_account["id_country"] = Request::post("id_country");
        
        // Добавляем значения к пользователю
        $data["user"] = Arr::merge($data["user"],$to_account);
        
        // Проверяем данные.
        $validator = Model::factory("validator","system");
        
        if($birthday = Request::post("birthday")){
            $valid = array(
                            "day" => array(
                                "required" => TRUE,
                                "type" => "str",
                                "pattern" => "^[0-2][0-9]|[3][0-1]",
                            ),
                            "month" => array(
                                "required" => TRUE,
                                "type" => "str",
                                "pattern" => "^[0][0-9]|[1][0-2]",
                            ),
                            "year" => array(
                                "required" => TRUE,
                                "type" => "str",
                                "pattern" => "^[1][089][0-9][0-9]|[2][0][0-9][0-9]",
                            ),
                    );
           
            // Создаем проверку даты
            $t = $validator->valids($valid,$birthday);
            
            if($t){ 
                $t = array("birthday" => $t);
                $this->error->set("error","warning",array("message"=>"Дата не верна."));
                
                foreach($this->error->role_array($t, FALSE) AS $value){
                    if(isset($value["type"]["pattern"])){
                        $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                    }else{
                        $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                    }
                    
                }  
                
                return FALSE;
            }
            
            if($birthday)
                $to_account["birthday"] = implode("-",array_reverse($birthday));
        }
        // Очищаем все пустые значение на NULL
        $to_account = Arr::replace_value($to_account,"",NULL);
        
        if(!isset($data["user"]["complete"]) OR $data["user"]["complete"] == 0){
            $to_account["complete"] = 1;
        }
        // На проверку новых данных
        $valid = array(
                        "name" => array(
                            "required" => TRUE,
                            "type" => "str",
                        ),
                        "id_country" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "^[0-9]*",
                        )
                );
                
        // Создаем проверку данных
        $t = $validator->valids_around($valid,$to_account);
        
        if($t){ 
            $this->error->set("error","warning",array("message"=>"Данные не верны."));

            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }
                
            }  
            
            return FALSE;
        }
        
        // Сохраняем информационные данные
        $result = $this->sql->insert_update($user, $to_account);
        
        $set = $this->sql->update(",",$result["update"]);

        if($set){
            Query::i()->sql("update_where", array(
                                        ":table" => "accounts",
                                        ":set" => $set,
                                        ":where" => sprintf("id_user = %s",DB::escape($user["id"])),
                                      ));
        }
        
        // Начинаем проверять поля
        
        // Проверяем поля
        $to_field = Request::post("fields",NULL, array());
        $field = $data["fields"];
        $data["fields"] = $to_field;
        
        if(isset($to_field["activity"])){
            $k = key($to_field["activity"]);

            $activity = $to_field["activity"][$k];
            $test = preg_split("/[,\.:;]/",$activity);
            
            foreach($test AS &$val){
                $val = trim($val);
            }
            
            $to_field["activity"][$k] = implode(", ", $test);
            $to_field["activity"][$k] = trim($to_field["activity"][$k], ",.:;");
        }
        
        foreach($to_field AS $key => $value){
            $id = key($value);
            if(empty($value[$id])){
                unset($to_field[$key]);
            }
        }
        
        $result = $this->sql->insert_update_delete($field, $to_field);
        
        if(!empty($result["update"])){
            $update = $this->prepare_fields($result["update"], $data);
            
            foreach($update AS $key => $value){
                $id_user = $value["id_user"];
                $id_name = $value["id_name"];
                unset($value["id_user"], $value["id_name"]);
                
                $set = $this->sql->update(",",$value);

                Query::i()->sql("update_where", array(
                                            ":table" => "fields_user",
                                            ":set" => $set,
                                            ":where" => sprintf("id_user = %s AND id_name = %s",DB::escape($id_user), DB::escape($id_name)),
                                          ));
                
            }
        }
        if(!empty($result["insert"])){
            $insert = $this->prepare_fields($result["insert"], $data);
            
            reset($insert);
            $where = implode(",",array_keys(current($insert)));
            
            $set = $this->sql->insert_string($insert);

            Query::i()->sql("insert",array(
                                            ":table" => "fields_user",
                                            ":where" => $where,
                                            ":set" => $set
                                         ));
           
        }
        
        return TRUE;
    }
    function user_lang_spec_post(&$data){
        $to_specialization = Request::post("specialization", NULL, array());
        $to_language = Request::post("language", NULL, array());
        $user_specialization = $data["user_specialization"];
        $user_language = $data["user_language"];

        $data["user_specialization"] = array();
        foreach($to_specialization AS $key => $value){
            $data["user_specialization"][$key]["id_user"] = $data["user"]["id"];
            $data["user_specialization"][$key]["id_specialization"] = $value;
        }
        $data["user_language"] = array();
        foreach($to_language AS $key => $value){
            $data["user_language"][$key]["id_user"] = $data["user"]["id"];
            $data["user_language"][$key]["id_language"] = $value;
        }
        // Проверяем специализации.
        $test = array();
        foreach($user_specialization AS $value){
            $id = $value["id_specialization"];
            $test[$id] = $id;
        }
        $result = $this->sql->insert_update_delete($test, $to_specialization);
        $insert_spec = $this->array_dop($result["insert"], $data["user"]["id"]);
        $delete_spec = $result["delete"];
       
        // Удаляем ненужные специализации
        if($delete_spec){
            $ids = $this->sql->insert_string($delete_spec);
            $sql = sprintf("id_specialization IN %s AND id_user = %s",$ids, DB::escape($data["user"]["id"]));
            Query::i()->sql("delete_where",array(
                                ":table" => "user_specialization",
                                ":where" => $sql,
                            ));
        }
        // Добавляем специализации
        if($insert_spec){
            $set = $this->sql->insert_string($insert_spec);

            Query::i()->sql("insert",array(
                                ":table" => "user_specialization",
                                ":where" => "id_specialization,id_user",
                                ":set" => $set
                            ));
        }
        
        // Проверяем языки.
        $test = array();
        foreach($user_language AS $value){
            $id = $value["id_language"];
            $test[$id] = $id;
        }
        $result = $this->sql->insert_update_delete($test, $to_language);
        $insert_lang = $this->array_dop($result["insert"], $data["user"]["id"]);
        $delete_lang = $result["delete"];
        
        // Удаляем языки
        if($delete_lang){
            $ids = $this->sql->insert_string($delete_lang);
            $sql = sprintf("id_language IN %s AND id_user = %s",$ids, DB::escape($data["user"]["id"]));
            Query::i()->sql("delete_where",array(
                                ":table" => "user_language",
                                ":where" => $sql,
                            ));
        }
        // Добавляем языки
        if($insert_lang){
            $set = $this->sql->insert_string($insert_lang);

            Query::i()->sql("insert",array(
                                ":table" => "user_language",
                                ":where" => "id_language,id_user",
                                ":set" => $set
                            ));
        }
        
        return TRUE;
    }
    function phone_post(&$data){
        $phone = Request::post("phone",NULL, array());
        
        // Проверяем данные.
        $validator = Model::factory("validator","system");
        $valid = array(
                        "id_country_code" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "^[0-9]*$",
                        ),
                        "phone" => array(
                            "required" => TRUE,
                            "type" => "str",
                            "pattern" => "^[0-9]*$",
                        ),
                );

        // Проверяем форму на ошибки.
        $t = $validator->valids($valid,$phone);
        
        $new_phone = array();
        if(isset($phone["new"])){
            $new_phone = $phone["new"];
            unset($phone["new"]);
        }
        
        
       
        $data["phone"] = array();
        foreach($phone AS $key => &$value){
            $value["id"] = (string)$key;
            $value["id_user"] = $data["user"]["id"];
            $value["phone"] = trim($value["phone"]);
            $data["phone"][] = $value;
        }
        unset($value);
        
        // Добавляем новые телефоны
        foreach($new_phone AS $key => &$value){
            $value["id_user"] = $data["user"]["id"];
            $data["phone"][] = $value;
        }
        unset($value);

        // Проверяем телефоны
        if($t){
            $t = array("phone" => $t);
            
            $this->error->set("error","warning",array("message"=>"Неправильно заполнены телефоны"));
            
            foreach($this->error->role_array($t, FALSE) AS $value){
                if(isset($value["type"]["pattern"])){
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1,"tooltip"=>"Только числа"));
                }else{
                    $this->error->set("error","danger",array("role"=>$value["path"],"select"=>1));
                }
                
            }  
            
            return FALSE;
        }
        
        // Проверка на уникальность
        $test = array();
        foreach($data["phone"] AS $value){
            $string = $value["id_country_code"] . ":" .$value["phone"];
            if(isset($test[$string])){
                $this->error->set("error","warning",array("message"=>"Одинаковые телефоны."));
                return FALSE;
            }else{
                $test[$string] = 1;
            }
        }
        
        // Проверяем какие телефоны нужно заменить.
        $result = $this->sql->insert_update_delete($data["phone"], $phone);
        
        if(!empty($result["update"])){
            foreach($result["update"] AS $key => $value){
                $set = $this->sql->update(",",$value);

                Query::i()->sql("update",array(
                                            ":table" => "phone",
                                            ":set" => $set,
                                            ":id" => $key
                                         ));
            }
        }
        
        if(!empty($result["delete"])){
            $ids = array_keys($result["delete"]);
            
            $set = $this->sql->insert_string($ids);
            
            Query::i()->sql("delete",array(
                                            ":table" => "phone",
                                            ":where" => "id",
                                            ":insert" => $set
                                         ));
        }
        if(!empty($new_phone)){
            $set = $this->sql->insert_string($new_phone);
            reset($new_phone);
            $where = implode(",",array_keys(current($new_phone)));

            Query::i()->sql("insert",array(
                                            ":table" => "phone",
                                            ":where" => $where,
                                            ":set" => $set
                                         ));
        }
        
        return TRUE;
    }
     
}