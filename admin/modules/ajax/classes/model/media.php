<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Media_Ajax_Admin{
    
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
        $path = DOCROOT . "media" . DIRECTORY_SEPARATOR;
        $current = Request::get("current");
        $root = urldecode(Request::get("root")) . DIRECTORY_SEPARATOR;
        
        $path .= $root;
        $path .= $current;
        
        $scan = $this->dir->scan($path);

        // Включить потом
        if(!Request::post("file")){
            unset($scan["files"]);
        }
        foreach($scan["directories"] AS &$value){
            if(isset($value["dir"])){
                $value["dir"] = $current ."/".$value["dir"];
                $value["dir"] = trim($value["dir"],'/');
            }
        }

        $xml_pars = "admin_module|ajax::filesystem";
        $xsl_pars = "admin_template|default::filesystem";
        
        $utf = str_replace(DOCROOT," ",$scan["path_utf"]);
        $utf = str_replace("\\","/",$utf);
        $utf = trim($utf);
        
        $tech = array(
            array("svg"=>array(
                "page" => $this->svg->get_svg("page"),
                "category" => $this->svg->get_svg("category")
            )),
            "root" => str_replace("\\","/",$root),
            "current" => $current,
            "path" => str_replace("\\","/",$current),
            "path_utf" => $utf
        );

        $breadcrumb_url = explode("/",$current);
        $breadcrumb_last = array_pop($breadcrumb_url);
        $breadcrumb = array();
        foreach(array_reverse($breadcrumb_url) AS $key => $val){
            $breadcrumb[$key]["name"] = $val;
            $breadcrumb[$key]["url"] = implode("/",$breadcrumb_url);
            array_pop($breadcrumb_url);
        }
        if(!empty($breadcrumb)){
            $breadcrumb = array_reverse($breadcrumb);
        }
        if(!empty($breadcrumb_last)){
            $key = count($breadcrumb);
            $breadcrumb[$key]["name"] = $breadcrumb_last;
        }
        
        $data = array();
        $data["current"] = urlencode($current);
        $data["root"] = urlencode(trim($root,DIRECTORY_SEPARATOR));
        
        $scan["breadcrumb"] = $breadcrumb;

        $data["content"] = $this->xml->preg_load($scan,$xml_pars,$xsl_pars,$tech);
        return $data;
    }
}