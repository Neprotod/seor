<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Page_Pages_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','pages');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Module::factory('error',TRUE);
        $this->url = Admin_Model::factory('url','system');
        
        $this->xml = Module::factory('xml',TRUE);
        
        //Подключаем редактор
        Registry::i()->editor = TRUE;
        
        /*
        $this->xml->style('default');
        */
    }
    
    function get($id){
        
        /*********************************************/

    /*    $this->error->set('error','error',array('title'=>"ОШИБКА!",'massage'=>"сообщение ошибки сообщение ошибки сообщение ошибки сообщение ошибки сообщение ошибки сообщение ошибки",'role'=>'name','tooltip'=>'Это подсказка','select'=>'1'));*/
        /*$this->error->set('error','error',array('title'=>"Ошибка!",'massage'=>"Обратите внитание на <b>Заголовок</b>",'role'=>'title','tooltip'=>'Это подсказка'));
        $this->error->set('error','error',array('title'=>"ОШИБКА!",'massage'=>"сообщение ошибки",'role'=>'url','tooltip'=>'Еще подсказка','select'=>'1'));*/
        /*$this->error->set('error','error',array('title'=>"ОШИБКА!",'massage'=>"сообщение ошибки",'role'=>'name','tooltip'=>'Это подсказка','select'=>'1'));
        $this->error->set('error','warning',array('title'=>"Не ошибка!",'massage'=>"сообщение ошибки",'role'=>'name','tooltip'=>'Это подсказка','select'=>'1'));
        
        $this->error->set('massage','success',array('title'=>"Сообщение",'massage'=>"текст сообщения"));
        $this->error->set('massage','info',array('title'=>"Сообщение",'massage'=>"текст сообщения"));
        $this->error->set('massage','info',array('title'=>"Сообщение",'massage'=>"текст сообщения"));*/
        /*
        $this->error->set('massage','success',array('role'=>'url','tooltip'=>'Заполнено правильно'));
        
        $this->error->set('error',array('massage'=>'Нормально заполнено'));
        $this->error->set('error','error',array('title'=>"Ошибка!",'massage'=>"Обратите внитание на <b>Заголовок</b>",'role'=>'title','tooltip'=>'Это подсказка'));
        $this->error->set('error','warning',array('title'=>"Ошибка!",'massage'=>"Обратите внитание на <b>Заголовок</b>",'role'=>'title','tooltip'=>'Это подсказка'));
        $this->error->set('massage','success',array('title'=>"Сообщение",'massage'=>"текст сообщения"));
        $this->error->set('massage','info',array('title'=>"Сообщение",'massage'=>"текст сообщения"));
        */
        
        /*$this->error->set('error','error',array('role'=>'url','title'=>"Не забудь",'massage'=>"заполнить обязательные поля",'tooltip'=>'Это подсказка'));*/
        /*$this->error->set('error','warning',array('role'=>'url','title'=>"Не забудь",'massage'=>"заполнить обязательные",'tooltip'=>'Это подсказка'));
        $this->error->set(array('role'=>'title','title'=>"Не забудь",'massage'=>"А это для ошибок",'tooltip'=>'тест'));
        $this->error->set(array('role'=>'url','title'=>"Не забудь",'massage'=>"заполнить обязательные ",'tooltip'=>'тест'));
        
        /*
        $this->error->set('error','error',array('role'=>'url','title'=>"Не забудь",'massage'=>"заполнить обязательные поля"));
        */
        //$xml = 
        
        //exit();
        /*********************************************/

        /*
        try{
            throw new Core_Exception('База данных не установлена2',NULL,NULL,array('title'=>'Ошибка'));
        }catch(Exception $e){
            //$this->error->set('error','error',array('title'=>'Ошибка.','massage'=>$e->getMessage()));
            $e->error_reporting();
        }
*/
        $template = Registry::i()->root_template;
        
        //Индексация
        $robots = array('index' => 1,'follow' =>1,'noindex' =>0,'nofollow' =>0);
        
        $page = $this->method->get_page($id,$template['id']);
        
        //Обработка пост запроса
        $this->post($page,$template);
        
        if(!empty($page['robots'])){
            $check_robots = array_flip(explode(',',$page['robots']));
            //Очищаем значения массива
            $robots = Arr::fill($robots,NULL);
            
            //Заполняем массив 1
            $check_robots = Arr::fill($check_robots,1);
            
            //Заполняем нужные ячейки для указания индексации
            $robots = Arr::merge($robots,$check_robots);
        }

        Registry::i()->title = "Страница: ".$page['title'];
        
        $date = array();
        //Основные параметры
        $date['param'] = $page;
        $date['robots'] = $robots;
        $date['template'] = $template;
        $date['style'] = $this->style($template,$page);
        $date['type'] = $this->type($template,$page);
        $date['error'] = $this->error->output();
        
        //Отображение описательных элементов
        $date['description'] = Admin_Template::factory(Registry::i()->template,"content_description_description",$date);
        
        //Подключаем редактор
        $date['editor'] = Admin_Template::factory(Registry::i()->template,"content_editor");
        
        $fonds = array();
        $fonds['date'] = $date;
        
        return $fonds;
        
    }
    
    
    /*
     * Создает страницу
     *
     */
    function create(){
        $template = Registry::i()->root_template;
        //Берем парсер
        $parse = Admin_Model::factory('parse','system');
        
        $param = $parse->post();
        
        //Индексация
        $robots = array('index' => 1,'follow' =>1,'noindex' =>0,'nofollow' =>0);
        
        Registry::i()->title = "Создание страницы";
        
        $date = array();
        
        //Основные параметры
        $date['robots']   = $robots;
        $date['template'] = $template;
        $date['param']       = $param;
        $date['style']      = $this->style($template);
        $date['type']       = $this->type($template);
        
        //Отображение описательных элементов
        $date['description'] = Admin_Template::factory(Registry::i()->template,"content_description_description",$date);
        
        //Подключаем редактор
        $date['editor'] = Admin_Template::factory(Registry::i()->template,"content_editor");
        
        $fonds = array();
        $fonds['date'] = $date;
        
        return $fonds;
    }
    
    /*
     * Создает отображения стилей
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение стилей
     */
    protected function style($template,$param = NULL){
        //Берем парсер
        $parse = Admin_Model::factory('parse','system');
        
        //Загружаем нужную модель, что бы не переподключать
        $parse->style = $this->style;
        
        if(isset($param['id'])){
            $search = array(
                'id_table' => $param['id'],
                'type' => 'page',
            );
        }else{
            $search = array(
                'type' => 'page',
            );
        }

        $date = $parse->xml_style($search,$template,'style_style');
        
        //return Admin_Template::factory(Registry::i()->template,"content_style_style",$date);
        return $date['xml'];
    }
    
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение типов
     */
    protected function type($template,$param = NULL){
        //Берем парсер
        $parse = Admin_Model::factory('parse','system');
        
        //Загружаем нужную модель, что бы не переподключать
        $parse->type = $this->type;
        if(isset($param['content_type'])){
            $search = array(
                'content_type' => $param['content_type'],
                'type' => 'page',
            );
        }else{
            $search = array(
                'type' => 'page',
            );
        }
        $date = $parse->xml_type($search,$template,'type_type');
        
        return $date['xml'];
    }
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение типов
     */
    protected function post(&$page,$template){
        if(Request::method('post')){
            
            $post = Admin_Model::factory('post','system');
            
            //Загружаем параметр
            $post->load_param($page);
            
            ////////////////////////////////
            /////// Обработка стилей
            ////////////////////////////////
            try{
                if($post->style($template,'page'))
                    $this->error->set('massage','success',array('massage'=>'Стили изменены.'));
            }catch(Exception $e){
                $this->error->set('error','error',array('title'=>'Ошибка.','massage'=>$e->getMessage()));
            }
            ////////////////////////////////
            /////// Обработка типов
            ////////////////////////////////
            try{
                if($post->type())
                    $this->error->set('massage','success',array('massage'=>'Отображение изменено.'));
            }catch(Exception $e){
                $this->error->set('error','error',array('title'=>'Ошибка.','massage'=>$e->getMessage()));
            }
            ////////////////////////////////
            /////// Запись описательных данных и URL
            ////////////////////////////////
            if($update = &$post->data($page)){
                try{
                    $post->url('page');
                    $this->method->update_page($page['id'],$update);
                    $this->error->set('massage','success',array('title'=>'Все прошло успешно.','massage'=>'Все данные сохранены.'));
                }catch(Exception $e){
                    $this->error->set('error','error',array('title'=>'Ошибка.','massage'=>$e->getMessage()));
                }
            }
            
        }
    }
    
}