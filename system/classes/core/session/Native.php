<?php defined('SYSPATH') OR exit();
/**
 * Родной PHP класс сессии.
 *
 * @package    Tree
 * @category   Core
 */
class Core_Session_Native extends Session {

    /**
     * @return  string
     */
    public function id(){
        return session_id();
    }

    /**
     * @param   string  $id  session id
     * @return  null
     */
    protected function _read($id = NULL){
        // Синхронизировать сессии cookie с параметрами файла Cookie
        session_set_cookie_params($this->_lifetime, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
        // Не позволяют PHP отправить заголовки Cache-Control
        session_cache_limiter(FALSE);

        // Задать имя файла cookie сессии
        session_name($this->_name);

        if ($id){
            // Установить идентификатор сессии
            session_id($id);
        }

        // Запустить сессию
        session_start();

        // Использование глобальной $_SESSION для хранения данных
        $this->_data =& $_SESSION;

        return NULL;
    }

    /**
     * @return  string
     */
    protected function _regenerate(){
        // Восстановить идентификатор сессии
        session_regenerate_id();

        return session_id();
    }

    /**
     * @return  bool
     */
    protected function _write(){
        // Записать и закрыть сессию
        session_write_close();

        return TRUE;
    }

    /**
     * @return  bool
     */
    protected function _destroy(){
        // Уничтожьте текущую сессию
        session_destroy();

        // Зачистить данные после уничтожения.
        $status = ! session_id();

        if ($status){
            // Убедитесь, что сессия не может быть перезапущена
            Cookie::delete($this->_name);
        }

        return $status;
    }

}
