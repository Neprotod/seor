<?php defined('MODPATH') OR exit();
/**
 * Отображение страниц
 * 
 * @package    module
 * @category   page
 */
class Model_Type_Vis_Page{

    public $date_format = "%Y-%m-%d'";
    public $page;
    
    function __construct($page){
        $this->sql = Model::factory("sql","system");
        $this->xml = Module::factory("xml",TRUE);
        
        $this->page = $page;
    }
    
    /**
     * Вывод содержимого страницы
     *
     * @return string контент страницы
     */
    function fetch(){
        $file_name = $this->page['file_name'];
        $xml_pars = "template|default::page_".$file_name;
        $xsl_pars = "template|default::page_".$file_name;
        
        
        if($fields_all = Query::i()->sql("fields.get",array(":type"=>"page",":id"=>$this->page['id']),"name")){
            $fields = array();
            $params = array();
            
            if(isset($fields_all["field"])){
                $fields = $fields_all["field"];
                $fields = explode("||",$fields["text"]);
            }
            
            if(isset($fields_all["params"])){
                $params = $fields_all["params"];
                $params = explode("&&",$params["text"]);
                foreach($params AS $key =>&$value){
                    $value = explode("||",$value);
                }
                unset($fields_all["field"],$fields_all["params"]);
            }
             
            $data = array();
            $data["fields"] = $fields_all;
            $data["field"] = $fields;
            $data["params"] = $params;
        }
        $data["page"] = $this->page;
 
        
        $tech = array(
           "root" => Registry::i()->root,
           "site" => Core::$root_url,
        );
        
        return $this->xml->preg_load($data,$xml_pars,$xsl_pars,$tech);
        
        //exit;
    }
}