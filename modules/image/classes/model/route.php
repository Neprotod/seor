<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Route_System{
    
    protected $url;
    
    function __construct(){
        $this->url = URL::instance();
    }
    
    function init(){
        //Определяем УРЛ
        return $this->url($this->url->url_original);
    }
    
    /*
     * Функция проверяет пришедьший URL и удалет 
     */
    protected function url($url){
        $sql = "SELECT u.url, t.type, u.id_table,(SELECT c.url FROM __url c WHERE c.id = u.id_canonical ) AS canonical
                    FROM __url u
                    INNER JOIN __type t ON u.id_type = t.id
                    WHERE u.url = :url LIMIT 1;";
                  
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $query->param(':url',$url);
        
        // Проверяем есть ли такой URL, если нет пытаемся перенаправить.
        if($result = $query->execute()){
            return reset($result);
        }else{
            $this->drop_url($url);
        }
    }
    
    /*
     * Функция проверяет есть ли перенаправление ссылки.
     */
    protected function drop_url($url){
        $sql = "SELECT d.url_reset, u.url, d.time_drop
                    FROM __url_drop d
                    INNER JOIN __url u ON d.id_url = u.id
                    WHERE d.url_reset = :url LIMIT 1;";
                    
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $query->param(':url',$url);
        
        // Если есть перенаправляем, если нет выдаем ошибку.
        if($result = $query->execute()){
            $result = reset($result);
            $this->relocation($result['url']);
        }else{
            //Ошибка 404
            Model::factory('error','system')->error();
        }
    }
    
    /*
     * Перенаправление.
     */
    protected function relocation($url){
            header("HTTP/1.1 302 Found");
            
            header("Location: ".$url);
            
            exit();
    }
    
}