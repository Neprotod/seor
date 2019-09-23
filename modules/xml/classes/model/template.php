<?php defined('SYSPATH') OR exit();
/**
 * Подключения XML/XSL с темы
 * 
 * @package    module/xml
 * @category   template
 */
class Model_Template_XML{
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
     * Ищет и возвращает путь к файлу XML в теме
     *
     * @param  string  имя темы
     * @param  string  имя файла допускается путь через '_'
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     * @use self::path()
     */
    function xml_path($template,$file,$admin){
        return $this->path($template,$file,$this->default_xml_dir,$this->xml_ext,$admin);
    }
    
    /**
     * Ищет и возвращает путь к файлу XSL в теме
     *
     * @param  string  имя темы
     * @param  string  имя файла допускается путь через '_'
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     * @use self::path()
     */
    function xsl_path($template,$file,$admin){
        return $this->path($template,$file,$this->default_xsl_dir,$this->xsl_ext,$admin);
    }
    
    
    /**
     * Создает путь к файлу используется в методах xml_path и xsl_path
     *
     * @param  string  имя темы
     * @param  string  имя файла допускается путь через '_'
     * @param  string  техническая директория, как xml или xsl
     * @param  string  расширение, xml или xsl
     * @param  boolean TRUE подключит административную тему
     * @return string  путь к файлу
     */
    protected function path($template,$file,$dir,$ext,$admin){
        $path = '';
        $file = trim(str_replace('_',DIRECTORY_SEPARATOR,$file),DIRECTORY_SEPARATOR);

        if(empty($template))
            $template = (is_array(Registry::i()->template))?Registry::i()->template['name']:Registry::i()->template;
        
        //Для административной темы
        if($admin === TRUE){
            if(strtolower(Core::$sample) == 'admin'){
                $path = Admin::find_file("template_{$template}_{$dir}",$file,$ext);
            }else{
                throw new Core_Exception('У вас нет прав для использования административной темы');
            }
        }else{
            $path = Core::find_file("template_{$template}_{$dir}", $file,$ext);
        }
        //Если файла нет
        if(!is_file($path)){
            $path = $dir.DIRECTORY_SEPARATOR.$file.".{$ext}";

            throw new Core_Exception('Нет файла в теме <b>:template</b>, на пути <b>:path</b>',array(':template'=>$template,':path'=>$path));
        }
        return $path;
    }
}