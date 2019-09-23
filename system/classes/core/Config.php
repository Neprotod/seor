<?php defined('SYSPATH') OR exit();
/**
 * Подключение конфигурационных файлов и создание запросов к БД
 *
 * @package   Tree
 * @category  Core
 */
class Core_Config{

    /**
     * @var  Core_Config  Статический сингелтон
     */
    protected static $_instance;

    /**
     * @var  array  Настройки чтения
     */
    protected $_readers = array();
    
    /**
     * Получить статический экземпляр Config.
     *
     *     $config = Config::instance();
     *
     * @return  Config
     */
    public static function instance(){
        if (Config::$_instance === NULL){
            // Создайте новый образец
            Config::$_instance = new Config;
        }

        return Config::$_instance;
    }

    /**
     * Чтение файлов. Если чтение нужно использовать только тогда когда все остальные чтения окончились неудачей, использовать FALSE
     *
     *     $config->attach($reader);        // Try first
     *     $config->attach($reader, FALSE); // Try last
     *
     * @param   object  $reader Config_Reader экземпляр
     * @param   boolean $first  добавьте читателя как первого использованного объекта
     * @return  $this
     */
    public function attach(Config_Reader $reader, $first = TRUE){
        if ($first === TRUE){
            // Место чтения журнала в верхней части стека
            array_unshift($this->_readers, $reader);
        }
        else{
            // Место чтения в нижней части стека
            $this->_readers[] = $reader;
        }

        return $this;
    }

    /**
     * Отделить конфигурации чтения.
     *
     *     $config->detach($reader);
     *
     * @param   object $reader Config_Reader образец
     * @return  $this
     */
    public function detach(Config_Reader $reader){
        if (($key = array_search($reader, $this->_readers)) !== FALSE){
            // Удалить записыватель
            unset($this->_readers[$key]);
        }

        return $this;
    }

    /**
     * Загрузка группы конфигураций. 
     *
     *     $array = $config->load($name);
     *
     * @param   string $group имя группы конфигурации
     * @return  Config_Reader
     * @throws  Core_Exception
     */
    public function load($group){
        foreach ($this->_readers as $reader){
            if ($config = $reader->load($group)){
                // Нашел читателя для этой конфигурации группы
                return $config;
            }
        }

        // Сброс итератора
        reset($this->_readers);

        if ( ! is_object($config = current($this->_readers))){
            throw new Core_Exception('Нет конфигураций');
        }

        // Загрузить читатель как пустой массив
        return $config->load($group, array());
    }

    /**
     * Копирование одной конфигурационной группы для всех других читателей.
     * 
     *     $config->copy($name);
     *
     * @param   string   имя группы конфигурации
     * @return  $this
     */
    public function copy($group){
        // Загрузить конфигурационной группы
        $config = $this->load($group);

        foreach ($this->_readers as $reader){
            if ($config instanceof $reader){
                // Не копировать config в той же группе
                continue;
            }

            // Загрузить объект конфигурации
            $object = $reader->load($group, array());

            foreach ($config as $key => $value){
                // Копировать каждое значение в конфигурации
                $object->offsetSet($key, $value);
            }
        }

        return $this;
    }
}