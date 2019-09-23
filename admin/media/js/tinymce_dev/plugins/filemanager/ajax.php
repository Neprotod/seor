<?php
    session_start();
    header("Cache-control: no-store,max-age=0");
    header("Content-type: application/json; charset=UTF-8");
    if($_SESSION['fVerify'] !== TRUE){
        exit();
    }
    include "function.php";
    //if($_SESSION["verify"] != "FileManager4TinyMCE" AND $_SESSION["RF"]["verify"] != 'RESPONSIVEfilemanager') die('forbidden');

    //Зарезервированные имена
    $reserve = array('/media/original','/media/error','/media/resize');
    //Меняем корневую директорию
    chdir($_SERVER['DOCUMENT_ROOT']);
    
    //Старый путь к файлу
    if($_POST['charset'] == 'ASCII')
        $old = iconv('UTF-8','cp1251',$_POST['path']);
    else
        $old = $_POST['path'];
    
    //Новый путь к файлу
    if($_POST['type'] == 'file'){
        $_POST['ext'] = (!empty($_POST['ext']))? '.'.$_POST['ext'] : $_POST['ext'];
        $new = $_POST['dir'].$_POST['name'].$_POST['ext'];
    }else{
        $new = $_POST['dir'].$_POST['name'];
    }
    $new = iconv('UTF-8','cp1251',$new);
    
    $return = array('verify'=>'error');
    $return['classes'] = 'alert-danger';
    $return['massage'] = "Файл переименован.";
    //Зарезервированные файлы
    if(in_array($old,$reserve)){
        $prefix = '';
        if(is_dir($old)){
            $prefix = 'Эта папка зарезервирована';
        }else{
            $prefix = 'Этот файл зарезервирован';
        }
        
        $return['massage'] = "<strong>Ошибка!</strong> {$prefix} системой.";
    }
    elseif(!is_file('.'.$old) AND !is_dir('.'.$old)){
        $return['massage'] = "Файл или папка которую нужно переименовать удалены";
    }
    elseif($old != $new){
        if(is_file($new)){
            $return['massage'] = "<strong>Ошибка!</strong> Такое имя файла уже есть.";
        }elseif(is_dir($new)){
            $return['massage'] = "<strong>Ошибка!</strong> Такое имя директории уже есть.";
        }else{
            if(rename('.'.$old,'.'.$new)){
                $return['verify'] = 'complete';
                $return['classes'] = 'alert-success';
                if($_POST['type'] == 'file'){
                    $pathinfo = pathinfo($new);
                    $return['file'] = $pathinfo['basename'];
                }else{
                    $return['folder'] = $_POST['folder'];
                }
                $return['name'] = $_POST['name'];
                $return['path'] = lang_convert($new);
            }else{
                $return['massage'] = "<strong>Ошибка!</strong> Файл не был переименован.";
            }
        }
    }else{
        $return['massage'] = "Имена совпадают.";
    }
    
    echo json_encode($return);
?>