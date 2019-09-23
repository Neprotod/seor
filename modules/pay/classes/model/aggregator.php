<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Aggregator_Pay {

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","pay");
        $this->user_method = Controller::factory("method","user");
    }
    
    function liqpay(){
        $data = array();
        $data_l = json_decode(base64_decode($_POST["data"]), TRUE);
        
        $order_id = $data_l["order_id"];
        
        $status = $data_l["status"];
        
        $order = Query::i()->sql("orders.get_pay",array(
                                                    ":id" => $order_id
                                                  ),NULL, TRUE);
        
        if($status == "sandbox" OR $status == "success" OR $status == "wait_accept"){
            if($order){
                    //Находим пользователя
                $user = $this->user_method->get_user(array("id" => $order["id_user"]));
                
                $data["type"] = "success";
                
                if($order["state"] == 1){
                    $data["title"] = "Платеж был произведен ранее";
                    $data["message"] = "Вы уже можете пользоваться купленной услугой.";
                }else{
                    $action = $order["action_name"];
                    
                    try{
                        Query::i()->sql("transaction.start");
                        
                        if($result = $this->method->$action($order)){
                            Query::i()->sql("update", array(
                                                ":table" => "orders_pay",
                                                ":set" => "state = 1",
                                                ":id" => $order["id"]
                                              ));
                                              
                            $data["title"] = "Seor Coin были начислены.";
                            
                            Query::i()->sql("transaction.commit");
                        }else{
                            throw new Core_Exception("Оплата не прошла");
                        }
                    }catch(Exception $e){
                         // Что-то пошло не так, возвращает все
                        Query::i()->sql("transaction.rollback");
                        
                        $data["type"] = "danger";
                        $data["title"] = "Оплата не прошла";
                        $data["message"] = "По каким-то причинам оплата не прошла. Приносим свои извинения, обратитесь в тех поддержку, если у вас есть квитанция, оплату проведут в ручном режиме.";
                        
                        $this->drop_session($order);
                        
                        // Обрабатываем ошибку
                        Core_Exception::client($e);
                    }
                }
            }else{
                $data["type"] = "danger";
                $data["title"] = "Такой оплаты не существует";
                $data["message"] = "Возможно вы попали сюда случайно";
            }
        }elseif($status == "failure"){
            $data["type"] = "danger";
            $data["title"] = "Платеж отменен";
            $data["message"] = "Создайте новый платеж.";
            $this->drop_session($order);
        }
        return $data;
    }
    function seor(){
        $data = array();
        
        $order_id = Request::post("order_id");
        
        $order = Query::i()->sql("orders.get_pay",array(
                                                    ":id" => $order_id
                                                  ),NULL, TRUE);
                                                  
        $session = Registry::i()->session;
        
        if($order){
            //Находим пользователя
            $user = $this->user_method->get_user(array("id" => $order["id_user"]));
            
            $data["type"] = "success";
            
            if($order["state"] == 1){
                $data["title"] = "Платеж был произведен ранее";
                $data["message"] = "Вы уже можете пользоваться купленной услугой.";
            }else{
                $action = $order["action_name"];
                $seor =  $order["amount"] / $order["rate"];
                
                if($seor > $user["seor"]){
                    $data["type"] = "danger";
                    $data["title"] = "Недостаточно Seor Coin";
                    $data["message"] = "На вашем счету недостаточно Seor Coin.";
                    
                    $session->delete("order_id");
                    // Удаляем
                    Query::i()->sql("delete", array(
                                            ":table" => "orders_pay",
                                            ":where" => "id",
                                            ":insert" => sprintf("(%s)",$order_id)
                                          ));
                }else{
                    // Проводим оплату.
                    try{
                        Query::i()->sql("transaction.start");
                        
                        if($result = $this->method->$action($order)){
                            Query::i()->sql("update", array(
                                                ":table" => "orders_pay",
                                                ":set" => "state = 1",
                                                ":id" => $order["id"]
                                              ));
                            
                            $set = sprintf("seor = seor - %s",$seor);
                            
                            Query::i()->sql("update_where",array(
                                                  ":table" => "accounts",
                                                  ":set" => $set,
                                                  ":where" => sprintf("id_user = %s",$order["id_user"]),
                                               ));
                                              
                            $data["title"] = "Аккаунт продлен.";
                            
                            $this->drop_session($order);
                            
                            Query::i()->sql("transaction.commit");
                        }else{
                            throw new Core_Exception("Оплата не прошла");
                        }
                    }catch(Exception $e){
                         // Что-то пошло не так, возвращает все
                        Query::i()->sql("transaction.rollback");
                        
                        $data["type"] = "danger";
                        $data["title"] = "Оплата не прошла";
                        $data["message"] = "По каким-то причинам оплата не прошла. Приносим свои извинения, обратитесь в тех поддержку, если у вас есть квитанция, оплату проведут в ручном режиме.";
                        
                        $this->drop_session($order);
                        
                        // Обрабатываем ошибку
                        Core_Exception::client($e);
                    }
                }
                
            }
            
            
        }else{
            $data["type"] = "danger";
            $data["title"] = "Такой оплаты не существует";
            $data["message"] = "Возможно вы попали сюда случайно";
        }
        
        return $data;
    }
    
    
    protected function drop_session($order){
        Query::i()->sql("delete", array(
                                        ":table" => "session",
                                        ":where" => "id_user",
                                        ":insert" => sprintf("(%s)",$order["id_user"])
                                      ));
    }
    protected function drop_pay($order){
        Query::i()->sql("delete", array(
                                        ":table" => "orders_pay",
                                        ":where" => "id",
                                        ":insert" => sprintf("(%s)",$order["id"])
                                      ));
        Query::i()->sql("delete", array(
                                        ":table" => "session",
                                        ":where" => "id_user",
                                        ":insert" => sprintf("(%s)",$order["id_user"])
                                      ));
    }
}