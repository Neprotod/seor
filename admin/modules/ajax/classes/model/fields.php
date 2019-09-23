<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Fields_Ajax_Admin{
    
    public $dir;
    public $xml;
    public $sql;
    public $svg;
    
    function __construct(){
        $this->dir = Model::factory("filemanager_dir","filesystem");
        $this->xml = Module::factory("xml",TRUE);
        $this->sql = Admin_Model::factory("sql","system");
        $this->svg = Admin_Model::factory('svg','system');
    }
    
    function fetch(){
        
    }
    function add_fields(){
       $id = Request::post("id");
       $id_table = Request::post("id_table");
       $id_type = Request::post("id_type");
       $name = Request::post("name");
       $var = Request::post("var");
       $where = Request::post("where");
       $position = Request::post("position","int",0);
       $data = array();
       if($id){
            $update = array(
                $where => $var,
                "position" => $position,
            );
            $update = $this->sql->update(',', $update);
            $name = Admin_Query::i()->sql("update",array(
                ":id"=>$id,
                ":table"=>"fields",
                ":set"=>$update,
            ));
            $data["id"] = $id;
       }else{
            $name = Admin_Query::i()->sql("fields.fields_name",array(":name"=>$name),NULL,TRUE);
            $name = $name["id"];
            $id = Admin_Query::i()->sql("fields.insert",array(
                                        ":id_table"=>$id_table,
                                        ":id_type"=>$id_type,
                                        ":id_name"=>$name,
                                        ":var"=>$var,
                                        ":where"=>$where,
                                        ":position"=>$position,
                                        ));
            $data["id"] = $id[0];
       }
       return $data;
    }
}