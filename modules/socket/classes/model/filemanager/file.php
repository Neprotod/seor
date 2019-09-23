<?php defined('MODPATH') OR exit();

class Model_Filemanager_File_Socket{
    public $file;
    public $dir = "";
    function __construct(){
        if($file = Request::get('file'))
            $this->file = utf8_decode($file);

        if($dir = Request::get('dir'))
            $this->dir = utf8_decode($dir);
        $this->path = $this->dir.'/'.$this->file;
    }
    
    function open($return = FALSE){
        if(is_file($this->path)){
            $content = file_get_contents($this->path);
            
            if(isset($_POST['encode'])){
                $content = iconv($_POST['encode']['old'],$_POST['encode']['new'],$content);
            }
            
        }else{
            echo 'Не файл';
        }
        
        $fond = array(
            'content' => htmlspecialchars($content,ENT_NOQUOTES),
            'file' => $this->file
        );
        if($return === TRUE)
            return $fond;
        echo View::factory('filemanager_file_file','socket',$fond);
    }
    
    function save(){
        if(isset($_POST['content'])){
            // Обратное преобразование
            $_POST['content'] = htmlspecialchars_decode($_POST['content'],ENT_NOQUOTES);
            $tmp = $this->dir.'/'.'temp_'.$this->file;
            if(file_put_contents($tmp,Request::post('content')) !== FALSE){
                unlink($this->path);
                rename($tmp,$this->path);
            }else{
                return Registry::i()->massage = 'Ошибка записи';
            }
            Registry::i()->massage = 'Файл обнавлен';
        }
    }
}
