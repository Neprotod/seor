<?php defined('SYSPATH') OR exit();
/**
 * Обработка сессии
 * 
 * @package    Tree
 * @category   Helpers
 */
abstract class Core_Session{
    /**
     * @var  string  адаптер сессии по умолчанию
     * @static
     */
    public static $default = 'native';

    /**
     * @var  array  экземпляр сессии
     * @static
     */
    public static $instances = array();

    /**
     * @var  string  имя cookie
     */
    protected $_name = 'session';

    /**
     * @var  int  время жизни cookie
     */
    protected $_lifetime = 0;

    /**
     * @var  bool  шифрование данных сессии?
     */
    protected $_encrypted = FALSE;

    /**
     * @var  array  данные сессии
     */
    protected $_data = array();

    /**
     * @var  bool  уничтожить сессию?
     */
    protected $_destroyed = FALSE;
    
    /**
     * Создаем сессию определенного типа.
     * Некоторые типы сеансов (native, database) также поддерживается перезагрузка сессии путем передачи идентификатора сессии в качестве второго параметра.
     *
     *     $session = Session::instance();
     *
     * [!!!] [Session::write] будет вызван автоматически при завершении запроса.
     *
     * @param   string   $type Тип сессии (native, cookie, etc)
     * @param   int      $id   Идентификатор сессии
     * @return  Session
     * @uses    Core::config
     */
    public static function instance($type = NULL, $id = NULL){
        
        if ($type === NULL){
            // Использовать тип по умолчанию
            $type = Session::$default;
        }

        if (!isset(Session::$instances[$type])){
            // Загрузить конфигурацию для этого типа
            $config = Core::config('session')->get($type);

            // Задать имя класса сессии
            $class = 'Session_'.ucfirst($type);

            // Создать экземпляр новой сессии
            Session::$instances[$type] = $session = new $class($config, $id);

            // Запись сессии при выключении
            register_shutdown_function(array($session, 'write'));
        }
        
        return Session::$instances[$type];
    }
    /**
     * Создаем сессию определенного типа.
     * Некоторые типы сеансов (native, database) также поддерживается перезагрузка сессии путем передачи идентификатора сессии в качестве второго параметра.
     *
     *     $session = Session::instance();
     *
     * [!!!] [Session::write] будет вызван автоматически при завершении запроса.
     *
     * @param   string   $type Тип сессии (native, cookie, etc)
     * @param   int      $id   Идентификатор сессии
     * @return  Session
     * @uses    Core::config
     */
    public static function i($type = NULL, $id = NULL){
        return static::instance($type, $id);
    }

    /**
     * Перегрузки имя , время жизни, и зашифрованные параметры сессии.
     *
     * [!!] Сеансы могут быть созданы только с помощью [Session::instance] method.
     *
     * @param   array   $config конфигурации
     * @param   int     $id     session id
     * @return  void
     * @uses    Session::read
     */
    public function __construct(array $config = NULL, $id = NULL){
        if (isset($config['name'])){
            // Имя файла cookie для хранения идентификатора сессии
            $this->_name = (string)$config['name'];
        }

        if (isset($config['lifetime'])){
            // время жизни cookie
            $this->_lifetime = (int)$config['lifetime'];
        }

        if (isset($config['encrypted'])){
            if ($config['encrypted'] === TRUE){
                // использовать шифрование по умолчанию.
                $config['encrypted'] = 'default';
            }

            // Включение или отключение шифрования данных
            $this->_encrypted = $config['encrypted'];
        }

        // Загрузить сессию
        $this->read($id);
    }

    /**
     * Возвращает сериализованный объект. Если шифрование включено сессия будет зашифрована Если нет, то строки вывода будет кодироваться с помощью [base64_encode].
     *
     *     echo $session;
     *
     * @return  string
     * @uses    Encrypt::encode
     */
    public function __toString(){
        // Сериализация массива данных
        $data = serialize($this->_data);

        if ($this->_encrypted){
            // Шифровать данные с помощью ключа по умолчанию
            $data = Encrypt::instance($this->_encrypted)->encode($data);
        }else{
            // Запутывание данных с кодировкой base64
            $data = base64_encode($data);
        }

        return $data;
    }

    /**
     * Возвращает массив текущей сессии. Возвращенный массив также может быть назначен по ссылке.
     *
     *     // Получить копию данных текущей сессии
     *     $data = $session->as_array();
     *
     *     // Присвоение по ссылке для модификации
     *     $data =& $session->as_array();
     *
     * @return  array
     */
    public function & as_array(){
        return $this->_data;
    }

