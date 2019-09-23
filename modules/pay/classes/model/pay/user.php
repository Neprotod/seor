<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Pay_User_Pay {

    const VERSION = '1.0.0';
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","pay");
        $this->user_method = Controller::factory("method","user");
    }
    
    function fetch($user){
        $this->error->cookie(FALSE);
        
        $currency_default = "UAH";
        
        $data = array();
        
        $currency_type = strtoupper(Request::get("currency","str",$currency_default));
        
        $xml_pars = "template|default::pay_pay";
        $xsl_pars = "template|default::pay_user";
        
        // Берем соотношения валют
        $data["currency"] = Query::i()->sql("currency.rate",array(),"name");
        
        unset($data["currency"]["SEOR"]);
        
        $currency = isset($data["currency"][$currency_type])
                            ?$data["currency"][$currency_type]
                            :$data["currency"][$currency_default];
        
        $data["price"] = Query::i()->sql("price.get",array(":user_type" => $user["id_user_type"]));
        
        $data["user"] = $this->user_method->get_user(array());
        
        $url = Core::$root_url .Url::root(FALSE) . "/checkout";
        $session = Registry::i()->session;
        
        // Проверяем существует ли у пользователя не оплаченные заявки. 
        if($order_id = $session->get("order_id")){
            $this->error->set("message","info",array("message"=>"У вас есть незаконченные платежи. Вы должны оплатить или отменить платеж, для новых операций."));
            $this->error->save_cookie();
            // Если заявка существует, перенаправляем на оплату сразу.
            Request::redirect($url);
        }
        
        // Если пришел пост запрос
        if(Request::method("post")){
            $currency_post = strtoupper(Request::post("currency","str"));
            $type = "";
            // Определяем тип покупки
            if($account = Request::post("account")){
                $type = "account";
                
                $price = $data["price"];
                
                $key = key($account);
                
                $find = current(Arr::search(array("type_name"=>"account", "name" => $key),$price));
                
                $rate = $data["currency"][$currency_post]["rate"];
                
                $cost = $find["amount"] * $rate;
            }else{
                $type = "seor";
                $cost = Request::post("currency");
                $currency_post = strtoupper(Request::post("currency_type","str"));
            }
            
            $order_type = Query::i()->sql("orders.orders_action",array(
                                                        ":where" => "name",
                                                        ":id"    => $type,
                                                   ), NULL, TRUE);
           
            $order_description = Query::i()->sql("orders.get_detail_where",array(
                                                        ":where" => "name",
                                                        ":id"    => $type,
                                                   ), NULL, TRUE);

            $type_id = $order_type["id"];
            $order_description_id = $order_description["id"];
            
            $set = array(
                "id_user" => $user["id"],
                "id_orders_detail" => $order_description_id,
                "amount" => $cost,
                "id_currency" => $data["currency"][$currency_post]["id_currency"],
                "state" => 0,
                "id_order_action" => $type_id,
            );
            
            $where = implode(",",array_keys($set));
            
            $set = $this->sql->insert_string($set);
            
            try{
                Query::i()->sql("transaction.start");
                
                // Заполняем данные для создания заявки на оплату.
                $ids = Query::i()->sql("insert",array(
                                            ":table" => "orders_pay",
                                            ":where" => $where,
                                            ":set" => $set,
                                        ));
                $session->set("order_id", $ids[0]);
                
                Query::i()->sql("transaction.commit");
                Request::redirect($url);
            }catch(Exception $e){
                 // Что-то пошло не так, возвращает все
                Query::i()->sql("transaction.rollback");
                
                $this->error->set("error","danger",array("message"=>"Не получить оформить заявку, тех поддержка уже уведомлена. Приносим извинения за неудобство."));
                // Обрабатываем ошибку
                Core_Exception::client($e);
            }
        }
        
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "prev" => base64_decode(Request::get("prev")),
            "currency" => $currency,
            array("error" => $error)
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}