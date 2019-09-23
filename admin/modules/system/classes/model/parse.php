<?php defined('MODPATH') OR exit();


class Model_Parse_System_Admin{
    
    //@var object модель style модуля style
    public $style;
    
    //@var object модель type модуля style
    public $type;
    
    //@var object модуль xml
    public $xml;
    
    function __construct(){
        $this->xml = Module::factory('xml',TRUE);
        $this->error = Admin_Model::factory('error','system');
    }
    /*
     * Формируем используемые и не используемые стили
     *
     * @param  array массив которые должен содержать два параметра $search['id_table'] и $search['type']
     * @return array стили
     * @use system|model style
     */
    function style(array $search, $template = NULL){
        //Проверяем есть ли модель стилей
        if(empty($this->style) OR !is_object($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        //Если нет темы, пытаемся ее получить
        if(!isset($template['name']))
            $template = Registry::i()->root_template;
        
        //Проверяем корректность массива
        if(!isset($search['type'])){
            throw new Core_Exception('Не правильный массив $search');
        }
        
        //Берем все стили по типу
        $style = $this->style->get_styles($template['name'],array($search['type'],NULL));
        
        //Берем стили относящиеся к данному контексту
        $style_contents = (isset($search['id_table']))?$this->style->style_contents($search,NULL,'id_style'):array();
        
        //Вычисляем расхождение массива, для отсеивания используемых и не используемых стилей
        $styles = Arr::diff_key($style,$style_contents,TRUE);
        
        if(!isset($search['id_table'])){
            if($keys = Request::post('style')){
                foreach($styles AS $key => $s){
                    if(isset($keys[$key])){
                        $styles[$key]['status'] = 1;
                    }else{
                        $styles[$key]['status'] = 0;
                    }
                }
            }
        }
        //Отсеиваем используемые и не используемые
        if(!empty($style_contents)){
            foreach($style_contents AS $key => $stype_content){
                if($stype_content['status']){
                    $style_contents['enabled'][$key] = $style[$key];
                    $style_contents['enabled'][$key]['status'] = $stype_content['status'];
                }else{
                    $styles[$key] =  $style[$key];
                    $styles[$key]['status'] = $stype_content['status'];
                }
                unset($style_contents[$key]);
            }
            
            //Сортируем стили которые используются
            foreach($style_contents AS &$stype_content){
                $stype_content = $this->style->style_sort($stype_content,FALSE);
            }
        }

        //Сортируем стили которые не используются
        if(!empty($styles)){
            $styles = $this->style->style_sort($styles,TRUE,TRUE);
            if(!empty($styles['default'])){
                $fond = array();
                $fond[NULL] = $styles['default'];
                unset($styles['default']);
                Arr::unshift($style_contents,'default',$fond);
            }
        }
        
        $date = array();
        $date['style_contents'] = $style_contents;
        $date['styles'] = $styles;

        return $date;
    }
    
    /*
     * Формируем используемые и не используемые стили
     *
     * @param  array массив которые должен содержать два параметра $search['id_table'] и $search['type']
     * @return array стили
     * @use system|model style
     */
    function xml_style(array $search, $template = NULL,$file = NULL){
        
        //Проверяем есть ли модель стилей
        if(empty($this->style) OR !is_object($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        //Если нет темы, пытаемся ее получить
        if(!isset($template['name']))
            $template = Registry::i()->root_template;
        
        //Проверяем корректность массива
        if(!isset($search['type'])){
            throw new Core_Exception('Не правильный массив $search');
        }
        
        //Берем все стили по типу
        $style = $this->style->get_styles($template['name'],array($search['type'],NULL));
        
        //Берем стили относящиеся к данному контексту
        $style_contents = (isset($search['id_table']))?$this->style->style_contents($search,NULL,'id_style'):array();
        
        //Отмечаем используемые и не используемые стили
        foreach($style_contents AS $key => $value){
            if(isset($style[$key])){
                $style[$key]['status'] = $value['status'];
            }
        }
        
        $xml = $this->xml->preg_load($style,'admin_module|system::style','admin_template|'.$file);

        $date = array();
        $date['xml'] = $xml;

        return $date;
    }
    
    
    /*
     * Формируем используемые и не используемые стили
     *
     * @param  array массив которые должен содержать два параметра $search['content_type'] и $search['type']
     * @return array стили
     * @use system|model style
     */
    function type(array $search, $template = array()){
        
        //Проверяем есть ли модель стилей
        if(empty($this->style) OR !is_object($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        //Если нет темы, пытаемся ее получить
        if(!isset($template['name']))
            $template = Registry::i()->root_template;
        
        //Проверяем корректность массива
        if(!isset($search['type'])){
            throw new Core_Exception('Не правильный массив $search');
        }
        
        $content_type = '';
        if($content_type = $this->type->get_types($template['name'],$search['type'])){
            if(isset($search['content_type'])){
                $find = $content_type[$search['content_type']];
                unset($content_type[$search['content_type']]);
                Arr::unshift($content_type,NULL,$find);
            }
        }
        
        $date = array();
        $date['content_type'] = $content_type;
        
        return $date;
    }
    
    /*
     * Формируем используемые и не используемые стили
     *
     * @param  array массив которые должен содержать два параметра $search['content_type'] и $search['type']
     * @return array стили
     * @use system|model style
     */
    function xml_type(array $search, $template = array()){
        
        //Проверяем есть ли модель стилей
        if(empty($this->style) OR !is_object($this->style))
            $this->style = Admin_Model::factory('style','system');
        
        //Если нет темы, пытаемся ее получить
        if(!isset($template['name']))
            $template = Registry::i()->root_template;
        
        //Проверяем корректность массива
        if(!isset($search['type'])){
            throw new Core_Exception('Не правильный массив $search');
        }
        
        $content_type = '';
        if($content_type = $this->type->get_types($template['name'],$search['type'])){
            if(isset($search['content_type'])){
                $find = $content_type[$search['content_type']];
                $find['status'] = 1;
                unset($content_type[$search['content_type']]);
                Arr::unshift($content_type,NULL,$find);
            }
        }
        $date = array();
        $date['xml'] = $this->xml->preg_load($content_type,'admin_module|system::type','template|type_type');
        
        return $date;
    }
    
    /*
     * Обрабатывают POST
     *
     * @return array стили
     */
    function post(){
        //Модель обработки ошибок
        $error = Admin_Model::factory('error','system');
        
        $fond = array();
        
        $fond['status'] = 1;
        
        if(Request::method('post')){
            //Описательные
            if(!$fond['title'] = Request::post('title')){
                $error->errors(array('massage'=>'Не заполнено поле title','path'=>'input[name="title"]','class'=>'input_error'),'description');
            }
            if(!$fond['url'] = Request::post('url')){
                $error->errors(array('massage'=>'Не заполнено поле URL','path'=>'input[name="url"]','class'=>'input_error'),'description');
            }
            $fond['status']      = Request::post('status',NULL,1);
            $fond['meta_title']  = Request::post('meta_title');
            $fond['description'] = Request::post('description');
            $fond['robots']      = Request::post('robots');
            //Типы
            if(!$fond['content_type'] = Request::post('content_type')){
                $error->errors(array('massage'=>'Не выбран тип данных','path'=>'.types .box','class'=>'input_error'),'description');
            }
            //Стили
            $fond['style'] = Request::post('style');
            //Контент
            $fond['content'] = Request::post('content');
            $fond['css']      = Request::post('css');
            $fond['js']      = Request::post('js');
        }

        return $fond;
    }
}