    /**
     * Получите идентификатор текущего сеанса, если его поддерживает сессии.
     *
     *     $id = $session->id();
     *
     * [!!] Не все типы сессии имеют идентификаторы.
     *
     * @return  string
     * @since   3.0.8
     */
    public function id(){
        return NULL;
    }

    /**
     * Получите имя файла cookie сеанса.
     *
     *     $name = $session->name();
     *
     * @return  string
     * @since   3.0.8
     */
    public function name(){
        return $this->_name;
    }

    /**
     * Получите переменную массива, сессии.
     *
     *     $foo = $session->get('foo');
     *
     * @param   string  $key     Имя переменной
     * @param   mixed   $default Значение по умолчанию для возвращения.
     * @return  mixed
     */
    public function get($key, $default = NULL){
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
    }

    /**
     * Получить и удалить переменную из массива сессии.
     *
     *     $bar = $session->get_once('bar');
     *
     * @param   string  $key     имя переменной
     * @param   mixed   $default значение по умолчанию для возвращения.
     * @return  mixed
     */
    public function get_once($key, $default = NULL){
        $value = $this->get($key, $default);

        unset($this->_data[$key]);

        return $value;
    }

    /**
     * Установка переменной в массиве сессии.
     *
     *     $session->set('foo', 'bar');
     *
     * @param   string   $key   имя переменной
     * @param   mixed    $value значение
     * @return  $this
     */
    public function set($key, $value){
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Установить переменную по ссылке
     *
     *     $session->bind('foo', $foo);
     *
     * @param   string  $key   имя переменной
     * @param   mixed   $value значение по ссылке
     * @return  $this
     */
    public function bind($key, & $value)
    {
        $this->_data[$key] =& $value;

        return $this;
    }

    /**
     * Удаляет переменную в массиве сессии.
     *
     *     $session->delete('foo');
     *
     * [!] можно передавать неограниченное количество переменных
     *
     * @param   string  $key имя переменной ... другие переменные
     * @param   ...
     * @return  $this
     */
    public function delete($key){
        $args = func_get_args();

        foreach ($args as $key){
            unset($this->_data[$key]);
        }

        return $this;
    }

    /**
     * Загружает существующие данные сессии.
     *
     *     $session->read();
     *
     * @param   string   session id
     * @return  void
     */
    public function read($id = NULL){
        if (is_string($data = $this->_read($id))){
            try{
                if ($this->_encrypted){
                    // Расшифровать данные с помощью ключа по умолчанию
                    $data = Encrypt::instance($this->_encrypted)->decode($data);
                }else{
                    // Декодирования данных в формате base64
                    $data = base64_decode($data);
                }

                // Unserialize данных
                $data = unserialize($data);
            }
            catch (Exception $e){
                // Игнорировать все ошибки чтения
            }
        }

        if (is_array($data)){
            // Загрузить данные локально
            $this->_data = $data;
        }
    }

    /**
     * Создает новый идентификатор сеанса и возвращает его.
     *
     *     $id = $session->regenerate();
     *
     * @return  string
     */
    public function regenerate(){
        return $this->_regenerate();
    }

    /**
     * Устанавливает last_active timestamp и сохраняет сессию.
     *
     *     $session->write();
     *
     * [!!] Все ошибки во время сессии будут записаны.
     *         Но не отображаются так как запись происходит после вывода.
     *
     * @return  boolean
     * @uses    Core::$log
     */
    public function write(){
        if (headers_sent() OR $this->_destroyed){
            // Сессия не может быть записан, когда отправляются заголовки или когда
            // сессия была уничтожена
            return FALSE;
        }
        // Установите последний активный timestamp
        $this->_data['last_active'] = time();

        try{
            return $this->_write();
        }catch (Exception $e){
            // Log & ignore all errors when a write fails
            //Core::$log->add(Log::ERROR, Core_Exception::text($e))->write();

            return FALSE;
        }
    }

    /**
     * Полностью уничтожить текущую сессию.
     *
     *     $success = $session->destroy();
     *
     * @return  boolean
     */
    public function destroy(){
        if ($this->_destroyed === FALSE){
            if ($this->_destroyed = $this->_destroy()){
                // Сессия была разрушена, очистить все данные
                $this->_data = array();
            }
        }

        return $this->_destroyed;
    }

    /**
     * Загрузить данные сессии в строку и вернуть ее.
     *
     * @param   string   session id
     * @return  string
     */
    abstract protected function _read($id = NULL);

    /**
     * Создать новый идентификатор сеанса и возвращает его.
     *
     * @return  string
     */
    abstract protected function _regenerate();

    /**
     * Пишет текущую сессию.
     *
     * @return  boolean
     */
    abstract protected function _write();

    /**
     * Уничтожает текущий сеанс.
     *
     * @return  boolean
     */
    abstract protected function _destroy();

}