<?php defined('MODPATH') OR exit();

class Model_Mysql_Sql_Socket{
    public $tables;
    public $backup;
    protected $connect_error;
    function __construct(){
        $this->backup = Model::factory('mysql_backup','socket');
        
        try{
            $this->tables = $this->backup->show_table();
        }catch(Exception $e){
            // Если базы нету
            if($e->db_code()){
                $_GET["file"] = "database.php";
                $_GET["dir"] = "./application/config";
                $file = Model::factory('filemanager_file','socket');
                if($_POST['action'] == 'save'){
                    $file->save();
                    header("Location: /".Url::instance());
                }else{
                    ob_start();
                    echo "<h2>".$e->getMessage()."</h2>";
                    echo "<div>Укажите правельную базу данных:</div>";
                    /*if($return = $file->open(TRUE)){
                        echo "<div><form method=\"post\"><textarea></textarea><input name=\"action\" type=\"hidden\" value=\"save\" /></form></div>";
                    }*/
                    $file->open();
                    $this->connect_error = ob_get_clean();
                }
                
                //exit();
            }
            
        }
    }
    
    function fetch(){
        if(!empty($this->connect_error)){
            echo $this->connect_error;
            return;
        }
        if(Request::post("drop")){
            if($table = Request::post("table"))
                $this->sql("DROP TABLE {$table}", 4);
            $this->tables = $this->backup->show_table(TRUE);
        }
        elseif(Request::post("save")){
            $table = Request::post("table");
            $key = Request::post("key");
            $this->backup->create_file(array($this->tables[$key]),array($this->tables[$key]));
            exit();
        }
        if($mysql = Request::post("mysql")){
            $result = $this->sql($mysql['sql'], $mysql['type']);
            Registry::i()->massage = "Запрос выполнен";
        }
        
        echo View::factory('mysql_sql_fetch','socket',array('result'=>$result,'tables'=>$this->tables,'database'=>$this->backup->db_name));
    }
    
    function sql($sql,$type){
        $sql = DB::placehold($sql);
        $type = intval($type);
        $query = DB::query($type, $sql);
        try{
            $result = $query->execute();
        }catch(Exception $e){
            $result = array(
                "Massage" => $e->getMessage(),
            );
        }
        return $result;
    }
}