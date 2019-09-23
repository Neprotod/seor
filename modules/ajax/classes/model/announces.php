<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Announces_Ajax{
    
    function __construct(){
        $this->method = Controller::factory("method","ads");
        $this->account = Controller::factory("account","user");
        $this->user = Module::factory("user",TRUE);
    }
    
    function fetch(){
        Registry::i()->founds["url"] = "ads";
        
        $user = $this->user->get();
        
        $data = $this->method->get_ads(NULL, $user);
        
        $return = array("content" => $data["content"]);
        
        return $return;
    }
    function active_deactive(){
        $data = array();
        $data["error"] = Str::__("Что-то пошло не так и статус объявления не изменился.");
        $id = Request::post("id","int");
        $status = Request::post("status","int");
        
        $ad = Query::i()->sql("ads.get",array(
                                                    ":id" => $id 
                                                ),NULL,TRUE);
        
        if($ad["approved"] == 1){
            try{
                Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => sprintf("status = %s",DB::escape($status)),
                                                ":id" => $id,
                                            ));
                unset($data["error"]);
                $data["content"] = Str::__("Статус объявления изменен.");
            }catch(Exception $e){
                Core_Exception::client($e);
            }
        }
        
        return $data;
    }
    function drop(){
        $data = array();
        $data["title"] = Str::__("Удаление объявлений");
        
        try{
            $id = Request::post("id","int");
            $drop = Request::post("drop","int", false);
            if($drop){
                $user = $this->user->get();
                
                $ad = Query::i()->sql("ads.get",array(
                                                        ":id" => $id 
                                                    ),NULL,TRUE);
                if($user["id"] == $ad["id_user"]){
                    if($ad["status"] == 3){
                        Query::i()->sql("delete",array(
                                                ":table" => "ads",
                                                ":where"   => "id",
                                                ":insert" => sprintf("(%s)",$ad["id"]),
                                            ));
                    }else{
                        Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => "status = 2",
                                                ":id" => $ad["id"],
                                            ));
                    }
                    $data["content"] = "Вакансия удалена.";
                }else{
                    throw new Core_Exception("Попытка удаления вакансии <b>№:id</b> которая принадлежит пользователю :user_ads, пытается ее удалить пользователь :id_user",array(":id" => $ad["id"],":user_ads" => $ad["id_user"],":id_user" =>$user["id"]));
                }
            }else{
                $user = $this->user->get();
                
                
                $ad = Query::i()->sql("ads.get",array(
                                                        ":id" => $id 
                                                    ),NULL,TRUE);
                
                if($user["id"] == $ad["id_user"]){
                    $data["content"] = Str::__("Ваша вакансия \":title\" будет удалена.",array(":title" => $ad["title"]));
                
                    $data["id"] = $ad["id"];
                }else{
                    $data["error"] = Str::__("Это не ваша вакансия");
                    
                }
                
            }
        }catch(Exception $e){
            Core_Exception::client($e);
            $data["error"] = Str::__("Вакансия не удалена, из за технических проблем. Тех поддержка уже уведомлена.");
        }
        return $data;
    }
    function publish(){
        $data = array();
        $data["title"] = Str::__("Публикация объявления.");
        
        $id = Request::post("id","int");
        $publish = Request::post("publish","int");
        
        $user = $this->user->get();
        try{
            $ad = Query::i()->sql("ads.get",array(
                                                    ":id" => $id 
                                                ),NULL,TRUE);
            $data["id"] =  $ad["id"];
            
            if($user["id"] != $ad["id_user"]){
                $data["error"] = Str::__("Это не ваша вакансия.");
                return $data;
            }
            if($user["days"] == 0){
                $data["error"] = Str::__('Период обслуживания аккаунта окончен, все объявления не активны, <a href="/account/pay">продлите аккаунт</a>, что бы опубликовать объявление.');
                return $data;
            }
            
            $data["ads_title"] = $ad["title"];
            
            if(!$this->test_to_pay($data,$user)){
                return $data;
            }
            
            
            
            if($data['flag'] == TRUE AND $publish){
                Query::i()->sql("transaction.start");
                // Поднимаем вверх списка
                Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => "status = 0, approved = 0, pay = 1, seen = 0",
                                                ":id" => $ad["id"],
                                            ));
                // Оплата
                $this->account->pay_ad($ad["id"], $user["id"], intval($data["cost"]), "create");
                
                Query::i()->sql("transaction.commit");
                $data["content"] = "Вакансия опубликована";
                $data["seor"] = $user["seor"] - $data["cost"];
                $data["ads"] = ($data["cost"] == 0)?$user["ads"] - 1: $user["ads"];
            }else{
                $data["content"] = View::factory("ads_up","ajax", $data);
            }
            
            return $data;
        }catch(Exception $e){
            
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $data["error"] = Str::__("Вакансия не опубликовалась. Тех поддержка уже уведомлена.");
            return $data;
        }
    }
    function up(){
        $data = array();
        $data["title"] = Str::__("Поднять объявление вверх");
        //$data["content"] = Str::__("Ваша вакансия");
        
        $id = Request::post("id","int");
        $up = Request::post("up","int");
        
        $user = $this->user->get();
        
        try{   
            $ad = Query::i()->sql("ads.get",array(
                                                    ":id" => $id 
                                                ),NULL,TRUE);
            $data["id"] =  $ad["id"];
            
            if($user["id"] != $ad["id_user"]){
                $data["error"] = Str::__("Это не ваша вакансия.");
                return $data;
            }
            if($user["days"] == 0){
                $data["error"] = Str::__('Период обслуживания аккаунта окончен, все объявления не активны, <a href="/account/pay">продлите аккаунт</a>, что бы поднять объявление.');
                return $data;
            }
            if($ad["pay"] != 1){
                $data["error"] = Str::__("Вакансия не была оплачена, ее нельзя поднять.");
                return $data;
            }
            if($ad["approved"] != 1){
                $data["error"] = Str::__("Вакансия не прошла модерацию, ее нельзя поднять.");
                return $data;
            }
            
            //$price = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
            
            $data["ads_title"] = $ad["title"];
            /*
            $data["cost"] = 0;
            
            $flag = 0;
            
            if($user["ads"]){
                $flag = 1;
            }else{
                $cost = 0;
                
                $price = Arr::search(array("type_name" => "up"), $price);
                $price = Arr::value_key($price,"name");
                
                $cost = floor($price["ads_up"]["amount"]);
                
                $data["cost"] = $cost;
                
                if($cost > $user["seor"]){
                    $data["error"] = Str::__('Недостаточно средств, <a href="/account/pay">пополните счет</a>.');
                    return $data;
                }else{
                    $flag = 1;
                }
            }*/
            if(!$this->test_to_pay($data,$user)){
                return $data;
            }
            
            
            
            if($data['flag'] == TRUE AND $up){
                Query::i()->sql("transaction.start");
                // Поднимаем вверх списка
                Query::i()->sql("update",array(
                                                ":table" => "ads",
                                                ":set"   => "time = NOW()",
                                                ":id" => $ad["id"],
                                            ));
                // Оплата
                $this->account->pay_ad($ad["id"], $user["id"], intval($data["cost"]), "up");
                
                Query::i()->sql("transaction.commit");
                $data["content"] = "Вакансия вверху списка";
            }else{
                $data["content"] = View::factory("ads_up","ajax", $data);
            }
            
            return $data;
        }catch(Exception $e){
            
            Query::i()->sql("transaction.rollback");
            Core_Exception::client($e);
            $data["error"] = Str::__("Вакансия не удалось поднять вверх списка. Тех поддержка уже уведомлена.");
            return $data;
        }
    }
    
    function test_to_pay(&$data, $user, $type_name = "up", $price_name = "ads_up"){
        $price = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
            
        $data["cost"] = 0;
        
        $data["flag"] = 0;
        
        if($user["ads"]){
            $data["flag"] = 1;
        }else{
            $cost = 0;
            
            $price = Arr::search(array("type_name" => $type_name), $price);
            $price = Arr::value_key($price,"name");
            
            $cost = floor($price[$price_name]["amount"]);
            
            $data["cost"] = $cost;
            
            if($cost > $user["seor"]){
                $data["error"] = Str::__('Недостаточно средств, <a href="/account/pay">пополните счет</a>.');
                return FALSE;
            }else{
                $data["flag"] = 1;
            }
        }
        return TRUE;
    }
}