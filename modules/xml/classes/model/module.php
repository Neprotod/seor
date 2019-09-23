<?php
/**
 * Подключения XML/XSL с темы
 * 
 * @package    module/xml
 * @category   module
 */
class Model_Module_XML{
    /**
     * @var string техническое имя директории xml
     */
    public $default_xml_dir = 'xml';
    /**
     * @var string техническое имя директории xsl
     */
    public $default_xsl_dir = 'xsl';
    /**
     * @var string расширение файла xml
     */
    public $xml_ext = 'xml';
    /**
     * @var string расширение файла xsl
     */
    public $xsl_ext = 'xsl';
    
    /**
     * Ищет и возвращает путь к файлу XML в модуле
     *
     * @param  string  имя модуля
     * @param  string  имя файла допускается путь через '_'
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     * @use self::path()
     */
    function xml_path($modul,$file,$admin){
        return $this->path($modul,$file,$this->default_xml_dir,$this->xml_ext,$admin);
    }
    
    /**
     * Ищет и возвращает путь к файлу XSL в модуле
     *
     * @param  string  имя модуля
     * @param  string  имя файла допускается путь через '_'
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     * @use self::path()
     */
    function xsl_path($modul,$file,$admin){
        return $this->path($modul,$file,$this->default_xsl_dir,$this->xsl_ext,$admin);
    }
    
    /*
     * Создает путь к файлу используется в методах xml_path и xsl_path
     *
     * @param  string  имя модуля
     * @param  string  имя файла допускается путь через '_'
     * @param  string  техническая директория, как xml или xsl
     * @param  string  расширение, xml или xsl
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     */
    protected function path($modul,$file,$dir,$ext,$admin){
        $path = '';
        if($admin === TRUE){
            if(strtolower(Core::$sample) == 'admin'){
                $path = Admin_Module::mod_path($modul);
            }else{
                throw new Core_Exception('У вас нет прав для использования административного модуля');
            }
        }else{
            $path = Module::mod_path($modul);
        }
        //Преобразуем файл в путь
        $file = trim(str_replace('_',DIRECTORY_SEPARATOR,$file),DIRECTORY_SEPARATOR);
        //Дополняем путь
        $path = $path.$dir.DIRECTORY_SEPARATOR;
        
        $path .= $file.".{$ext}";
        
        if(!is_file($path))
            throw new Core_Exception("Нет файла :ext  в модуле <b>:modul</b>, на пути <b>:path</b>",array(':ext'=>$ext,':modul'=>$modul,':path'=>$path));
        
        return $path;
    }
}