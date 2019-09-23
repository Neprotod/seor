<?php

class Model_Init_User_Admin{

    function login($bool = FALSE){
        if($bool === FALSE){
            echo Admin_View::factory('login','user');
            exit();
        }
            
        $login = Request::post('login', 'string');
        $pass = Request::post('pass', 'string');
        if(!empty($login) AND !empty($pass)){
            $msg = $this->authorization($login, $pass);
        }else{
            $msg = 'Есть незаполненное поле';
        }
        
        echo Admin_View::factory('login','user',array('login'=>$login,'msg'=>$msg));
        exit();
    }
    
    function authorization($login, $pass){
        $login = strtolower($login);
        $pass = md5($pass);
        $sql = "SELECT login, pass, mail, created, status 
                    FROM __users
                    WHERE login = :login AND pass = :pass";
                    
        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':login',$login);
        $query->param(':pass',$pass);
        $result = $query->execute();
        
        if(empty($result[0])){
            return 'Неверный логин или пароль';
        }
        
        /*Заполняем информацию*/
        $result = $result[0];
        
        $user = md5($result['login'].$result['pass']);
        
        
        
        session_start();
        
        setcookie('session_admin', session_id(), time()+86400);
        
        $_SESSION['admin'] = 'admin';
        $_SESSION['user'] = md5($result['login']);
        $_SESSION['check'] = $user;
        
        header("Location: /".Url::instance());
    }
    
}