<?php

class User_Admin implements I_Module{

    function index(){
        if(empty($_COOKIE['session_admin'])){
            if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
                $init = Admin_Model::factory('init', 'user');
                $init->login(TRUE);
            }else{
                $init = Admin_Model::factory('init', 'user');
                $init->login();
            }
        }else{
            session_id($_COOKIE['session_admin']);
            session_start();
            if($_SESSION['admin'] != 'admin'){
                
                setcookie('session_admin', '', time()-86400);
                header("Location: /".Url::instance());
            }
        }
    }
    function loguot(){
        setcookie('session_admin', '', time()-86400);
        header("Location: /");
    }
}