<?php

class FileManager{

    public $dir;
    public $file;
    
    function __construct(){
        if(empty(Registry::i()->action)){
            Registry::i()->action = 'directory';
        }
        include Registry::i()->root.'/classes/dir.php';
        include Registry::i()->root.'/classes/file.php';
        $this->dir = new Dir();
        $this->file = new File();
    }
    
    function fetch(){
        $action = Registry::i()->action;
        if($action == 'directory'){
            // При схоранения в буфер*/
            if(isset($_POST['UTF-8'])){
                $this->dir->convert_utf8();
            }
            elseif(isset($_POST['cp1251'])){
                $this->dir->convert_cp1251();
            }
            elseif(isset($_POST['save'])){
                $_SESSION['directory']['save'] = $_POST['selected'];
            }
            elseif(isset($_POST['clear'])){
                unset($_SESSION['directory']['save']);
            }
            elseif(isset($_POST['past'])){
                $this->dir->past();
            }
            elseif(isset($_POST['cut'])){
                $this->dir->cut();
            }
            elseif(isset($_POST['upload'])){
                $this->dir->upload();
            }
            elseif(isset($_POST['unlink']) AND isset($_POST['selected'])){
                $this->dir->unlink();
            }
            elseif(isset($_POST['rename']) AND isset($_POST['new'])){
                $this->dir->rename();
            }
            elseif(isset($_POST['create_file'])){
                $this->dir->create_file();
            }
            elseif(isset($_POST['create_dir'])){
                $this->dir->create_dir();
            }
            elseif(isset($_POST['archivate']) AND isset($_POST['selected'])){
                $this->dir->archivator();
            }
            elseif(isset($_POST['de_archivate']) AND isset($_POST['selected'])){
                $this->dir->de_archivator();
            }
            elseif(isset($_POST['big_zip']) AND $_SERVER['HTTP_ZIP'] == 'big_zip'){
                $this->dir->big_zip();
            }
            // Вывод каталога
            $this->dir->scan();
        }
        elseif($action == 'file'){
            if($_POST['action'] == 'save'){
                $this->file->save();
            }
            $this->file->open();
        }
        elseif($action == 'error'){
            $error = new xml();
            $error->fetch();
        }
        elseif($action == 'get'){
            $file = $_POST['tree']['get']['dir'].'/';
            $file .= $_POST['tree']['get']['file'];
            
            echo file_get_contents($file);
        }
    }
}