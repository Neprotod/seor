<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Post_Posts_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','posts');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
        $this->url = Admin_Model::factory('url','system');
    }
    
    function get($id, $category){
        
        $template = Registry::i()->root_template;
        
        //Индексация
        $robots = array('index' => 1,'follow' =>1,'noindex' =>0,'nofollow' =>0);
        
        $post = $this->method->get_post($id,$template['id']);
        
        //Обработка пост запроса
        $this->post_method($post,$template);
        
        
        if(!empty($post['robots'])){
            $check_robots = array_flip(explode(',',$post['robots']));
            //Очищаем значения массива
            $robots = Arr::fill($robots,NULL);
            
            //Заполняем массив 1
            $check_robots = Arr::fill($check_robots,1);
            
            //Заполняем нужные ячейки для указания индексации
            $robots = Arr::merge($robots,$check_robots);
        }
        
        Registry::i()->title = "Пост: ".$post['title'];
        
        $date = array();

        $date['post'] = $post;
        $date['robots'] = $robots;
        $date['template'] = $template;
        $date['style'] = $this->style($template,$post);
        $date['type'] = $this->type($template,$post);
        
        $fonds = array();
        $fonds['date'] = $date;

        return $fonds;
    }
    
    function post_method(&$category,$template){
        if(Request::method('post')){
            $post = Admin_Model::factory('post','system');
            
            //Загружаем параметр
            $post->load_param($category);
            
            ////////////////////////////////
            /////// Обработка стилей
            ////////////////////////////////
            $post->style($template,'category');

            ////////////////////////////////
            /////// Обработка типов
            ////////////////////////////////
            $post->type();
            
            ////////////////////////////////
            /////// Обработка стилей расширения
            ////////////////////////////////
            $post->style_extends($template,'category');

            
            ////////////////////////////////
            /////// Обработка типов расширения
            ////////////////////////////////
            $post->type_extends($template,'category');
            
            ////////////////////////////////
            /////// Запись описательных данных
            ////////////////////////////////
            if($update = $post->data($category)){
                if(isset($update['url'])){
                    if(!$url = $this->url->get(array('url'=>$update['url']))){
                        $this->url->update($update['url'],array('url'=>$category['default_url']));
                    }else{
                        unset($update['url']);
                        throw new Core_Exception('Такой URL есть');
                    }
                }

                $this->method->update_categories($category['id'],$update);
            }
            
        }
    }
    
    
    /*
     * Создает отображения стилей
     *
     * @param  array  массив темы 
     * @param  array  массив category 
     * @return string отображение стилей
     */
    protected function style($template,$category){
        
        //Стили категории
        $style = $this->style->get_styles($template['name'],array('post',NULL));
        
        $search = array(
            'id_table' => $category['id'],
            'type' => 'category',
        );
        
        //Используемые стили
        $style_contents = $this->style->style_contents($search,NULL,'id_style');
        
        //Не используемые стили
        $styles = Arr::diff_key($style,$style_contents,TRUE);
        
        
        if(!empty($style_contents)){
            foreach($style_contents AS $key => $stype_content){
                if(isset($style[$key])){
                    if($stype_content['status']){
                        $style_contents['enabled'][$key] = $style[$key];
                        $style_contents['enabled'][$key]['status'] = $stype_content['status'];
                    }else{
                        $styles[$key] =  $style[$key];
                        $styles[$key]['status'] = $stype_content['status'];
                    }
                }
                unset($style_contents[$key]);
            }

            foreach($style_contents AS &$stype_content){
                $stype_content = $this->style->style_sort($stype_content,FALSE);
            }
        }

        if(!empty($styles)){
            $styles = $this->style->style_sort($styles,TRUE,TRUE);
            if(!empty($styles['default'])){
                $fond = array();
                $fond[NULL] = $styles['default'];
                unset($styles['default']);
                Arr::unshift($style_contents,'default',$fond);
            }
        }
        if(!empty($extends)){
            $extends = $this->style->style_sort($extends,TRUE,TRUE);
        }
        $date = array();
        $date['style_contents'] = $style_contents;
        $date['styles'] = $styles;

        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view."_style",$date);
    }
    
    
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив category 
     * @return string отображение типов
     */
    protected function type($template,$category){
        
        $content_type = '';
        if($content_type = $this->type->get_types($template['name'],'category')){
            if(isset($category['content_type'])){
                $find = $content_type[$category['content_type']];
                unset($content_type[$category['content_type']]);
                Arr::unshift($content_type,NULL,$find);
            }
        }
        
        //Расширение для поста
        if($extends_content = $this->type->get_types($template['name'],'post')){
            if(isset($category['extends_content'])){
                $find = $extends_content[$category['extends_content']];
                unset($extends_content[$category['extends_content']]);
                Arr::unshift($extends_content,NULL,$find);
            }
        }
        $date = array();
        $date['content_type'] = $content_type;
        $date['extends_content'] = $extends_content;

        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view."_type",$date);
    }
}