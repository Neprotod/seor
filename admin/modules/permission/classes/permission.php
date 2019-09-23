<?php

/*
 * Модуль работает со стилями и типами создает и выдает XML файлы.
 */
 
class Permission_Admin{
    static $permission;
    
    function init(){
        /////////////////////
        /////////TEST////////
        /////////////////////
        $xsd = Admin_Module::mod_path("permission") . "xsd/permission.xsd";
        $path = Admin_Module::mod_path("system") . "permission.xml";
        
        $xml = new DOMDocument();
        $xml->presserveWhiteSpase = false;
        $xml->load($path);
        
        
        $xml->schemaValidate($xsd);
    }
}