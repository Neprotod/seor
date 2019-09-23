<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Notification_Ajax{
    function __construct(){
        $this->user = Module::factory("user",TRUE);
        $this->error = Module::factory("error",TRUE);
    }
    
    function fetch(){
        $xml_pars = "module|ajax::notification_notification";
        $xsl_pars = "module|ajax::notification_notification";
        
        $data = array();
        
        $user = Registry::i()->user;
        if($user){
            $data = Query::i()->sql("user.notification.get",array(":id_user"=>$user["id"]),"id");
            
            // Запросы на снятие уведомления
            Query::i()->sql("update_where",array(
                                    ":table"=>"accounts",
                                    ":set"=>"notification = 0",
                                    ":where"=>"id_user = " . $user["id"],
                                ));
            // Запросы на снятие уведомления
            Query::i()->sql("update_where",array(
                                    ":table"=>"notification",
                                    ":set"=>"seen = 1",
                                    ":where"=>"id_user = " . $user["id"],
                                ));
            
            
            return Module::factory("xml", TRUE)->preg_load($data,$xml_pars,$xsl_pars);
        }
        return '';
    }
    function balance(){
        $xml_pars = "module|ajax::notification_balance";
        $xsl_pars = "module|ajax::notification_balance";
        
        if($data = Registry::i()->user){
            return Module::factory("xml", TRUE)->preg_load($data,$xml_pars,$xsl_pars);
        }
        return '';
    }
    function promo(){
        
        $data = array();
        
        if($promo = Request::post("promo","str")){
            // Пришел ключ, нужно его проверить.
            $promo = Query::i()->sql("promo.get",array(":promo"=>$promo),NULL,TRUE);
        
            $user = Registry::i()->user;

            if($promo){
                $error = 0;
                $data["success"] = 1;
                if(Query::i()->sql("logs.promo.get",array(":id_user"=>$user["id"],":id_promo"=>$promo["id"]))){
                    // Промокод использован
                    $promo = array();
                    $error = 1;
                    $data["success"] = 0;
                }
            }else{
                $error = 2;
                $data["success"] = 0;
            }
            if(Request::post("insert") AND !$error){
                $data["success"] = 1;
                $data["promo"] = $promo;
                try{
                    //Устанавливаем промокод
                    Query::i()->sql("transaction.start");
                    
                    // Если это одноразовый промокод то выключаем его
                    if($promo["once"]){
                        Query::i()->sql("update",array(
                                                        ":table" => "promo",
                                                        ":set" => "status = 0",
                                                        ":id" => $promo["id"],
                                                    ));
                    }
                    // Устанавливаем промокод, что он был использован пользователем
                    Query::i()->sql("logs.promo.insert",array(
                                                    ":id_user" => $user["id"],
                                                    ":id_promo" => $promo["id"],
                                                ));
                    
                    // Добавляем значения в аккаунт
                    $table = array(
                                "seor" => NULL,
                                "days" => NULL,
                                "clicks" => NULL,
                                "ads" => NULL,
                            );
                    
                    $sql = Model::factory("sql","system");
                    if(!$user["employer"]){
                        unset($promo["ads"]);
                    }
                    
                    
                    $table = $sql->intersect($promo, $table);
                    
                    $update = '';
                    foreach($table AS $key => $value){
                        $update .= sprintf('%1$s = %1$s + %2$s,',$key, DB::escape($value["value"]));
                    }
                    $update = trim($update,",");
                    
                    Query::i()->sql("update_where",array(
                                                ":table" => "accounts",
                                                ":set" => $update,
                                                ":where" => sprintf("id_user = %s",$user["id"]),
                                            ));
                    Query::i()->sql("transaction.commit");
                    
                    // Обновляем дату окончания аккаунта
                    Registry::i()->session->delete("note_lowday");
                    Controller::factory("method","user")->took_days($user);
                    
                    // Сохраняем сообщение об успехе
                    $this->error->set("message","info",array("message"=>"Промокод установлен."));
                    $this->error->save_cookie();
                }catch(Exception $e){
                    // Откатываем изменения
                    Query::i()->sql("transaction.rollback");
                    
                    // Что-то пошло не так. Отправляем письмо разработчику
                    Model::factory('exception','system')->set_xml($e,array('client'=>'true'));
                    $data["success"] = 0; 
                }
            }else{
                $promo["error"] = $error;
                $promo["employer"] = $user["employer"];
                
                $xml_pars = "module|ajax::notification_promo";
                $xsl_pars = "module|ajax::notification_promo";
                    
                $data["content"] = Module::factory("xml", TRUE)->preg_load($promo,$xml_pars,$xsl_pars);
            }
        }else{
            $data["title"] = "Промокод";
            $data["content"] = View::factory("notification_promo","ajax");
        }
        return $data;
    }
}