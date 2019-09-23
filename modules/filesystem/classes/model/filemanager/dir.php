<?php defined('MODPATH') OR exit();

class Model_Filemanager_Dir_Filesystem{

    public $file;
    public $directory;
    public $size;
    
    function __construct(){
        $this->file = array();
        $this->directory = array();
    }
    
    // Для отображения страницы файлов и директорий
    function scan($dir){
        $direct = dir($dir);
        // Сохраняем путь к папке
        $path = $direct->path;
        
        // Отберем каталоги и файлы
        $this->size = array();
        while (false !== ($entry = $direct->read())){
            if($entry == '.' OR $entry == '..')
                continue;
            if(is_file($direct->path.'/'.$entry)){
                $file = $entry;
                $tmp_file = array();
                //Str::charset($entry);
                if(Str::charset($entry) === 'ASCII'){
                    $tmp_file['name'] = iconv('cp1251','UTF-8',$entry);
                }else{
                    $tmp_file['name'] = $entry;
                }
                $tmp_file['file'] = utf8_encode($file);
                
                // Узнаем размер
                $this->size[$tmp_file['name']] = $this->FBytes(filesize($path.'/'.$file));

                $tmp_file['size'] = $this->size[$tmp_file['name']];
                
                $path_parts = pathinfo($path.'/'.$file);
                $tmp_file['exp'] = $path_parts['extension'];
                $tmp_file['filename'] = $path_parts['filename'];
                
                $this->file[] = $tmp_file;
            }else{
                $tmp_dir = array();
                if(Str::charset($entry) === 'ASCII'){
                    $tmp_dir['name'] = iconv('cp1251','UTF-8',$entry);
                }else{
                    $tmp_dir['name'] = $entry;
                }
                $tmp_dir['dir'] = utf8_encode($entry);
                $this->directory[] = $tmp_dir;
            }
        }
        asort($this->file);
        asort($this->directory);
        // Для кнопки назад
        $back = explode('/',$path);
        if(!empty($back) AND is_array($back)){
            array_pop($back);
            $back = implode('/',$back);
            if(empty($back))
                $back = NULL;
        }else{
            $back = NULL;
        }
        $direct->close();
        
        /*Проверка на path, для адекватных ссылок*/
        if(Str::charset($path) === 'ASCII'){
            $path_utf = iconv('cp1251','UTF-8',$path);
        }else{
            $path_utf = $path;
        }
        
        // Массив для передачи в шаблон
        $fond = array(
            'directories'=>$this->directory,
            'files'=>$this->file,
            'path'=>utf8_encode($path),
            'path_utf'=>$path_utf,
            'back'=>utf8_encode($back)
        );
        
        return $fond;
    }
    
    // Переименовать файл или директорию
    function rename($old, $new, $dir){
        $old = utf8_decode($old);
        $new = $new;
        $dir = trim(utf8_decode($dir),"/\\ ") . DIRECTORY_SEPARATOR;
        
        return rename($dir.$old,$dir.$new);
    }
    
    // Удаляет все файлы и саму директорию 
    function unlink($paths = array()){
        $dir = trim(key($paths),"/\\ ").DIRECTORY_SEPARATOR;
        $drops = array();
        foreach(reset($paths) as $unlink){
            $unlink = $unlink;
            // Кодировка для windows
            /*if(Core::$is_windows){
                $unlink = (mb_detect_encoding($unlink) == 'UTF-8')? iconv('UTF-8','cp1251',$unlink):$unlink;
            }*/
            if(is_file($dir.$unlink)){
                $drops['file'][] = $dir.$unlink;
                unlink($dir.$unlink);
            }else{
                $drops['directory'][] = $dir.$unlink;
                $this->unlink_directory($dir.$unlink);
            }
        }
        return $drops;
    }
    
    // Для удаления файлов и директорий внутри директории
    function unlink_directory($dir){
        if ($objs = glob($dir."/*")){
            foreach($objs as $obj) {
                is_dir($obj) ? $this->unlink_directory($obj) : unlink($obj);
            }
        }
        rmdir($dir);
    }
    
    // Вырезает из одного места и вставляет в другое
    function cut(){
        $newDir = $this->dir.'/';
        $oldDir = utf8_decode(key($_SESSION['directory']['save'])).'/';
        foreach($_SESSION['directory']['save'] as $save){
            $save = reset($save);    
            $save = utf8_decode($save);    
            rename($oldDir.$save,$newDir.$save);
        }
        unset($_SESSION['directory']['save']);
    }
    
