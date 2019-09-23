<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Method_Pay{

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->user_method = Controller::factory("method","user");
        $this->orders = Controller::factory("orders","user");
    }
    
    function account($order){
        // Определяем за какой аккаунт происходит оплата.
        $user = $this->user_method->get_user(array("id" => $order["id_user"]));
        
        $seor =  $order["amount"] / $order["rate"];
        
        $price = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        $find = current(Arr::search(array("type_name"=>"account", "amount" => $seor),$price));
        
        if(empty($find)){
            throw new Core_Excpeiotn("Нет в прайсе оплаты аккаунта с ценой :seor", array(":seor" => $seor));
        }else{
            $day = 30;
            $days = array(
                        "one"   => 1,
                        "three" => 3,
                        "six"   => 6,
                        "year"   => 12,
                    );
            
            $day = $day * $days[$find["name"]];

            $update = array(
                         "days" => $user["days"] + $day,
                      );

            if($user["id_user_type"] == 1){
                $update["clicks"] = $user["clicks"] + $find["clicks"];
            }else{
                $update["ads"]    = $user["ads"] + $find["adc"];
            }
            
            $set = $this->sql->update(",", $update);
            
            Query::i()->sql("update_where",array(
                                              ":table" => "accounts",
                                              ":set" => $set,
                                              ":where" => sprintf("id_user = %s",$order["id_user"]),
                                           ));
            
            Query::i()->sql("delete", array(
                                        ":table" => "session",
                                        ":where" => "id_user",
                                        ":insert" => sprintf("(%s)",$order["id_user"])
                                      ));
            
            return TRUE;
        }
    }
    function seor($order){
        // Определяем за какой аккаунт происходит оплата.
        $user = $this->user_method->get_user(array("id" => $order["id_user"]));
        
        $seor =  $order["amount"] / $order["rate"];
        
        $update = array(
                         "seor" => $seor + $user["seor"],
                      );
        
        $set = $this->sql->update(",", $update);
        
        Query::i()->sql("update_where",array(
                                          ":table" => "accounts",
                                          ":set" => $set,
                                          ":where" => sprintf("id_user = %s",$order["id_user"]),
                                       ));
        
        Query::i()->sql("delete", array(
                                    ":table" => "session",
                                    ":where" => "id_user",
                                    ":insert" => sprintf("(%s)",$order["id_user"])
                                  ));
        
        return TRUE;
    }
}