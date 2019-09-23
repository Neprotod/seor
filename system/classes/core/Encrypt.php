<?php defined('SYSPATH') OR exit();
/*
 * Шифрование данных.
 *
 * @package   Tree
 * @category  Helpers
 */
class Core_Encrypt{
    /**
     * @var  string  имя экземпляра по умолчанию
     * @static
     */
    public static $default = 'default';

    /**
     * @var  object  Шифровать экземпляры класса
     * @static
     */
    public static $instances = array();

    /**
     * @var  string  OS-зависимых RAND типа для использования
     * @static
     */
    protected static $_rand;

    /**
     * Возвращает образец одиночки класса Шифрования.
     *
     *     $encrypt = Encrypt::instance();
     *
     * @param   string  $name имя группы конфигурации
     * @return  Encrypt
     */
    public static function instance($name = NULL){
        if ($name === NULL){
            // Использовать имя экземпляра по умолчанию
            $name = Encrypt::$default;
        }

        if (!isset(Encrypt::$instances[$name])){
            // Загрузить данные конфигурации
            $config = Core::config('encrypt')->$name;

            if (!isset($config['key'])){
                // No default encryption key is provided!
                throw new Core_Exception('Никакой ключ шифрования не определяется в конфигурации кодирования group: :group',
                    array(':group' => $name));
            }

            if (!isset($config['mode'])){
                // Добавьте режим по умолчанию
                $config['mode'] = MCRYPT_MODE_NOFB;
            }

            if (!isset($config['cipher'])){
                // Добавить шифр по умолчанию
                $config['cipher'] = MCRYPT_RIJNDAEL_128;
            }

            // Создать новый экземпляр
            Encrypt::$instances[$name] = new Encrypt($config['key'], $config['mode'], $config['cipher']);
        }

        return Encrypt::$instances[$name];
    }

    /**
     * Создает новый обертку Mcrypt.
     *
     * @param   string   $key    ключ шифрования
     * @param   string   $mode   mcrypt mode
     * @param   string   $cipher mcrypt шифр
     * @return  void
     */
    public function __construct($key, $mode, $cipher){
        // Найти max длину ключа, на основе шифра и режима
        $size = mcrypt_get_key_size($cipher, $mode);

        if (isset($key[$size])){
            // Сократить ключ для максимального размера
            $key = substr($key, 0, $size);
        }

        // Сохранить ключ, режим и шифр
        $this->_key    = $key;
        $this->_mode   = $mode;
        $this->_cipher = $cipher;

        // Store the IV size
        $this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
    }

    /**
     * Шифрует строку и возвращает зашифрованную строку, которая может быть декодирована.
     *
     *     $data = $encrypt->encode($data);
     *
     * [base64](http://php.net/base64_encode)
     *
     * @param   string $data данные для шифрования
     * @return  string
     */
    public function encode($data){
        // Установите тип рэнд, если он уже не установлен
        if (Encrypt::$_rand === NULL){
            if (Core::$is_windows){
                // ОС Windows поддерживает только системы генератор случайных чисел
                Encrypt::$_rand = MCRYPT_RAND;
            }else{
                if (defined('MCRYPT_DEV_URANDOM')){
                    // Use /dev/urandom
                    Encrypt::$_rand = MCRYPT_DEV_URANDOM;
                }elseif (defined('MCRYPT_DEV_RANDOM')){
                    // Use /dev/random
                    Encrypt::$_rand = MCRYPT_DEV_RANDOM;
                }else{
                    // Use системный генератор случайныъ чисел
                    Encrypt::$_rand = MCRYPT_RAND;
                }
            }
        }

        if (Encrypt::$_rand === MCRYPT_RAND){
            // Генератор случайных чисел должен запускаться каждый раз, или он не будет давать действительно случайные результаты.
            mt_srand();
        }

        // Создать случайный вектор инициализации правильного размера для текущего шифра
        $iv = mcrypt_create_iv($this->_iv_size, Encrypt::$_rand);

        // Шифрование данных с помощью настроенных параметров и сгенерированный IV
        $data = mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv);

        // Используйте кодировку base64 для преобразования в строку
        return base64_encode($iv.$data);
    }

    /**
     * Расшифровывает зашифрованную строку обратно к своему исходному значению.
     *
     *     $data = $encrypt->decode($data);
     *
     * @param   string  $data строка в кодировке для расшифровки
     * @return  bool          FALSE если неудачная расшифровка
     * @return  string
     */
    public function decode($data){
        // Конвертировать данные обратно в двоичные
        $data = base64_decode($data, TRUE);

        if (!$data){
            // Недопустимые данные base64
            return FALSE;
        }

        // Извлечь вектор инициализации из данных
        $iv = substr($data, 0, $this->_iv_size);

        if ($this->_iv_size !== strlen($iv)){
            // IV не ожидаемый размер
            return FALSE;
        }

        // Удаление iv из данных
        $data = substr($data, $this->_iv_size);

        // Возвращает расшифрованные данные, обрезает \0 окончания.
        return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv), "\0");
    }
}