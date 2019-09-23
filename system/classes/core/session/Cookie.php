<?php defined('SYSPATH') OR exit();
/**
 * Cookie - класс, основанный на сессии .
 *
 * @package    Tree
 * @category   Core
 */
class Core_Session_Cookie extends Session {

    /**
     * @param   string  $id  session id
     * @return  string
     */
    protected function _read($id = NULL){
        return Cookie::get($this->_name, NULL);
    }

    /**
     * @return  null
     */
    protected function _regenerate(){
        // cookie сессия не имеет id
        return NULL;
    }

    /**
     * @return  bool
     */
    protected function _write(){
        return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
    }

    /**
     * @return  bool
     */
    protected function _destroy(){
        return Cookie::delete($this->_name);
    }

}
