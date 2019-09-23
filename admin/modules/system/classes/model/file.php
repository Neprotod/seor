<?php defined('MODPATH') OR exit();

/*
 * Модель определяет типы данных и переписывает их
 */
class Model_File_System_Admin{
    
    //@var string основная директория типов данных
    public $content = "content";
    
    function __construct(){}
    
    /*
     * 
     *
     * @param  
     * @return 
     */
    function get_types($id,$type = NULL){}
}