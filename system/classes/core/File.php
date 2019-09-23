<?php defined('SYSPATH') OR exit();
/**
 * Класс обработки путей и файлов
 *
 * @package   Tree
 * @category  Core
 */
class Core_File{
    
    /**
     * Данный метод соединяет строки так, что бы это было путем для файла или директории
     *
     * concat($path1[,$path2, $...])
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param  string строка части пути
     * @return string путь к файлу или папке
     */
    static function concat(){
        $fond = '';
        
        //Если есть аргументы, соедяем их
        if($pats = func_get_args()){
            foreach($pats as $path)
                if(!is_array($path) AND !empty($path))
                    $fond .= trim($path,'/') . '/';
            return trim($fond,'/');
        }else{
            return FALSE;
        }
    }
    
    /**
     * Проверяет существует ли папка или файл
     *
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param  string $dir    путь к папке
     * @param  string $file   имя файла
     * @param  string $ext    расширение файла
     * @param  bool   $return если TRUE вернет имя файла даже если его не существует
     * @return string         если существует, вернет полный путь
     */
    static function exist($dir, $file = NULL, $ext = NULL, $return = FALSE){
        $dir = trim($dir,'/').'/';
        
        if ($ext === NULL){
            // Используем расширение по умолчанию
            $ext = EXT;
        }
        elseif(!empty($ext)){
            // Используем заданное расширение
            $ext = ".{$ext}";
        }
        
        if(!empty($file)){
            $file = $dir.$file.$ext;
            if($return === TRUE)
                return $file;
            if(is_file($file)){
                return $file;
            }
        }else{
            if(is_dir($dir)){
                return $dir;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Возвращает содержимое файла
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param  string $file   путь к файлу
     * @param  bool   $encode TRUE если нужно перекодировать HTML символы
     * @return string         содержимое файла
     * @return bool           FALSE если файла нет
     */
    static function get_content($file,$encode = FALSE){
        if(is_file($file)){
            if($encode === TRUE)
                return Str::html_encode(file_get_contents($file));
            else
                return file_get_contents($file);
        }
        return FALSE;
    }
    /**
     * Записывает содержимое в файл
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param  string $file    путь к файлу
     * @param  bool   $content TRUE если нужно перекодировать HTML символы
     * @return string $encode  содержимое файла
     * @return bool            FALSE если файла нет
     */
    static function set_content($file,$content,$encode = FALSE){
        if(is_file($file)){
            if($encode === TRUE)
                return file_put_contents($file,Str::html_decode($content));
            else
                return file_put_contents($file,$content);
        }
        return FALSE;
    }
    /**
     * Создает директорию если ее нет
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @return string $dir  путь к папке
     * @return int    $mode права доступа
     * @return bool         FALSE если произошла ошибка
     */
    static function create_dir($dir,$mode = 0777){
        $dir = trim($dir,'/');
        if(!is_dir($dir)){
            if(mkdir($dir,$mode,TRUE))
                return $dir;
            return FALSE;
        }
        return TRUE;
    }
    /**
     * Создает пустой файл
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param string $file полный путь
     * @return bool        FALSE если произошла ошибка
     */
    static function create_file($file){
        if(empty($file))
            throw new Core_exception('Переменная $file пуста, создать путь к файлу невозможно');
        //Разбиваем путь для проверки
        $pathinfo = pathinfo($file);
        
        $file = $pathinfo['basename'];
        
        $dir = $pathinfo['dirname'];
        
        //Проверяем и создаем дерикторию если ее нет
        $dir = trim($dir,'/').'/';
        
        if(!is_dir($dir) AND !self::create_dir($dir)){
            return FALSE;
        }
        
        //Имя файла и путь
        $file = $dir.$file;
        //Создаем файл
        if($file = fopen($file,'a')){
            fclose($file);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Удаляет файл
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param string $file полный путь
     * @return bool        FALSE если произошла ошибка
     */
    static function unlink($file){
        
        $pathinfo = pathinfo($file);
        if(!isset($pathinfo['extension']))
            $pathinfo['extension'] = NULL;
        if($pathinfo['basename'] AND $file = File::exist($pathinfo['dirname'],$pathinfo['filename'],$pathinfo['extension'])){
            return unlink($file);
        }
        
        return FALSE;
    }
    /**
     * Переименовываем файл
     *
     * [!!!] Функция не обрабатывает разделить - \
     *
     * @param  string $canonical полный путь к файлу
     * @param  string $rename    полный путь для замены имени файла
     * @param  bool   $this_dir  переименование только если директории совпадают
     * @return bool              FALSE если произошла ошибка
     */
    static function rename($canonical, $rename,$this_dir = FALSE){
        
        $path_canonical = pathinfo($canonical);
        $path_rename = pathinfo($rename);
        
        if(!isset($path_canonical['extension']))
            $path_canonical['extension'] = NULL;
        
        //Если файла нет, создаем его
        if(!File::exist($path_canonical['dirname'],$path_canonical['filename'],$path_canonical['extension'])){
            File::create_file($canonical);
        }
        
        //Если файл существует не переименовываем
        if(File::exist($path_rename['dirname'],$path_rename['filename'],$path_rename['extension'])){
            return FALSE;
        }
        
        if($this_dir === FALSE){
            return rename($canonical,$rename);
        }elseif($path_canonical['dirname'] == $path_rename['dirname']){
            return rename($canonical,$rename);
        }
        
        return FALSE;
    }
}