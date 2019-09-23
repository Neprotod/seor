<?php defined('MODPATH') OR exit();

class Controller_Filemanager_Server{
    
    /**
     * @var array расширения которые нельзя скачивать по ссылке.
     */
    protected $extension = array('php','txt','htaccess','html','htm',);
    /**
     * @var корневая директория.
     */
    
    function __construct(){
        //$_SERVER['DOCUMENT_ROOT'];
    }
    
    /**
     * Возвращает массив всех найденных файлов и 
     * 
     * @return array возвращает массив
     */
    function dir($dir = NULL,$backlist = TRUE){
        $root = strtr($_SERVER['DOCUMENT_ROOT'],'/','\\');
        if(getcwd() != $root){
            chdir($root);
        }
        
        $dir = $this->parse_dir($dir);
        
        $return = array();
        
        //Хлебные крошки
        if($backlist){
            $backlist = explode('/',trim($dir,'/'));
            $backlist = array_flip($backlist);
            $backlist = Arr::fill($backlist, FALSE);
            end($backlist);
            $backlist[key($backlist)] = TRUE;
            $return['backlist'] = $backlist;
        }
        
        foreach(scandir($dir) AS $file_dir){
            if($file_dir == '.' OR $file_dir == '..'){
                continue;
            }
            
            if(is_file($dir.$file_dir)){
                $return['file'][$dir.$file_dir]['name'] = $file_dir;
                $return['file'][$dir.$file_dir]['size'] = @filesize($dir.$file_dir);
                $return['file'][$dir.$file_dir]['time'] = @filemtime($dir.$file_dir);
            }else{
                $return['dir'][$dir.$file_dir]['name'] = $file_dir;
                $return['dir'][$dir.$file_dir]['time'] = @filemtime($dir.$file_dir);
            }
        }
        
        return $return;
    }
    
    function get($file,$encode = NULL){
        $server = Module::factory('server',TRUE);
        if(!empty($encode) AND isset($server->decode[$encode])){
            $file = $server->decode[$encode]($file);
        }
        $file = './'.ltrim($file,'./');
        if(is_file($file)){
            return file_get_contents($file);
        }else{
            return FALSE;
        }
    }
    
    function set($file,$content,$new_name = NULL){
        $data = array();
        $data['path'] = $file;
        if(is_file($file)){
            if(file_get_contents($file) != $content){
                $data['content'] = TRUE;
                file_put_contents($file,$content);
            }
            //Переименование.
            if(!empty($new_name)){
                if($data['rename'] = $this->rename($file,$new_name))
                    $data['path'] = $new_name;
            }
        }else{
            $data['error'] = 'no_file';
        }
        return $data;
    }
    
    /**
     * Переименование файла
     */
    function rename($file,$new_name){
        if(is_file($file)){
            if(!is_file($new_name)){
                return rename($file,$new_name);
            }
        }
        return FALSE;
    }
    
    /**
     * @return array возвращает массив
     */
    function file($file = NULL,$dir = NULL){
        
    }
    
    
    /**
     * 
     * 
     * @param  string путь к файлу или директории
     * @result string правильный путь к файлу или директории
     */
    protected function parse_dir($dir){
        $dir = trim($dir,'./');
        if(empty($dir)){
            $dir = './';
        }else{
            $dir = './'.$dir.'/';
        }
        
        return $dir;
    }
}