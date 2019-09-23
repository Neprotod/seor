<?php defined('MODPATH') OR exit();

/*
 * Модель определяет к какому типу относится URL  
 */
class Model_Page_Pages_Admin{
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','pages');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
        $this->url = Admin_Model::factory('url','system');
        /*
        $this->xml = Admin_Module::factory('xml',TRUE);
        
        $this->xml->style('default');
        */
    }
    
    function get($id){
        
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
        
        Registry::i()->title = "Страница";
        
        $date = array();
        $date['page'] = $page;
        $date['robots'] = $robots;
        $date['template'] = $template;
        $date['style'] = $this->style($template,$page);
        $date['type'] = $this->type($template,$page);
        
        
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
    protected function style($template,$page){
        
        $style = $this->style->get_styles($template['name'],array('page',NULL));
    
        $search = array(
            'id_table' => $page['id'],
            'type' => 'page',
        );
        $style_contents = $this->style->style_contents($search,NULL,'id_style');
        
        $styles = Arr::diff_key($style,$style_contents,TRUE);
        
        if(!empty($style_contents)){
            foreach($style_contents AS $key => $stype_content){
                if($stype_content['status']){
                    $style_contents['enabled'][$key] = $style[$key];
                    $style_contents['enabled'][$key]['status'] = $stype_content['status'];
                }else{
                    $styles[$key] =  $style[$key];
                    $styles[$key]['status'] = $stype_content['status'];
                    //$style_contents['disabled'][$key] =  $style[$key];
                    //$style_contents['disabled'][$key]['status'] = $stype_content['status'];
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
        $date = array();
        $date['style_contents'] = $style_contents;
        $date['styles'] = $styles;
        
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view."_style",$date);
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
            $search = array(
                'id_table' => $page['id'],
                'type' => 'page',
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
            $default = $this->style->get_styles($search,array(NULL,'page'));
            //Берем значения
            $default_key = Arr::fill(array_flip(array_keys($default)),0);
            
            //Берем стили
            $style = Request::param($_POST['style'],NULL,array());
        

            //Берем стили по умочанию которые нужно отключить
            $default = array_diff_key($default_key,$style);

            //Берем значения которые есть в таблице
            $update = array_intersect_key($style, $ids);

            //Берем значения которых нет в таблице
            $insert = array_diff_key($style,$update);


            //Очищаем от повторений
            $update = array_diff_assoc($update,$ids);

            
            //Очищаем от повторений
            $default = array_diff_key($default_key,$style);
            
            //Берем стили по умочанию которые нужно удалить
            $default_drop = array_intersect_key($update,$default_key);
            

            //Возвращаем ключи для обнавления, без удалеммых полей
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
            
            //Обнавляем
            $update = Arr::merge($update,$drop);

            
            $insert = Arr::merge($insert,$default);

            //Очищаем, если запись все таки есть в базе
            $insert =  array_diff_assoc($insert,$ids);
                

            $s = $this->style->get_styles(array("id"=> array_keys($insert)));
            
            //Берем тип
            $type = $this->type->type_list('page');
            
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
            
            if(!empty($update)){
                $fonds = array();
                foreach($update AS $key => $value){
                    $fonds[$value][$key] = $value;
                }
                
                $table = array(
                    'id_table'=> $page['id'],
                    'id_type'=> $type['id'],
                );
                foreach($fonds AS $status => $fond){
                    //Запрос должен идти по id стиля, таблицы и типа
                    $this->style->style_content_update(array('status'=>$status),$table,array_keys($fond));
                }
                
            }
            
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
                    $fonds[$key]['id_table'] = $page['id'];
                    $fonds[$key]['id_style'] = $key;
                    $fonds[$key]['id_type'] = $type['id'];
                    $fonds[$key]['status'] = $value;
                }

                $this->style->style_content_set($fonds,$table,TRUE);
            }

            //Удалем
            if(!empty($delete)){
                $table = array(
                    'id_table' => $page['id'],
                    'id_type' => $type['id'],
                );
                
                $this->style->drop_style_content($table,array_keys($delete));
            }
            
            ////////////////////////////////
            /////// Обработка типов
            ////////////////////////////////
            $post->type();

            ////////////////////////////////
            /////// Запись описательных данных
            ////////////////////////////////
            if($update = $post->data($page)){
                if(isset($update['url'])){
                    if(!$url = $this->url->get(array('url'=>$update['url']))){
                        $this->url->update($update['url'],array('url'=>$page['default_url']));
                    }else{
                        unset($update['url']);
                        throw new Core_Exception('Такой URL есть');
                    }
                }

                $this->method->update_page($page['id'],$update);
            }
            
        }
    }
    /*
     * Создает отображения типов
     *
     * @param  array  массив темы 
     * @param  array  массив page 
     * @return string отображение типов
     */
    protected function type($template,$page){
        
        $content_type = '';
        if($content_type = $this->type->get_types($template['name'],'page')){
            if(isset($page['content_type'])){
                $find = $content_type[$page['content_type']];
                unset($content_type[$page['content_type']]);
                Arr::unshift($content_type,NULL,$find);
            }
        }
        
        $date = array();
        $date['content_type'] = $content_type;
        
        return Admin_Template::factory(Registry::i()->template,Registry::i()->template_view."_type",$date);
    }
}