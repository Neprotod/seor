<?php defined('MODPATH') OR exit();


class Model_Auxiliary_System_Admin{
    
    
    function __construct(){
        $this->sql = Model::factory("sql","system");
    }
    
    /*
     * Получаем тип контента
     *
     * @param  string тип
     * @param  string дополнительный where
     * @return array  вернет найденную страницу либо пустой массив
     */
    function get_content_type($type, $where = NULL){
        return Admin_Query::i()->sql("auxiliary.content_type",array(
                                                                ":type"=>$type,
                                                                ":where"=>$where,
                                                                ), 'id');
    }

    /*
     * Получаем дополнительные поля категории
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function get_fields($id, $type){
        return Admin_Query::i()->sql("auxiliary.fields",array(
                                                                ":type"=>$type,
                                                                ":id"=>$id,
                                                                ), 'id');
    }
    /*
     * Получаем дополнительные поля категории
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function drop_fields($ids){
        if($ids){
            $ids = $this->sql->insert_string($ids);
            return Admin_Query::i()->sql("delete",array(
                                                                ":table"=>"fields",
                                                                ":where"=>"id",
                                                                ":insert"=>$ids,
                                                                ));
        }
        return FALSE;
    }
    
    /*
     * Получаем дополнительные поля категории
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function insert_fields($insert){
        $table_name = array(
            "name" => NULL
        );
        if(isset($insert["name"])){
            $fields_name = Admin_Query::i()->sql("fields.fields_name",array(":name"=>$insert["name"]),NULL,TRUE);
        
            $insert["id_name"] = $fields_name["id"];
        }
       
        $insert["where"] = !isset($insert["where"])?"var":$insert["where"];
        
        $to_insert = array();
        foreach($insert AS $key => $val){
            $to_insert[":".$key] = $val;
        }
        
        return Admin_Query::i()->sql("fields.insert",$to_insert);
    }
    /*
     * Обновляем поля категории
     *
     * @param int id страницы
     * @return array вернет найденную страницу либо пустой массив
     */
    function update_fields($update, $id){
        $table = array(
            "var" => NULL,
            "text" => NULL,
        );
        
        
        
        $update_table = $this->sql->intersect($update, $table);
        $update_table = $this->sql->update(',', $update_table); 
        
        if($update_table){
            return Admin_Query::i()->sql("update",array(":set"=>$update_table,":id"=>$id,":table"=>"fields"));
        }
        
        return FALSE;
    }
}