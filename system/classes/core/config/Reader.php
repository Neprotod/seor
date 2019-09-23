<?php defined('SYSPATH') OR exit();
/**
 * Класс для расширения
 *
 * @package    Tree
 * @category   Configuration
 */
abstract class Core_Config_Reader extends ArrayObject {

    /**
     * @var  string  Имя группы конфигурации
     */
    protected $_configuration_group;

    /**
     * Загружает пустой массив как начальной конфигурации и включает массив 
     * ключей для использования в качестве свойства.
     *
     * @return  void
     */
    public function __construct(){
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Возвращает текущую группу в сериализованной форме.
     *
     *     echo $config;
     *
     * @return  string
     */
    public function __toString(){
        return serialize($this->getArrayCopy());
    }

    /**
     * Загружает группы конфигурации.
     *
     *     $config->load($name, $array);
     *
     * This method must be extended by all readers. After the group has been
     * loaded, call `parent::load($group, $config)` for final preparation.
     *
     * @param   string $group  имя группы конфигурации
     * @param   array  $config конфигурации массива
     * @return  $this   клон данного объекта
     */
    public function load($group, array $config = NULL){
        if ($config === NULL){
            return FALSE;
        }

        // Клон текущего объекта
        $object = clone $this;
        
        // Задайте имя группы
        $object->_configuration_group = $group;

        // Замените массив с фактической конфигурацией
        $object->exchangeArray($config);

        return $object;
    }

    /**
     * Возвращает необработанный массив, который используется для этого объекта.
     *
     *     $array = $config->as_array();
     *
     * @return  array
     */
    public function as_array(){
        return $this->getArrayCopy();
    }

    /**
     * Получить переменную из конфигурации или вернуть значение по умолчанию.
     *
     *     $value = $config->get($key);
     *
     * @param   string   array key
     * @param   mixed    array value
     * @return  mixed
     */
    public function get($key, $default = NULL){
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
    }

    /**
     * Задает значение в конфигурации массива.
     *
     *     $config->set($key, $new_value);
     *
     * @param   string   array key
     * @param   mixed    array value
     * @return  $this
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

}
