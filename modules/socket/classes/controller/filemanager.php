<?php defined('MODPATH') OR exit();

class Controller_FileManager_Socket{

    public $dir;
    public $file;
    public $correlate;
    
    function __construct(){
        if(empty(Registry::i()->urlArray)){
            Registry::i()->urlArray[] = 'directory';
        }
        $this->dir = Model::factory('filemanager_dir','socket');
        $this->file = Model::factory('filemanager_file','socket');
        $this->correlate = Model::factory('filemanager_correlate','socket');
    }
    
    function fetch(){
        $action = reset(Registry::i()->urlArray);
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
            elseif(isset($_POST['backup'])){
                $_POST['archiv_name'] = "backup".date("Y.m.d");
                $this->dir->archivator();
            }elseif(isset($_POST['UNIX'])){
                if(!isset($_POST['permission']))
                    $_POST['permission'][] = 0;
                $this->dir->unix();
            }
            // Вывод каталога
            $this->dir->scan();
        }
        elseif($action == 'file'){
            if(Request::post('action') == 'save'){
                $this->file->save();
            }
            elseif(isset($_POST['action']['no-redactor'])){
                $_SESSION['file']['no-redactor'] = TRUE;
            }
            elseif(isset($_POST['action']['redactor'])){
                unset($_SESSION['file']['no-redactor']);
            }
            elseif(isset($_POST['action']['redactor_size'])){
                $_SESSION['redactor']['redactor_size'] = $_POST['redactor']['redactor_size'];
            }
            elseif(isset($_POST['action']['scroll'])){            
                if($_POST['scroll'] == 'on'){
                    $_SESSION['file']['scroll'] = TRUE;
                }else{
                    unset($_SESSION['file']['scroll']);
                }
            }
            $this->file->open();
        }
        elseif($action == 'correlate'){
            $this->correlate->fetch();
        }
        elseif($action == 'get'){
            $file = $_POST['tree']['get']['dir'].'/';
            $file .= $_POST['tree']['get']['file'];
            
            echo file_get_contents($file);
            exit();
        }
    }
}