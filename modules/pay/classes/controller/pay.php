<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Pay_Pay{

    const VERSION = '1.0.0';
    
    public $agr = array(
        "liqpay" => array(
                "public_key" =>"i8599663628",
                "private_key" =>"YaYo6ZkIML2dvq6PSb1HbE2gedDp5PYpaI1GAowz"
            )
    );
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        $this->user = Module::factory("user",TRUE);
        $this->method = Controller::factory("method","pay");
        $this->user_method = Controller::factory("method","user");
    }
    
    function fetch(){
        
        $model = "pay_";
        
        $user = $this->user->get();
        
        if($user["employer"] == 1){
            // Передаем управление модели
            $model .= "user";
        }else{
            $model .= "user";
        }
        
        return Model::load($model,"pay","fetch",array($user));
        
    }
    /**
     * Работает, только если есть POST запрос, в противном случае перебрасывает на страницу pay
     *
     *
     *
     */
    function checkout(){
        $this->error->cookie(FALSE);
        
        $xml_pars = "template|default::pay_pay";
        $xsl_pars = "template|default::pay_checkout";
        
        $user = $this->user->get();
        if(empty($user)){
            Request::redirect(Url::root(NULL));
        }
        
        $session = Registry::i()->session;
        
        if(!$order_id = $session->get("order_id")){
            Request::redirect(Core::$root_url . "/account/pay");
        }
        // Разрешены только POST запросы.
        if(Request::method("post")){
            $action = Request::post("action");
            if($action == "cancel"){
                Query::i()->sql("delete", array(
                                            ":table" => "orders_pay",
                                            ":where" => "id",
                                            ":insert" => sprintf("(%s)",$order_id)
                                          ));
                
                $session->delete("order_id");
                
                $this->error->set("message","info",array("message"=>"Платеж был отменен."));
                
                $this->error->save_cookie();
                
                Request::redirect(Core::$root_url . "/account/pay");
            }
        }
        
        
        $data = array();
        $data["user"] = $user;
        
        $data["currency"] = Query::i()->sql("currency.rate",array(),"name");
        
        $order = Query::i()->sql("orders.get_pay",array(
                                                    ":id" => $order_id
                                                  ),NULL, TRUE);
        
        if(empty($order)){
            $session->delete("order_id");
            Request::redirect(Core::$root_url . "/account/pay");
        }
        $data["order"] = $order;
        
        $seor = 0;
        
        $currency = $order["currency_name"];
        $cost = $order["amount"];
        if($order["action_name"] != "seor"){
            $seor = $cost / $order["rate"];
        }
        /////////////////////////////////////////////////
        // Формирует LiqPay
        $pub_key = $this->agr['liqpay']["public_key"];
        $priv_key = $this->agr['liqpay']["private_key"];
        $liqpay = array(
            "version" => 3,
            "action" => "pay",
            "public_key" => $pub_key,
            "amount" => $cost,
            "currency" => $currency,
            "description" => "За інформаційні послуги №" . $order["id"],
            "type" => "buy",
            "server_url" => 'https://seor.ua/account/pay/order/liqpay',
            "result_url" => 'https://seor.ua/',
            "language" => "ru",
            "order_id" => $order["id"],
            "email" => $user["email"],
        );
        
        $l_data = base64_encode(json_encode($liqpay));
        $l_sig = base64_encode( sha1( $priv_key . $l_data . $priv_key, 1 ) );
        
        $liqpay = array(
                            "data" => $l_data,
                            "signature" => $l_sig,
                        );
        /////////////////////////////////////////////////
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "prev" => base64_decode(Request::get("prev")),
            "currency" => $currency,
            "cost" => $cost,
            "seor" => $seor,
            array("error" => $error),
            array("liqpay" => $liqpay),
        );

        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
    
    function order($type = NULL){
        $this->error->cookie(FALSE);
        
        $xml_pars = "template|default::pay_check";
        $xsl_pars = "template|default::pay_check";
        
        $data = array();
        
        $aggregator = Model::factory("aggregator","pay");
        
        $data["dop"] = $aggregator->$type();
        
        $error = $this->error->output();
        
        $tech = array(
            "root" => Registry::i()->root,
            "site" => Core::$root_url,
            "action" => Url::root(NULL),
            "url" => Url::root(FALSE),
            "mark" => Request::get("mark",NULL, "all"),
            "prev" => base64_decode(Request::get("prev")),
            array("error" => $error)
        );
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
    }
}