<?php defined('SYSPATH') OR exit();

class Core_Xml{
    /**
     * Возвращает новый объект Вида. Если вы не определяете параметр "Файл",
     * вам необходимо вызвать [View::set_filename].
     *
     *     $view = View::factory($file);
     *
     * @param   string  $file   view filename
     * @param   string  $module module name
     * @param   array   $data   array of values
     * @return  View
     */
    static function xml($file = NULL, $module = NULL, array $data = NULL){
        return Core_Xml_Xml::factory($file, $module, $data);
    }
    
    /**
     * Возвращает новый объект Вида. Если вы не определяете параметр "Файл",
     * вам необходимо вызвать [View::set_filename].
     *
     *     $view = View::factory($file);
     *
     * @param   string  $file   view filename
     * @param   string  $module module name
     * @param   array   $data   array of values
     * @return  View
     */
    static function xsl($file = NULL, $module = NULL, array $data = NULL){
        return Core_Xml_Xsl::factory($file, $module, $data);
    }
}