<?php

class File{
    public $file;
    function __construct(){
        if($_GET['file'])
            $this->file = utf8_decode($_GET['file']);
        if($_GET['dir'])
            $this->dir = utf8_decode($_GET['dir']);
        $this->path = $this->dir.'/'.$this->file;
    }
    
    function open(){
        if(is_file($this->path)){
            $content = file_get_contents($this->path);
            
            if(isset($_POST['encode'])){
                $content = iconv($_POST['encode']['old'],$_POST['encode']['new'],$content);
            }
            
        }else{
            echo 'Не файл';
        }
        
        $fond = array(
            'content' => $content
        );
        
        include Registry::i()->root.'/view/file.php';
    }
    
    function save(){
        if(isset($_POST['content'])){
            $tmp = $this->dir.'/'.'temp_'.$this->file;
            if(file_put_contents($tmp,html_entity_decode($_POST['content'])) !== FALSE){
                unlink($this->path);
                rename($tmp,$this->path);
            }else{
                return Registry::i()->massage = 'Ошибка записи';
            }
            Registry::i()->massage = 'Файл обнавлен';
        }
    }
}
