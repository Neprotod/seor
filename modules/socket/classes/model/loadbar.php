<?php defined('MODPATH') OR exit();

class Model_loadbar_Socket{
    
    function __construct(){
        
    }
    
    function fetch(){
        $dir = utf8_decode($_POST['dir']);
        $file = pathinfo($_POST['file']);
        $all_size = $this->FBytes($_POST['size']);
        $file_tmp = $dir.'/'.$file['filename'].'.tmp';
        if(is_file($file_tmp)){
            $size = $this->FBytes(($size = filesize($file_tmp))? $size : 0);
            echo "$size - $all_size {$file['filename']}.{$file['extension']}";
        }else{
            $size = 0;
            echo "$size - $all_size {$file['filename']}.{$file['extension']}";
        }
        exit();
    }
    
    function FBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes?log($bytes):0)/log(1024));
        //echo $pow.'<br>';
        $pow = min($pow, count($units)-1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision).' '.$units[$pow];
    }
}