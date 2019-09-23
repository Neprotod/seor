<?php defined('MODPATH') OR exit();
// На всякий случай подключаем
Module::factory("error");
/**
 * Модуль для отображения ошибок
 */
class Controller_System_Error extends Error_Module{
    
    function __construct(){
        //Сообщения и ошибки
        $this->alert = &Registry::i()->system_alert;
        
        //Основной модуль преобразования
        $this->xml = Module::factory('xml',TRUE);
        
        //Относительный путь к модулю
        $this->path = Module::mod_path('error',TRUE);
    }
}