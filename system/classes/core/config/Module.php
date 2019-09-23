<?php defined('SYSPATH') OR exit();
/**
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [Config].
 *
 * @package    Tree
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Core_Config_Module extends Config_Reader {

    /**
     * @var  string  Имя группы конфигурации
     */
    protected $_configuration_group;
    
    /**
     * @var  string  Путь к конфигурациям
     */
    public $_directory;
    
    /**
     * @var  bool  Изменилась ли группа конфигурации?
     */
    protected $_configuration_modified = FALSE;

    /**
     * Загрузите и поглотите все конфигурационные файлы в этой группе.
     *
     *     $config->load($name);
     *
     * @param   string  $group имя группы конфигурации
     * @param   array   $config конфигурации массива
     * @return  $this   клон текущего объекта
     * @uses    Core::find_file
     */
    public function load($group, array $module = NULL){
        $type = $module["type"];
        $module = $module["mod"];
        $mod_path = $type::mod_path($module) . "config" . DIRECTORY_SEPARATOR;
        $file = $mod_path.$group.EXT;
        if(is_file($file)){
            $arr = Core::load($file);
            if(is_array($arr)){
                return parent::load($group, $arr);
            }else{
                throw new Core_Exception("Пришел не массив, это не конфигурации");
            }
        }else{
            throw new Core_Exception("Нет файла конфигураций");
        }

        
    }

}
