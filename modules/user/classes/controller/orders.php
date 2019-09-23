<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_Orders_User{

    const VERSION = '1.0.0';
    
    /**
     * @var bool если в TRUE значит авторизация уже была проведена в прошлом
     */
    static $auth = FALSE;
    
    function __construct(){
        $this->orders_detail = Query::i()->sql("orders.get_detail",array(), "name");
        
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Model::factory("sql","system");
    }
    
    function set_orders($id_user, $orders_detail, $amount){
        if(!isset($this->orders_detail[$orders_detail])){
            throw new Core_Exception("Нет такой детали заказа как :detail",array(":detail" => $orders_detail));
        }
        try{
            Query::i()->sql("transaction.savepoint",array(":set" => "order"));
            
            $id_detail = $this->orders_detail[$orders_detail]["id"];
        
            $transaction = md5($id_user.$orders_detail.time());
            
            $state = 1;
            
            $insert = array($id_user, $id_detail, $amount, $state, $transaction);
            
            $set = $this->sql->insert_string($insert);

            Query::i()->sql("insert",array(
                                        ":table" => "orders",
                                        ":where" => "id_user, id_orders_detail, amount, state, transaction",
                                        ":set"   => $set
                                    ));
                                    
            Query::i()->sql("transaction.release_savepoint",array(":set" => "order"));
            return TRUE;
        }catch(Exception $e){
            Query::i()->sql("transaction.rollback_savepoint",array(":set" => "order"));
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
        }
        
    }
}