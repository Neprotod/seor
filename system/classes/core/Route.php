<?php defined('SYSPATH') OR exit();
/**
 * Маршрутизатор
 * 
 * @package    Tree
 * @category   Core
 */
class Core_Route{
    
    /*********method*********/
   /**
    * Если хост не прошел проверку, выдает ошибку.
    *
    * @return void
    */
    function __construct(){
        if(Core::$check_host !== TRUE){
            throw new Core_Exception_Include(NULL,'domen');
        }
    }
    /**
     * Перенаправляет запросы
     *
     * @return void
     */
    function init(){
        // Проверяем ссылку
        if(($char = UTF8::substr($_SERVER['REQUEST_URI'],-1,1) AND $_SERVER['REQUEST_URI'] != '/') AND ($char == '?' OR  $char == '/')){
        
            header("HTTP/1.1 302 Found");
            
            if($char == '?'){
                header("Location: ".rtrim(URL::root(NULL),'?/'));
            }else{
                header("Location: ".URL::root(NULL));
            }
            
            exit();
            
        }elseif($_SERVER['REQUEST_URI'] == '/index.php'){
            // Задумается о базовом УРЛ
            header("Location: /",TRUE,301);
            exit();
        }

        if(URL::root(NULL) == '/')
            Registry::i()->home = TRUE;
        else
            Registry::i()->home = FALSE;
    }
    
    /**
     * Страница не найдена. Ошибка 404
     *
     * @param  string $url url
     * @return void
     */
    static function not_found($url = NULL){
        return Model::factory('error','system')->error();
    }
    
}