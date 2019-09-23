<?php defined('MODPATH') OR exit();

/**
 * Модель определяет к какому типу относится URL  
 * 
 * @package    module/system
 * @category   route
 */
class Model_Route_System{
    /**
     * @var object экземпляр URL
     */
    protected $url;
    
    //@var string  имя метода по умолчанию
    protected $default_action = 'fetch';
    /**
     * Заполняем Route::url
     * 
     * @return void
     */
    function __construct(){
        $this->url = URL::instance();
        $this->sql = Model::factory("sql","system");
    }
    
    /**
     * Определяем URL
     * 
     * @return object URL
     */
    function init(){
        //Определяем УРЛ description
        return $this->url(ltrim($this->url->root(FALSE),'/'));
    }
    /**
     * Собирает список действий
     * 
     * @return array список действий
     */
    function action_list($url = NULL){
        if(!$url)
            $url = Registry::i()->founds;
        
        $where = "";
        if(isset($url["id"])){
            $where = "u.id = ". DB::escape($url["id"]);
        }
        elseif(isset($url["url"])){
            $where = "u.url = ". DB::escape($url["url"]);
        }else{
            throw new Core_Exception("Нет не id не самого URL");
        }

        $return = array();
        $return["params"] = array();
        $return["action"] = array();
        
        if($where){
            $return["action"] = Query::i()->sql("action.action_list",array(":where"=>$where),"id_params");
        
            $ids = array_keys($return["action"]);
            $ids = $this->sql->insert_string($ids);

            if($ids){
                $params = Query::i()->sql("action.action_params",array(":set"=>$ids));
                foreach($params AS $value){
                    $return["params"][$value["id_action_list"]][] = $value["param"];
                }
            }
        }
        
        return $return;
    }
    
    /**
     * Функция проверяет есть ли перенаправление ссылки.
     * 
     * @param  string $url url
     * @return mixed
     */
    protected function url($url){
        if(!$url)
            $url = "/";
        
        $result = Query::i()->sql("url.get_url",array(
                                    ":url" => $url
                                 ),NULL, TRUE);
        
        // Проверяем есть ли такой URL, если нет пытаемся перенаправить.
        if($result){
            $result["url"] = trim(Url::root(FALSE),"/");
            return $result;
        }else{
            $this->drop_url($url);
        }
    }
    
    /**
     * Проверяет пришедший URL и удаляет 
     */
    protected function drop_url($url){
        $result = Query::i()->sql("url.drop_url",array(
                                    ":url" => $url
                                 ),NULL, TRUE);
        
        // Если есть перенаправляем, если нет выдаем ошибку.
        if($result){
            $this->relocation($result['url']);
        }else{
            //Ошибка 404
            Model::factory('error','system')->error();
        }
    }
    
    /**
     * Разбивает на модель и метод
     *
     * @param  string $url  url
     * @param  int    $skip сколько ячеек пропустить
     * @return void
     */
    function parse_url($url, $skip = 0){
        //Массив содержащий     нужные значения для заполнения
        $fonds = array();
        $fonds['module'] = NULL;
        $fonds['action'] = $this->default_action;
        $fonds['param'] = array();
        
        $skip = abs($skip);
        
        $url = explode('/',trim($url,'/'));
        
        if($skip != 0 AND !empty($url)){
            for($skip;$skip > 0;$skip--)
                array_shift($url);
        }
        
        foreach($fonds as $key => $fond){
           
            if(!empty($url) AND $shift = array_shift($url)){
                if(!is_array($fond)){
                    $fonds[$key] = $shift;
                }else{
                    $shift = array('shift'=>$shift);
                    $fonds[$key] = Arr::merge($shift , $url);
                    break;
                }
            }else{
                break;
            }
        }
        return $fonds;
    }
    /**
     * Перенаправление.
     *
     * @param  string $url url
     * @return void
     */
    protected function relocation($url){
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".Url::site($url,TRUE));
            exit();
    }
    
}