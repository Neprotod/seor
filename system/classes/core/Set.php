<?php defined('SYSPATH') OR exit();
/**
 * Класс подключает модели и контроллеры как свойства. 
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Set{
    protected $set_array = array();

    function __construct(){

    }
    /**
     * Создать объект 
     * 
     * @return Set
     */
    static function i(){
        return new self;
    }

    function __get($name){
        // Определяем какой тип данных подключать;
        if(count($this->set_array) < 2){
            $this->set_array[] = $name;
            return $this;
        }else{
            throw new Core_Exception('Не правильная цепочка');
        }
    }
    function __call($name, $arguments){
        // Пересохраняем и удаляем лишние значения
        $set_array = $this->set_array;
        unset($this->set_array);
        
        if(empty($set_array))
            return Module::factory($name,TRUE,$arguments);
        
        $module = array_shift($set_array);
        $action = array_shift($set_array);
        
        switch($action){
            case 'controller':
                return Controller::factory($name,$module,$arguments);
                break;
            case 'model':
                return Model::factory($name,$module,$arguments);
                break;
            default:
                throw new Core_Exception('Не правильное действие');
        }
        
    }
}