<?php defined('MODPATH') OR exit();

//Модель обрабатывает POST запросы и выдает уже готовый массив для обнавления.
class Model_Post_System_Admin{
    
    //@var хранит образец модели type
    public $type = NULL;
    
    //@var хранит образец модели style
    public $style = FALSE;
    //@var bool хранит значение для метода check
    protected $check = FALSE;
    
    //@var array хранит значение для метода check
    protected $data = array();
    
    //@var array хранимые изменения
    protected $upadate = array();
    
    //@var array значения с базы данных для сверки
    protected $param = array();
    
    /**
     * @var object модуль обработки ошибок
     */
    protected $error;
    
    
    function __construct(){
        $this->error = Module::factory('error',TRUE);
    }
    /*
     * Проверка были ли изменены данные
     *
     * @param  bool если TRUE принудительно меняет что изменение было.
     * @return bool TRUE если изменение было, FALSE если небыло
     */
    function check($param = NULL){
        if($this->check !== TRUE){
            $this->check = (bool)$param;
        }
        
        return $this->check;
    }
    
    
    /*
     * Метод загружает параметры с базы данных
     *
     * @return void
     */
    public function load_param(array &$param){
        $this->param = &$param;
    }
    
    /*
     * Обнавляет стили
     *
     * [USE] MODUL::system MODEL::style
     * [USE] MODUL::system MODEL::type
     *
     * @return void
     */
    public function style(array $template,$param_type,$new_style = NULL){
        if(empty($this->param))
            throw new Core_Exception("Не пришли данные, выполните метод <b>load_param</b> в модели post системного модуля");
        
        if(!isset($template['id'])){
            throw new Core_Exception("Пришел не верный массив темы");
        }
        
        $changes = FALSE;
        
        //Модель для обработки типа контента
        if(empty($this->type))
            $this->type = Admin_Model::factory('type','system');
        if(empty($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        $search = array(
            'id_table' => $this->param['id'],
            'type' => $param_type,
        );
        
        //Берем стили контента
        $style_contents = $this->style->style_contents($search,NULL,'id_style');
        
        //Отфильтровываем ID
        $ids = array();
        foreach($style_contents AS $id => $value){
            $ids[$id] = $value['status'];
        }
        

        $search = array(
            'id_template' => $template['id'],
            'default' => 1
        );
        
        //Берем стили по умолчанию
        $default = $this->style->get_styles($search,array(NULL,$param_type));
        //Берем значения
        $default_key = Arr::fill(array_flip(array_keys($default)),0);
        
        //Берем стили
        $style = (empty($new_style))?Request::param($_POST['style'],NULL,array()):$new_style;
    

        //Берем стили по умолчанию которые нужно отключить
        $default = array_diff_key($default_key,$style);

        //Берем значения которые есть в таблице
        $update = array_intersect_key($style, $ids);

        //Берем значения которых нет в таблице
        $insert = array_diff_key($style,$update);


        //Очищаем от повторений
        $update = array_diff_assoc($update,$ids);

        
        //Очищаем от повторений
        $default = array_diff_key($default_key,$style);
        
        //Берем стили по умолчанию которые нужно удалить
        $default_drop = array_intersect_key($update,$default_key);
        

        //Возвращаем ключи для обновления, без удаляемых полей
        $update = array_diff_key($update,$default_drop);

        //Берем значения которые нужно отключить или удалить
        $drop = array_diff_key($ids,$style,$default);

        $drop = Arr::fill($drop,0);

                    
        //Удалить файлы по умолчанию
        $delete = array();
        foreach($drop as $key => $value){
            if(isset($style_contents[$key]) AND $style_contents[$key]['default'] == 1){
                $delete[$key] = $value;
                unset($drop[$key]);
            }
        }
        //Очищаем от повторений
        $drop = array_diff_assoc($drop,$ids);

        $delete = Arr::merge($delete,$default_drop);
        
        //Обновляем
        $update = Arr::merge($update,$drop);

        
        $insert = Arr::merge($insert,$default);

        //Очищаем, если запись все таки есть в базе
        $insert =  array_diff_assoc($insert,$ids);
            
        
        $s = $this->style->get_styles(array("id"=> array_keys($insert)));
        
        //Берем тип
        $type = $this->type->type_list($param_type);
        
        
        //Очищаем INSERT от уже существующих
        foreach($s AS $key => $value){
            if($s[$key]['default'] == 1 AND $insert[$key] == 1){
                unset($insert[$key]);
            }
            
            //Редкий случай совпадения, при включении стандартного типа
            if(isset($insert[$key]) AND isset($ids[$key])){
                $update[$key] = $insert[$key];
                unset($insert[$key]);
            }
        }
        
        //Обновляем стили
        if(!empty($update)){
            $fonds = array();
            foreach($update AS $key => $value){
                $fonds[$value][$key] = $value;
            }
            
            $table = array(
                'id_table'=> $this->param['id'],
                'id_type'=> $type['id'],
            );
            foreach($fonds AS $status => $fond){
                //Запрос должен идти по id стиля, таблицы и типа
                $changes = (bool)$this->style->style_content_update(array('status'=>$status),$table,array_keys($fond));
            }
            
        }
        
        //Добавляем записи
        if(!empty($insert)){
            $table = array(
                'id_table',
                'id_style',
                'id_type',
                'status',
            );
            
            $table = array_flip($table);
            $table = Arr::fill($table,NULL);
            
            $fonds = array();
            foreach($insert AS $key => $value){
                $fonds[$key] = $table;
                $fonds[$key]['id_table'] = $this->param['id'];
                $fonds[$key]['id_style'] = $key;
                $fonds[$key]['id_type'] = $type['id'];
                $fonds[$key]['status'] = $value;
            }

            $changes = (bool)$this->style->style_content_set($fonds,$table,TRUE);
        }

        //Удаляем
        if(!empty($delete)){
            $table = array(
                'id_table' => $this->param['id'],
                'id_type' => $type['id'],
            );
            
            $changes = (bool)$this->style->drop_style_content($table,array_keys($delete));
        }
        
        return $changes;
    }
    
    /*
     * Для добавления или удаления стиля расшрения.
     * Стиль либо давляется в базу данных либо удалется.
     * [!!!] Стили "по умолчанию" попадают в базу данных только на отключение.
     * 
     * @param array   файл темы должен содерать $template[id]
     * @param string  имя типа, обычно category
     * @param array   новые стили если не задать ищит в $_POST[extends_style]
     * @param string  имя наследуемого типа, обычно post
     */
    public function style_extends(array $template, $param_type, $new_style = NULL, $new_param = 'post'){
        if(empty($this->param))
            throw new Core_Exception("Не пришли данные, выполните метод <b>load_param</b> в модели post системного модуля");
        
        if(!isset($template['id'])){
            throw new Core_Exception("Пришел не верный массив темы");
        }
        
        $changes = FALSE;
        
        //Модель для обработки типа контента
        if(empty($this->type))
            $this->type = Admin_Model::factory('type','system');
        if(empty($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        $search = array(
            'id_table' => $this->param['id'],
            'type' => $param_type,
            'extends' => 1
        );
        
        //Берем стили контента
        $style_contents = $this->style->style_contents($search,NULL,'id_style');
        
        //Отфильтровываем ID
        $ids = array();
        foreach($style_contents AS $id => $value){
            $ids[$id] = $value['status'];
        }
        

        $search = array(
            'id_template' => $template['id'],
            'default' => 1
        );
        
        //Берем стили по умолчанию
        $default = $this->style->get_styles($search,array(NULL,$new_param));
        //Берем значения
        $default_key = Arr::fill(array_flip(array_keys($default)),0);
        
        //Берем стили
        $style = (empty($new_style))? $this->param_on_error($_POST['extends_style'],'extends_style'):$new_style;
        
        //Берем стили по умолчанию которые нужно отключить
        $default = array_diff_key($default_key,$style);

        //Берем значения которые есть в таблице
        $update = array_intersect_key($style, $ids);

        //Берем значения которых нет в таблице
        $insert = array_diff_key($style,$update);


        //Очищаем от повторений
        $update = array_diff_assoc($update,$ids);

        
        //Очищаем от повторений
        $default = array_diff_key($default_key,$style);
        
        //Берем стили по умолчанию которые нужно удалить
        $default_drop = array_intersect_key($update,$default_key);
        

        //Возвращаем ключи для обновления, без удаляемых полей
        $update = array_diff_key($update,$default_drop);

        //Берем значения которые нужно отключить или удалить
        $drop = array_diff_key($ids,$style,$default);

        $drop = Arr::fill($drop,0);
                    
        //Удалить файлы по умолчанию
        $delete = array();
        foreach($drop as $key => $value){
            if(isset($style_contents[$key]) AND $style_contents[$key]['default'] == 1){
                $delete[$key] = $value;
                unset($drop[$key]);
            }
        }
        //Очищаем от повторений
        $drop = array_diff_assoc($drop,$ids);

        $delete = Arr::merge($delete,$default_drop);
        
        //Обновляем
        $update = Arr::merge($update,$drop);

        
        $insert = Arr::merge($insert,$default);

        //Очищаем, если запись все таки есть в базе
        $insert =  array_diff_assoc($insert,$ids);


        $s = $this->style->get_styles(array("id"=> array_keys($insert)));
        
        //Берем тип
        $type = $this->type->type_list($param_type);
        
        
        foreach($s AS $key => $value){
            if($s[$key]['default'] == 1 AND $insert[$key] == 1){
                unset($insert[$key]);
            }
            
            //Редкий случай совпадения, при включении стандартного типа
            if(isset($insert[$key]) AND isset($ids[$key])){
                $update[$key] = $insert[$key];
                unset($insert[$key]);
            }
        }
        
        //На удаление, update, так как у нас не может быть изменения в контексте
        $delete = Arr::merge($delete,$update);
        
        
        //Добавляем записи
        if(!empty($insert)){
            $table = array(
                'id_table',
                'id_style',
                'id_type',
                'status',
                'extends',
            );
            
            $table = array_flip($table);
            $table = Arr::fill($table,NULL);
            
            $fonds = array();
            foreach($insert AS $key => $value){
                $fonds[$key] = $table;
                $fonds[$key]['id_table'] = $this->param['id'];
                $fonds[$key]['id_style'] = $key;
                $fonds[$key]['id_type'] = $type['id'];
                $fonds[$key]['status'] = $value;
                $fonds[$key]['extends'] = 1;
            }

            $changes = (bool)$this->style->style_content_set($fonds,$table,TRUE);
        }
        
        //Удаляем
        if(!empty($delete)){
            $table = array(
                'id_table' => $this->param['id'],
                'id_type' => $type['id'],
            );
            
            $changes = (bool)$this->style->drop_style_content($table,array_keys($delete));
        }
        
        return $changes;
    }
    
    /*
     * Метод обрабатывает тип данных
     *
     * [USE] MODUL::system MODEL::type
     *
     * @return void
     */
    public function type($param = NULL,$extends = FALSE){
        if(empty($this->param))
            throw new Core_Exception("Не пришли данные, выполните метод <b>load_param</b> в модели post системного модуля");
        
        $changes = FALSE;
        //Имя ячейки в массиве
        $key = 'content_type';
        //Если нужно расширение
        if($extends === TRUE){
            $key = 'extends_content';
        }
        
        //Модель для обработки типа контента
        if(empty($this->type))
            $this->type = Admin_Model::factory('type','system');
        
        //Начальные данные
        $default_content_type = $this->param_on_error($this->param[$key],$key);
        $type_content           = $this->param_on_error($this->param['type_content'],'type_content');
        
        //Данные для изменения
        $content_type = Request::post($key,'integer',$default_content_type);
            
        //Если не совпадает, меняем тип контента
        if($content_type != $default_content_type){
            //Поля WHERE
            $search = array(
                'id' => $type_content
            );
            //Поля обновления
            $set = array(
                $key => $content_type
            );
            
            //Обновляем
            if($changes = (bool)$this->type->type_content_update($search, $set)){
                $this->param[$key] = $content_type;
            }
            
        }
        
        return $changes;
    }
    
    /*
     * Метод обрабатывает типов расширения
     *
     * [USE] MODUL::system MODEL::type
     *
     * @return void
     */
    public function type_extends($param = NULL){
        if(empty($this->param))
            throw new Core_Exception("Не пришли данные, выполните метод <b>load_param</b> в модели post системного модуля");
        
        $changes = FALSE;
        
        //Модель для обработки типа контента
        if(empty($this->type))
            $this->type = Admin_Model::factory('type','system');
        
        //Начальные данные
        $default_content_type = $this->param_on_error($this->param['extends_content'],'content_type');
        $type_content           = $this->param_on_error($this->param['type_content'],'type_content');
        
        //Данные для изменения
        $content_type = Request::post('extends_content','integer',$default_content_type);
            
        //Если не совпадает, меняем тип контента
        if($content_type != $default_content_type){
            //Поля WHERE
            $search = array(
                'id' => $type_content
            );
            //Поля обновления
            $set = array(
                'extends_content' => $content_type
            );

            //Обновляем
            if($changes = (bool)$this->type->type_content_update($search, $set)){
                $this->param['extends_content'] = $content_type;
            }
            
        }
        
        return $changes;
    }
    
    /*
     * Собирает данные
     *
     * @param  array массив данных с базы данных.
     * @return bool  TRUE если изменение было, FALSE если не было
     */
    function &data(&$param){
        if(!Request::method('post') OR empty($param))
            return FALSE;
        
        //Сохраняем для изменения в защищенных функциях
        if(empty($this->param))
            $this->param = &$param;

        //Основные
        $this->data['title']     = Str::escape(Request::post('title',NULL,''));
        $this->data['url']         = Translit::url(Request::post('url',NULL,''));
        $this->data['status']     = Request::post('status','integer');
        
        //Метаданные
        $this->data['meta_title']  = Str::escape(Request::post('meta_title'));
        $this->data['description'] = Str::escape(Request::post('description'));
        
        //Индексация
        $this->data['robots'] = array_flip(Request::post('robots',NULL,array()));
        
        //Контент
        $this->data['content']  = Str::html_decode(Request::post('content'));
        $this->data['css']         = Str::html_decode(Request::post('css'));
        $this->data['js']         = Str::html_decode(Request::post('js'));
        
        //Для изменения статической категории
        if(isset($this->param['static'])){
            $this->data['static'] = Str::html_decode(Request::post('static',NULL,NULL));
        }
        
        //Обработка данных
        $this->indexation();
        $this->main();
        $this->meta();
        $this->content();

        if(!empty($this->update) OR $this->check == TRUE){
            //$this->modified();
            $format = Registry::i()->date_format;
            $this->param['modified'] = date(strtr($format,array('%'=>'')),time());
            $update =& $this->update;
        }else{
            $update = array();
        }
        return $update;
    }
    
    /*ЗАЩИЩЕННЫЕ МЕТОДЫ ОБРАБОТКИ ДАННЫХ*/
    
    /*
     * Определяет индексацию
     *
     * @return void
     */
    protected function indexation(){
        if(empty($this->data['robots']) OR empty($this->param))
            return FALSE;
        
        //Берем индексацию из базы данных
        $robots_default = array_flip(explode(',',Request::param($this->param['robots'],NULL,'')));
        
        //Если массивы отличаются 
        if(array_diff_key($this->data['robots'],$robots_default)){
            $robots = implode(',',array_flip($this->data['robots']));
            //Массив на обновление
            $this->update['robots'] = $robots;
            
            //Для отображения на странице
            $this->param['robots'] = $robots;
        }
    }
    
    /*
     * Метод обрабатывает начальные данные как заголовок
     *
     * @return void
     */
    protected function main(){
        if(empty($this->data) OR empty($this->param))
            return FALSE;
        
        //Начальные данные
        $title = Request::param($this->param['title']);
        $url = Request::param($this->param['url']);
        $status = Request::param($this->param['status']);
        
        $static = Request::param($this->param['static']);
        
        if((isset($this->data['title']) OR is_null($this->data['title'])) AND ($this->data['title'] != $title)){
            $this->update['title'] = $this->data['title'];
            $this->param['title'] = $this->data['title'];
        }
        
        if(array_key_exists('url', $this->data) AND ($this->data['url'] != $url)){
            $this->update['url'] = $this->data['url'];
            $this->param['default_url'] = $this->param['url'];
            $this->param['url'] = $this->data['url'];
        }
        
        if((isset($this->data['status']) OR is_null($this->data['status'])) AND ($this->data['status'] != $status)){
            $this->update['status'] = $this->data['status'];
            $this->param['status'] = $this->data['status'];
        }
        
        if(isset($this->data['static']) AND ($this->data['static'] != $status)){
            $this->update['static'] = $this->data['static'];
            $this->param['static'] = $this->data['static'];
        }
    }
    
    /*
     * Метод обрабатывает meta данные
     *
     * @return void
     */
    protected function meta(){
        if(empty($this->data) OR empty($this->param))
            return FALSE;
        
        $meta_title = Request::param($this->param['meta_title']);
        $description = Request::param($this->param['description']);
        
        if((isset($this->data['meta_title']) OR is_null($this->data['meta_title'])) AND ($this->data['meta_title'] != $meta_title)){
            $this->update['meta_title'] = $this->data['meta_title'];
            $this->param['meta_title'] = $this->data['meta_title'];
        }
        
        if((isset($this->data['description']) OR is_null($this->data['description'])) AND ($this->data['description'] != $description)){
            $this->update['description'] = $this->data['description'];
            $this->param['description'] = $this->data['description'];
        }
    }
    
    /*
     * Метод обрабатывает данные контента и стиля
     *
     * @return void
     */
    protected function content(){
        if(empty($this->data) OR empty($this->param))
            return FALSE;
        
        $content = Request::param($this->param['content']);
        $css = Request::param($this->param['css']);
        $js = Request::param($this->param['js']);
        
        if((isset($this->data['content']) OR is_null($this->data['content'])) AND ($this->data['content'] != $content)){
            $this->update['content'] = $this->data['content'];
            $this->param['content'] = $this->data['content'];
        }
        
        if((isset($this->data['css']) OR is_null($this->data['css'])) AND ($this->data['css'] != $css)){
            $this->update['css'] = $this->data['css'];
            $this->param['css'] = $this->data['css'];
        }
        
        if((isset($this->data['js']) OR is_null($this->data['js'])) AND ($this->data['js'] != $js)){
            $this->update['js'] = $this->data['js'];
            $this->param['js'] = $this->data['js'];
        }
    }
    
    /*
     * Метод обрабатывает данные контента и стиля
     *
     * @return void
     */
    function url($type){
        if(empty($this->update) OR empty($this->param) OR !array_key_exists('url', $this->update))
            return FALSE;
        
        //var_dump($this->param);
        if(!isset($this->url) OR !is_object($this->url))
            $this->url = Admin_Model::factory('url','system');
        
        if(!$url = $this->url->get(array('url'=>$this->update['url']))){
            if(!$this->url->update($this->update['url'],array('url'=>$this->param['default_url']))){
                if(!$this->url->update($this->update['url'],array('type'=>$type,'id_table'=>$this->param['id']))){
                    //Обновить поля не удалось, вставляем новый URL и выводим сообщение.
                    if($this->url->insert(array('url'=>$this->update['url'],'type'=>$type,'id_table'=>$this->param['id']))){
                        $this->error->set('massage','info',array('title'=>'URL не удалось обновить.','massage'=>'URL был сохранен, но обратите внимание, почему его не оказалось в базе.'));
                    }else{
                        throw new Core_Exception('Не удалось вставить URL. Обратитесь к администратору.');
                    }
                }
            }
        }else{
            unset($this->update['url']);
            throw new Core_Exception('Такой URL есть');
        }

        return $this->update;
    }
    
    
    /*ЗАЩИЩЕННЫЕ ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ*/
    protected function param_on_error(&$param,$name = NULL){
        if(!isset($param))
            throw new Core_Exception("Нет важного значения <b>{$name}</b>");
        return $param;
    }
}