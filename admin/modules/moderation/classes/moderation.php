<?php

class Moderation_Admin{
    function __construct(){
        $this->user = Controller::factory('method','user');
        $this->error = Module::factory("error",TRUE);
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function fetch(){
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        Registry::i()->title = "Модерирование";
        
        $data = array();
        
        $data["content"] = $this->count_moderation();
        
        $data["content"] .= $this->count_verification();
        
        return Admin_Template::factory(Registry::i()->template,"content_moderation_fetch",$data);
    }
    function ads($id = NULL){
        Registry::i()->active_menu = "moder";
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        Registry::i()->title = "Модерирование вакансий";
        
        $data = array();
        if(!$id){
            $data["content"] = $this->get_ads();
        }else{
            $data["content"] = $this->get_ad($id);
        }
        
        return Admin_Template::factory(Registry::i()->template,"content_moderation_fetch",$data);
    }
    function verification($id = NULL){
        Registry::i()->active_menu = "moder";
        if($this->error->success()){
            Registry::i()->errors = $this->error->output();
        }
        
        Registry::i()->title = "Подтверждение пользователей";
        
        $data = array();
        if(!$id){
            $data["content"] = $this->get_verfs();
        }else{
            $data["content"] = $this->get_verf($id);
        }
        
        return Admin_Template::factory(Registry::i()->template,"content_moderation_fetch",$data);
    }
    
    protected function get_verfs(){
        $data = array();
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_verfs";
        $xsl_pars = "admin_template|default::moderation_verfs";
        
        $data = array();
        $data["ads"] = Admin_Query::i()->sql("moderation.get_verfs",array());

        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    protected function get_verf($id){
        $data = array();
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_verf";
        $xsl_pars = "admin_template|default::moderation_verf";
        
        $data = array();
        
        $data["users"] = Admin_Query::i()->sql("users.get",array(":where" => "u.id = " . $id),NULL,TRUE);
        
        if(Request::method("post")){
            try{
                Query::i()->sql("transaction.start");
                $submit = Request::post("submit");
                
                // Подтверждение
                if($submit == 1){
                    Query::i()->sql("update_where",array(
                                                    ":table" => "accounts",
                                                    ":set"   => "complete = 2",
                                                    ":where" => sprintf("id_user = %s", DB::escape($id)),
                                                ));
                    
                    // Отправляем уведомление на сайт
                    $notefivation = Controller::factory("notification","user");
                    $notefivation->presets($data["users"],"verification",array("note"=>"Теперь у вас появится значок щита в аккаунте.","title" => "Ваши данные подтверждены."));

                    // Отправляем письмо.
                    $mail = Module::factory("mail",TRUE);
                    
                    $mail->driver("smtp");
                    
                    $mail->isHTML(TRUE);
                   
                    $mail->to($data["users"]["email"]);
                    $mail->from(NULL,"SEOR");
                    $mail->subject("Ваши данные подтверждены.");
                    
                    echo $mail->view("verificationc",array());

                    $mail->send();
                }
                // Отказ
                elseif($submit == 2){
                    
                    $note = Request::post("note");

                    Query::i()->sql("delete",array(
                                                    ":table" => "user_verification",
                                                    ":where"   => "id_user",
                                                    ":insert" => sprintf("(%s)",$id),
                                                ));
                    
                    if(!$note){
                        $note = "Данные не прошли модерацию.";
                    }
                    
                    // Отправляем уведомление на сайт
                    $notefivation = Controller::factory("notification","user");
                    $notefivation->presets($data["users"],"verification",array("note"=>$note));
                    
                    // Отправляем письмо.
                    $mail = Module::factory("mail",TRUE);
                    
                    $mail->driver("smtp");
                    
                    $mail->isHTML(TRUE);
                   
                    $mail->to($data["users"]["email"]);
                    $mail->from(NULL,"SEOR");
                    $mail->subject("Ваши данные не прошли модерацию.");
                    
                    $mail->view("verification",array("note" => $note));
                    
                    $mail->send();
                }
                
                
                Query::i()->sql("transaction.commit");
                Request::redirect(Core::$root_url."/admin/moderation/verification");
            }catch(Exception $e){
                Query::i()->sql("transaction.rollback");
                Core_Exception::handler($e);
            }
        }
        
        $detail = Admin_Query::i()->sql("moderation.get_verf",array(":id" => $id),NULL,TRUE);
        
        $data["detail"] = json_decode($detail["detail"], true);
        $data["file"] = $data["detail"]["file"];
        unset($data["detail"]["file"]);
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    protected function get_ads(){
        $data = array();
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_ads";
        $xsl_pars = "admin_template|default::moderation_ads";
        
        $data = array();
        $data["ads"] = Admin_Query::i()->sql("moderation.get_ads",array());

        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    protected function get_ad($id){
        $data = array();
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_ad";
        $xsl_pars = "admin_template|default::moderation_ad";
        
        $data = array();
        $data["ads"] = Admin_Query::i()->sql("moderation.get_ad",array(
                                                                ":id" => $id 
                                                            ),NULL,TRUE);
                                                            
        $data["ads_language"] = Query::i()->sql("ads.language",array(
                                                                ":id" => $id 
                                                            ),"id_language");
                                                            
        $data["ads_specialization"] = Query::i()->sql("ads.specialization",array(
                                                                ":id" => $id 
                                                            ),"id_specialization");
        // Берем заметки
        $data["note"] = Query::i()->sql("note.ads",array(
                                                            ":id_ads" => $id 
                                                        ));
        
        $data["user"] = $this->user->get_user(array("id" => $data["ads"]["id_user"]), true);
        
        if(Request::method("post")){
            try{
                Query::i()->sql("transaction.start");
                // СНЯТЬ ЗАМЕТКУ МОДЕРАТОРА ЕСЛИ ОНА ЕСТЬ.
                if(!empty($data["note"])){
                    Query::i()->sql("delete",array(
                                            ":table" => "moderator_notes",
                                            ":where" => "id_ads",
                                            ":insert"   => sprintf("(%s)",DB::escape($data["ads"]["id"])),
                                        ));
                }
                $submit = Request::post("submit");
                $time = Request::post("time","int",0);
                if($submit == 1){
                    $ads_test = array();
                    $ads_test["title"] = $data["ads"]["title"];
                    $ads_post["title"] = Request::post("title");
                    $ads_test["description"] = $data["ads"]["description"];
                    $ads_post["description"] = Request::post("description");
                    
                    $result = $this->sql->insert_update($ads_test, $ads_post);
                    $update = $result["update"];
                    $update["approved"] = 1;
                    $update["seen"] = 1;
                    $update["status"] = 1;
                    
                    
                   
                    $set = $this->sql->update(",",$update);
                    
                    if($time == 1 OR empty($data["ads"]["time"])){
                        $set .= ",time = NOW()";
                        $set  = trim($set,",");
                    }
                    
                    if($set){
                        Query::i()->sql("update",array(
                                                        ":table" => "ads",
                                                        ":set"   => $set,
                                                        ":id" => $data["ads"]["id"],
                                                    ));
                    }
                }
                elseif($submit == 2){
                    
                    $note = Request::post("note");
                    $data["note"] = $note;
                    if($note){
                        $to_insert = array();
                        $to_insert["id_ads"] = $data["ads"]["id"];
                        $to_insert["note"] = $note;
                        
                        $set = $this->sql->insert_string($to_insert);
          
                        Query::i()->sql("insert",array(
                                                        ":table" => "moderator_notes",
                                                        ":where"   => "id_ads, note",
                                                        ":set" => $set,
                                                    ));
                    }
                    $update = array();
                    $update["approved"] = $submit;
                    $update["seen"] = 1;
                    $update["status"] = 0;
                    
                    $set = $this->sql->update(",",$update);
                    
                    if($set){
                        Query::i()->sql("update",array(
                                                        ":table" => "ads",
                                                        ":set"   => $set,
                                                        ":id" => $data["ads"]["id"],
                                                    ));
                    }
                    
                    // Отправляем уведомление на сайт
                    $notefivation = Controller::factory("notification","user");
                    $notefivation->presets($data["user"],"rejected",array("ads"=>$data["ads"]));
                    
                    // Отправляем письмо.
                    $mail = Module::factory("mail",TRUE);
                    
                    $mail->driver("smtp");
                    
                    $mail->isHTML(TRUE);
                   
                    $mail->to($data["user"]["email"]);
                    $mail->from(NULL,"SEOR");
                    $mail->subject("Вакансия не прошла модерацию.");

                    $mail->view("rejected",$data);
                    
                    $mail->send();
                }
                
                
                Query::i()->sql("transaction.commit");
                Request::redirect(Core::$root_url."/admin/moderation/ads");
            }catch(Exception $e){
                Query::i()->sql("transaction.rollback");
                Core_Exception::handler($e);
            }
        }
        
        if($data["ads"]["seen"] == 0){
            Query::i()->sql("update",array(
                                            ":table" => "ads",
                                            ":set"   => "seen = 2",
                                            ":id" => $data["ads"]["id"],
                                        ));
        }
        
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    protected function count_moderation(){
        $data = array();
        
        $count_ads = Admin_Query::i()->sql("moderation.count_ads",array(),NULL, TRUE);
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_count";
        $xsl_pars = "admin_template|default::moderation_count";
        
        $data = array();
        $data["count_ads"] = $count_ads;
        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    protected function count_verification(){
        $data = array();
        
        $count_ads = Admin_Query::i()->sql("moderation.count_verification",array(),NULL, TRUE);
        
        //Создаем отображение
        $xml_pars = "admin_template|default::moderation_verificationCount";
        $xsl_pars = "admin_template|default::moderation_verificationCount";
        
        $data = array();
        $data["count_ads"] = $count_ads;

        $tech = array(
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "session_id" => Session::i()->id(),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}