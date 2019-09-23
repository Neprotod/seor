<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Controller_method_user{

    const VERSION = '1.0.0';
    
    // 1209600  - две недели
    // 2629743  - месяц
    /**
     * @var int сколько живет токен
     */
    public $lifetime = 2629743;
    
    function __construct(){
        $this->error = Module::factory("error",TRUE);
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
    }
  
    function get_user_page($data){
        $id_table = 0;
        if(is_array($data)){
            if(!isset($data["id_table"])){
                throw new Core_Exception("В массиве нет ячейки <b>id_table</b>");
            }
            $id_table = $data["id_table"];
        }else{
            $id_table = $data;
        }
        
        return Query::i()->sql("user.get_user_page",array(":id" => $id_table),NULL,TRUE);
    }
    
    function get_user($data, $ignore = FALSE, $any = FALSE){
        if(isset(Registry::i()->user) AND !$ignore)
            return Registry::i()->user;
        
        if(!$any)
            $data["status"] = 1;
        
        $table = array(
            "id" => "u",
            "email" => "u",
            "password" => array(
                            'prefix'=> 'u',
                            'col_name'=> 'pass'
                        ),
            "pass" => "u",
            "status" => "u",
        );
        
        if($table = $this->sql->intersect($data, $table)){
            $table = $this->sql->where("AND", $table);
            $user = Query::i()->sql("user.get",array(":where"=>$table),NULL,TRUE);
            if($user){
                // Проверяем адекватность дат
                /*if($user["expiration"] != $user["test_expiration"]){
                    echo "<pre>";
                    var_dump($user);
                    exit;
                }*/
                if($ignore)
                    return $user;
                return Registry::i()->user = $user;
            }
        }
        
        return FALSE;
    }
    
    function logout(){
        Cookie::delete("token");
    }
    
    function token($user = NULL, $token = NULL, $reloaded = FALSE){
        if(isset(Registry::i()->token))
            return Registry::i()->token;
        
        if(isset(Registry::i()->user))
            $user = Registry::i()->user;

        if(($key = Cookie::get("token") OR $key = $token) AND $reloaded == FALSE){
            $key = DB::escape($key);
            
            $where = "token = $key AND lifetime > NOW()";
            
            if($token = Query::i()->sql("tokens.get",array(":where"=>$where),NULL,TRUE)){
                if($this->get_user(array("id"=>$token["id_user"]))){
                  return Registry::i()->token = $token;  
                }else{
                    Cookie::delete("token");
                    return FALSE;
                }
            }else{
                Cookie::delete("token");
                return FALSE;
            }
        }else{
            if($user){
                if(!isset($user["id"])){
                    // если нет ID пытаемся найти по email
                    if(!$user = $this->get_user($user)){
                        return FALSE;
                    }
                }
                $id_user = $user["id"];
                
                $where = "id_user = $id_user AND lifetime > NOW()";
                $token = Query::i()->sql("tokens.get",array(":where"=>$where),NULL,TRUE);

                if(!$token){
                    $key = md5($user["id"].$user["email"].time());
                    
                    $timestamp = date('Y-m-d G:i:s',time()+$this->lifetime);
                    
                    Query::i()->sql("tokens.insert",array(
                                                ":id_user" => $id_user,
                                                ":token" => $key,
                                                ":lifetime" => $timestamp,
                                            ));
            
                    $token = Query::i()->sql("tokens.get",array(":where"=>$where),NULL,TRUE);
                }else{
                    $key = $token["token"];
                }
                
                Cookie::set("token",$key,0x7FFFFFFF);
                
                return Registry::i()->token = $token;
            }else{
                return FALSE;
            }
        }
        
        return TRUE;
    }
    
    function logs_login($user = NULL){
        if(!$user AND isset(Registry::i()->user)){
            $user = Registry::i()->user;
        }
        if(!$user)
            throw new Core_Exception('Пустой массив $user');
        // Таблицы в базе
        $table = array(
            "id_user" => NULL,
            "user_agent" => NULL,
            "ip" => NULL,
        );
        // Заполняем данные
        $user_logs = array();
        $user_logs["id_user"] = $user["id"];
        $user_logs["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
        $user_logs["ip"] = $_SERVER["REMOTE_ADDR"];
        
        $logs_test = $this->sql->intersect($user_logs,$table);

        $where = $this->sql->where("AND",$logs_test);
        
        $id = Query::i()->sql("logs.login.insert",array(
                        ":id_user" => $user_logs["id_user"],
                        ":user_agent" => $user_logs["user_agent"],
                        ":ip" => $user_logs["ip"]
                    ));
        // Записываем
        $id = current($id);
        Query::i()->sql("logs.login.insert_time",array(
                        ":id" => $id
                    ));
        
    }
    function took_days($user = NULL){
            // Если есть дни, нужно проверить, нужно ли их снимать.
            if($user["days"]){
                // Проверяем есть ли дата последнего снятия
                if($user["took_days"]){
                    $date = new DateTime();
                    $took_days = new DateTime($user["took_days"]);
                    // Сколько дней снять
                    if($day = $date->diff($took_days)->d){
                        $days = $user["days"] - $day;
                        // Если значение отрицательное или равно нулю, обнулением дату
                        $where = '';
                        if($days <= 0){
                            $days = 0;
                            $where = "took_days = NULL";
                            // Отправляем уведомление
                            Registry::i()->session->delete("note_lowday");
                            Controller::factory("notification","user")->presets($user,"verylowdays");
                        }else{
                            // Уведомления что осталось меньше 5-и дней
                            if($days <= 5 AND !Registry::i()->session->get("note_lowday")){
                                Registry::i()->session->set("note_lowday",true);
                                Controller::factory("notification","user")->presets($user,"lowdays",array("day"=>$days));
                            }else{
                                Registry::i()->session->delete("note_lowday");
                            }
                            
                            $where = "took_days = took_days";
                        }
                        
                        $set = sprintf('%3$s + INTERVAL %1$s DAY, days = %2$s, expiration = took_days + INTERVAL %2$s DAY', $day, $days, $where);
                    }else{
                        // Проверяем совпадают ли даты завершения
                        if($user["expiration"] != $user["test_expiration"]){
                            $set = sprintf('expiration = %s', DB::escape($user["test_expiration"]));
                        }
                    }
                }else{
                    $set = sprintf("took_days = NOW(), expiration = NOW() + INTERVAL %s DAY",$user["days"]);
                }
            }else{
                if($user["expiration"]){
                    $set = "took_days = NULL, expiration = NULL";
                }
            }
        try{
            if(isset($set)){
                // Начинаем транзакцию
                Query::i()->sql("transaction.start");
                
                Query::i()->sql("update_where",array(
                                                    ":table" => "accounts",
                                                    ":set"   => $set,
                                                    ":where" => sprintf("id_user = %s", $user["id"]),
                                                ));
                // Завершаем транзакцию
                Query::i()->sql("transaction.commit");
            }
        }catch(Exception $e){
            // Что-то пошло не так, возвращает все
            Query::i()->sql("transaction.rollback");
            
            // Обрабатываем ошибку
            Core_Exception::client($e);
        }
    }
    
    function get_fields($user_id){
        if(is_array($user_id)){
            $user_id = $this->sql->insert_string($user_id, FALSE);
        }
        
        return Query::i()->sql("user.field.get",array(":id_user" => $user_id));
    }
    
    function media_user_path($id = NULL){
        if(empty($id)){
            $id = Registry::i()->user["id"];
        }

        return sprintf("media/user/u%s/",$id);
    }
}