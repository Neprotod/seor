<?php defined('SYSPATH') OR exit();
/**
 * Исключение на подключение файла
 *
 * @package    Tree
 * @category   Exceptions
 */
class Core_Exception_Include extends Exception{
    public function __construct($message = NULL, $file_name = NULL,$ext = NULL){
        @ob_clean();
        header('Content-type: text/html charset="utf-8"');
        if($error = Core::find_file('error',$file_name,$ext)){
            include $error;
            exit();
        }else{
            exit('Файл для подключения не найден');
        }
    }
}
