<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Filesystem_Ajax_Admin{
    
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
        $root = '';
        $root = urldecode(Request::get("root"));
        $root .= !empty($root)? DIRECTORY_SEPARATOR : '';
        
        $path .= $root;
        $path .= $current;
        
        $parent_root = Url::root(NULL);
        
        $scan = $this->dir->scan($path);
        
        foreach($scan["directories"] AS &$value){
            if(isset($value["dir"])){
                $value["dir"] = $current ."/".$value["dir"];
                $value["dir"] = trim($value["dir"],'/');
            }
        }

        $xml_pars = "admin_module|ajax::filesystem";
        $xsl_pars = "admin_module|ajax::filesystem";
        
        $utf = str_replace(DOCROOT,"",$scan["path_utf"]);
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
            "path_utf" => $utf,
            "dir_path" => $scan["path_utf"],
            "mod_path" => Admin_Module::mod_path("ajax",TRUE),
            "parent_root" => $parent_root,
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
    
    function add(){
        $data = array();
        $dir = Request::post("dir");
        
        if($data["file"] = $this->dir->upload_form($dir)){
            $data["content"] = 'Файл добавлен.';
        }else{
            $data["content"] = 'Файл не добавлен.';
        }
        return $data;
    }
    function drop(){
        $dir = Request::post("dir");
        $paths = array();
        $paths[$dir][] = trim(Request::post("drop"),"/\\");
        if($this->dir->unlink($paths)){
            return 'Удалено.';
        }else{
            return 'Ничего не удалено.';
        }
    }
    function rename(){
        $dir = Request::post("dir");
        $old = Request::post("old_name");
        $new = Request::post("new_name");

        if($this->dir->rename($old,$new,$dir)){
            return 'Переименовано.';
        }else{
            return 'Ничего не переименовано.';
        }
    }
    function create_dir(){
        $data = array();

        $dir = Request::post("dir");
        $new_dir = Request::post("new_dir");
        
        if($this->dir->create_dir($dir,$new_dir)){
           $data["content"] = "Папка создана.";
        }else{
            $dop_text = "";
            if(is_dir($dir.$new_dir))
                $dop_text .= " Такая папка уже существует.";
            $data["error"] = "Папка не создана." . $dop_text;
        }
        
        return $data;
    }
}