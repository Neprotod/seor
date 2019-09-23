<?php defined('MODPATH') OR exit();

/**
 * Модель определяет типы данных и переписывает их
 */
class Model_Error_System_Admin{
    
    /**
     * @var array все сообщения
     */
    protected $alert = array();
    
    /**
     * @var object все сообщения
     */
    protected $xml = array();
    
    /**
     * @var array начальная ячейка массива
     */
    protected $default = array('error','massage');
    
    /**
     * @var array типы ячеки
     */
    protected $type = array(
                'error' => array('error','warning','danger'),
                'massage' => array('success','info')
            );

    /**
     * @var array ячейки для которых нужна ячейка role
     */
    protected $key = array('tooltip','select','valid');
    
    /**
     * Конструктор, загружает из регистра данные в класс
     *
     * @return void
     */
    function __construct(){
        //Сообщения и ошибки
        $this->alert = &Registry::i()->alert;
        
        //Основной модуль преобразования
        $this->xml = Module::factory('xml',TRUE);
    }

    /**
     * Загружает данные в массив для дальнейшего преобразования в XML
     *
     * [!]  set([string $default [, string $type]], array $setting)
     *      Допустимые значения:
     *            [string $default] - error | massage
     *            [string $type]     - для error | warning | danger | success | info 
     *                                        детельно смотри в массив $this->type
     *            array  $fond     - ключи массива title, massage, role, tooltip, select, valid
     * 
     */
    function set(){
        //Правильное количество
        $num_arg = func_num_args();
        
        if($num_arg < 1 OR $num_arg > 3){
            throw new Core_Exception('Не правильно заполненные аргументы <b>set()</b>. Структура: <b>set</b>([string <i>$default</i>(massage|error)[, string <i>$type</i>]], array <i>$setting</i>)');
        }
        
        $args = func_get_args();
        
        //Ключ первой ячейки
        $default = ($num_arg > 1)? array_shift($args): 'error';
        
        //Проверяем на основную ячеку массива
        if(!in_array($default,$this->default)){
            throw new Core_Exception('Пришло значение <b>:arg</b>. В методе set() первое значение, может только (<b><i>:default</i></b>)',array(':arg'=>$default,':default'=>implode(', ',$this->default)));
        }
        
        $type = NULL;
        $fond = NULL;
        
        //Заполняем ячейки
        foreach($args AS $arg){
            if(is_array($arg)){
                $fond = array_change_key_case($arg);
            }else{
                $type = $arg;
            }
        }
        
        //Проверяем на тип
        if(!in_array($type,$this->type[$default]) AND !is_null($type)){
            throw new Core_Exception('Пришло тип <b>:arg</b>. Допустимые типы для <b>:default</b> это (<b><i>:type</i></b>) и <b>NULL</b>',array(':arg'=>$type,':default'=>$default,':type'=>implode(', ',$this->type[$default])));
        }
        
        //Проверяем на массив
        if(!is_array($fond)){
            throw new Core_Exception('Последний аргумент в <b>set()</b> должно быть <b>array</b>');
        }
        
        //Если есть tooltip, select, valid но нет role это не имеет смысла
        if($key = Arr::intersect_key($fond,$this->key) AND !isset($fond['role'])){
            throw new Core_Exception('Для полей  <b>(:key)</b> обязательным является поле <b>role</b>',array(':key'=> implode(', ',array_keys($key))));
        }
        
        $fond['type'] = $type;
        
        $this->alert[$default][] = $fond;
    }
    /**
     * Выдает HTML в виде готовых ошибок и сообщений
     *
     * @param  string путь к XSL для функции xml::preg_load
     * @return string HTML строка
     */
    function output($xsl = NULL){
        $xml = "admin_module|system::error";
        if($xsl === NULL)
            $xsl = "admin_module|system::error";
        if(!empty($this->alert)){
            ksort($this->alert);
            return $this->xml->preg_load($this->alert,$xml,$xsl);
        }
        
        return FALSE;
    }
}