    // Для глубокого скана директорий
    private function deep_scan($old,$new,&$fonds = NULL,$no_root = FALSE){
        // Кодировка для windows
        /*if(Core::$is_windows){
            $old = (mb_detect_encoding($old) == 'UTF-8')? iconv('UTF-8','cp1251',$old):$old;
            $new = (mb_detect_encoding($new) == 'UTF-8')? iconv('UTF-8','cp1251',$new):$new;
        }*/
        if(empty($fonds))
            $fonds = array();
        if($no_root === FALSE)
            $fonds[$old] = $new;

        $scans = scandir($old);
        
        foreach($scans as $fond){
            // Кодировка для windows
            /*if(Core::$is_windows){
                $fond = (mb_detect_encoding($fond) == 'UTF-8')? iconv('UTF-8','cp1251',$fond):$fond;
            }*/
            if($fond == '.' OR $fond == '..')
                continue;
            if(is_file($old.'/'.$fond)){
                $fonds[$old.'/'.$fond] = $new.'/'.$fond;
            }else{
                if($no_root === FALSE)
                    $fonds[$old.'/'.$fond] = $new.'/'.$fond;
                $fonds += $this->deep_scan($old.'/'.$fond,$new.'/'.$fond,$fonds,$no_root);
            }
        }
        return $fonds;
    }
    
    // Переводит байты в мегабайты
    /*
     * @param int/string количество байт
     * @param int сколько цифр после запятой
     * @return преобразованое значение вплоть до терабайта
     */
    function FBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes?log($bytes):0)/log(1024));
        //echo $pow.'<br>';
        $pow = min($pow, count($units)-1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision).' '.$units[$pow];
    }
    // Конвертирует все папки и файлы в UTF-8
    function convert_utf8() {
        foreach(reset($_POST['selected']) as $select){
            // Разкодируем
            $select = utf8_decode($select);
        
            $cp1251 = $select;
            $utf8 = iconv('cp1251','UTF-8',$select);
            rename($this->dir.'/'.$cp1251,$this->dir.'/'.$utf8);
        }
        
    }
    // Конвертирует все папки и файлы в UTF-8
    function convert_cp1251() {
        foreach(reset($_POST['selected']) as $select){
            // Разкодируем
            $select = utf8_decode($select);
            $utf8 = $select;
            $cp1251 = iconv('UTF-8','cp1251',$select);
            rename($this->dir.'/'.$utf8,$this->dir.'/'.$cp1251);
        }
        
    }
    
    function unix(){
        $resurs = utf8_decode($_POST['encode_name']);
        $num = 0;
        foreach($_POST['permission'] as $permission){
            $num += $permission;
        }
        $chmod = base_convert($num,8,10);
        if(is_dir($this->dir.'/'.$resurs) OR is_file($this->dir.'/'.$resurs))
            chmod($this->dir.'/'.$resurs,$chmod);
    }
    
    // Загружает файл из строки
    function upload_form($dir, $file_rename = NULL){
        $this->create_dir($dir);
        $uploads_dir = $dir;
        $files = $_FILES;
        $upload_file = array();
        foreach($files as $name => $file){
            if($file_rename)
                $file_name = $file_rename;
            else
                $file_name = $file["name"];
            $uploads_dir = trim($uploads_dir,"/\\ ") . DIRECTORY_SEPARATOR;
            $path = $uploads_dir . $file_name;
            $upload_file[] = $path;
            
            move_uploaded_file($file['tmp_name'],$path);
        }
        return $upload_file;
    }
    
    // Создать файл
    function create_file($dir, $file){
        $dir = trim($dir,"/\\") . DIRECTORY_SEPARATOR;
        $file = trim($file,"/\\");
        if(!is_dir($dir)){
            
        }
        return (bool)fopen($dir.$file,'a');
    }
    
    // Создать директорию
    function create_dir($dir, $direcorty = '', $mask = 0777){
        $dir = trim($dir,"/\\");
        if($direcorty){
            $direcorty =  DIRECTORY_SEPARATOR . trim($direcorty,"/\\");
        }
        
        $path = realpath($dir.$direcorty);
        if(!is_dir($path)){
            // Если что поменять на 0777
            return mkdir($dir.$direcorty,$mask,TRUE);
        }else{
            return FALSE;
        }
        
    }
}