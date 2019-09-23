<?php defined('MODPATH') OR exit();

class Controller_Mysql_Socket{

    public $sql;
    
    function __construct(){
        if(empty(Registry::i()->urlArray)){
            Registry::i()->urlArray[] = 'sql';
        }
        $this->sql = Model::factory('mysql_sql','socket');
        $this->backup = Model::factory('mysql_backup','socket');
    }
    
    function fetch(){
        if(Request::post('action')){
            $this->action();
        }
        $action = reset(Registry::i()->urlArray);
        if($action == 'sql'){
            $this->sql->fetch();
        }
        elseif($action == 'backup'){
            $this->backup->fetch();
        }
    }
    function action(){
        if(isset($_POST['action']['no-redactor'])){
            $_SESSION['mysql']['no-redactor'] = TRUE;
        }
        elseif(isset($_POST['action']['redactor'])){
            unset($_SESSION['mysql']['no-redactor']);
        }
        elseif(isset($_POST['action']['redactor_size'])){
            $_SESSION['redactor']['redactor_size'] = $_POST['redactor']['redactor_size'];
        }
    }
}