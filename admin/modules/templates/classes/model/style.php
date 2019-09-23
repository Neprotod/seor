<?php

/*
 * Модель для отображения получения
 */
class Model_Style_Templates_Admin{
    
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','templates');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
    }
    
    function get($id){
        //Загружаем стиль
        $style = $this->style->get_style($id,TRUE);
        $content = '';
        if(Request::method('post')){
            //Берем массив с POST запросом
            $fond = $this->post();
            if($content = Request::post('file') OR $content !== FALSE){
                if(File::set_content($style['path'],$content,TRUE) !== FALSE){
                    //Массив для проверки
                    $check = array(
                        'name' => $fond['name'],
                        'id_template' => $style['id_template'],
                        'id_type' => $style['id_type'],
                        'style_type' => $style['style_type'],
                    );
                    //Проверяем есть ли такое же имя
                    $check = $this->style->get_style($check);
                    
                    if(empty($check) OR $check['id'] == $style['id']){
                        //Записываем новое значение
                        $this->style->style_update($style['id'],$fond);
                        //Берем новое значение
                        $check = $this->style->get_style($style['id'],TRUE);
                        
                        if($check['name'] != $style['name']){
                            if(File::rename($style['path'],$check['path'])){
                                //Файл переименован, сохраняем для вывода
                                $style = $check;
                                $this->error->set('Файл переименован',TRUE);
                            }else{
                                //Ошибка переименования, откат к предыдущему имени
                                $this->style->style_update($style['id'],array('name'=>$style['name']));
                                
                                $this->error->set('Ошибка переименования, скорее всего имя уже существует');
                            }
                        }
                        
                    }else{
                        //На всякий случай, если имени нет
                        if(!isset($check['name']))
                            $check['name'] = '';
                        
                        $this->error->set("Имя файла <b>{$check['name']}</b> уже существует");
                    }
                    $this->error->set('Файл обновлен',TRUE);
                }else{
                    $this->error->set('Ошибка обновления файла');
                }
            }
        }
        
        
        if(!empty($style)){
            $content = (!empty($style['path']))? File::get_content($style['path'],TRUE) : NULL;
            if($content === FALSE){
                $this->error->set('Файла не существует');
            }
        }else{
            $this->error->set('Стиля не существует');
        }
        
        $template = $this->method->get_template($style['template']);

        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        Registry::i()->fonds['action'] = 'template template_file '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['file'] = $style;
        $date['content'] = $content;
        $date['messages'] = $this->error->output();
        $date['template'] = $template;
        
        $fonds = array();
        $fonds['template'] = $template;
        $fonds['date'] = $date;
        
        return $fonds;
    }
    /*
     * Создание стиля
     *
     * @param int id темы
     * @param string тип данных
     * @param string тип стиля
     */
    function create($id,$type = NULL,$style_type = NULL){
        
        //Загружаем тему
        $template = $this->method->get_template(intval($id));
        
        if(Request::method('post')){
            //Берем массив с POST запросом
            $fond = $this->post();
            $fond['id_template'] = $template['id'];
            if(isset($fond['type']))
                $type = Request::post('type');
            if(isset($fond['style_type']))
                $style_type = Request::post('style_type');
            
            $check = $fond;
            unset($check['style_type']);
            $check['id_style_type'] = $fond['style_type'];
            if(empty($fond['name'])){
                //Если нет имени выводим ошибку
                $this->error->set("Пустое имя файла");
            }
            elseif(!$this->style->get_style($check)){
                //Записываем в базу данных
                $result = $this->style->style_set($fond);
                
                //Берем стиль, для создания пути
                $get_style = $this->style->get_style($result[0],TRUE);

                if(File::create_file($get_style['path'])){
                    header("Location: ".Url::root()."/templates/style/get/{$result[0]}".Url::query(array('create'=>1),'auto'));
                }else{
                    //Удаляем из базы данных, если файл не создался.
                    $this->style->drop_style($result[0]);
                    $this->error->set("Файл не создался, обратитесь к раздработчику");
                }
            }else{
                $this->error->set("Такое имя файла уже существует");
            }
        }
        
        //Берем все типы данных
        $type_list = $this->type->type_list();
        //Дополняем массив нужным значением
        foreach($type_list AS $key => $value){
            $type_list[$key]['exist'] = FALSE;
        }
        
        //Добавляем общий стиль
        Arr::unshift($type_list, NULL,array('exist'=> FALSE));
        
        //Проверяем к какому типу относится
        if(array_key_exists($type,$type_list))
            $type_list[$type]['exist'] = TRUE;
        else
            $type_list[NULL]['exist'] = TRUE;

        //Берем все типы стиля
        $style_list = $this->style->style_list();

        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $id,
                    );
        
        //Заголовое и мета заголовок 
        Registry::i()->title = 'Создаем стиль в теме: '.$template['name'];
        Registry::i()->meta_title = 'Создание стиля';
        
        //Дополняем стили
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['template'] = $template;
        $date['type_list'] = $type_list;
        $date['style_list'] = $style_list;
        $date['style_type'] = $style_type;
        $date['type_id'] = $type;
        $date['messages'] = $this->error->get();
        
        $fonds['template'] = $template;
        $fonds['date'] = $date;
        //Загружаем тему
        return $fonds;
    }
    /*
     * Удалить стиль
     *
     * @param int id темы
     * @param string тип данных
     * @param string тип стиля
     */
    function drop($id_template,$id = NULL){
        
        if($id === NULL){
            $id = $template_id;
            unset($template_id);
        }
        $drop = FALSE;
        //Выводим наверх, массив для отображения
        $date = array();
        
        $style = $this->style->get_style($id);

        //Загружаем тему
        if(isset($template_id)){
            $template = $this->method->get_template(intval($template_id));
        }
        elseif(isset($style['template'])){
            $template = $this->method->get_template($style['template']);
        }
        
        if(!empty($style)){
            
            $style_contents = '';
            if($style_contents = $this->style->style_contents(array("id_style"=>$id,'status'=>1))){
                //Создаем нужный для нас массив без файлов по умолчанию
                $style_contents = $this->style->style_sort($style_contents,FALSE);
                
                $this->error->set('Нельзя удалять стиль, его использут следующие разделы');
            }else{
                if(Request::method('post')){
                    if($this->style->drop_style($id)){
                        $drop = TRUE;
                        File::unlink($style['path']);
                        $this->error->set('Стиль удален',TRUE);
                    }else{
                        $this->error->set('Стиль не удален');
                    }
                }
            }
            //Заполняем массив на вывод
            $date['styles'] = $style_contents;
        }else{
            $drop = TRUE;
            $this->error->set('Стиля не существует');
        }

        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        //Заголовое и мета заголовок 
        Registry::i()->title = 'Удаления стиля темы: '.$template['name'];
        Registry::i()->meta_title = 'Удаления стиля';
        
        //Дополняем стили
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        //Расширение для модулей
        $modules = array(
                'page' => 'pages',
                'post' => 'posts',
                'category' => 'categories',
            );
        
        $date['template'] = $template;
        $date['modules'] = $modules;
        $date['messages'] = $this->error->get();
        $date['drop'] = $drop;

        $fonds = array();
        $fonds['date'] = $date;
        
        return $fonds;
    }
    /*
     * Метод заполняет HTML стилем
     *
     * @param  array массив значений
     * @return string строка уже готового стиля
     */
    function box($template_id,$styles = NULL){
        
        $return = FALSE;
        if(empty($styles)){
            $return = TRUE;
            $styles = $this->style->get_styles(intval($template_id));
        }
        //Берем все типы данных
        $type_list = $this->type->type_list();
        foreach($type_list AS $key => $value){
            $type_list[$key] = array();
        }
        //Добавляем общий стиль
        Arr::unshift($type_list, NULL,array());
        
        //Создаем нужный для нас массив без файлов по умолчанию
        $styles = $this->style->style_sort($styles);
        
        //Если есть стиль по умолчанию, добавляем для сортировки
        if(isset($styles['default']))
            Arr::unshift($type_list, 'default',array());
        
        //Объеденяем для полноты массива
        $styles = Arr::merge($type_list,$styles);
        
        $date = array();
        $date['styles'] = $styles;
        $date['template_id'] = $template_id;
        if($return === FALSE){
            return Admin_Template::factory(Registry::i()->template,'content_templates_style_box',$date);
        }else{
            $fonds = array();
            $fonds['date'] = $date;
            
            return $fonds;
        }
            
    }
    /*
     * Метод соберает нужный массив post
     *
     * @return array отфильтрованный массив POST для style
     */
    protected function post($template_id = NULL){
        $fonds = array();
        //Собераем данные
        if(!empty($template_id))
            $fonds['id_template'] = $template_id;

        //Записываем дополнительно в переменные для вывода в select
        if(isset($_POST['type_id']))
            $fonds['id_type'] = Request::param(Request::post('type_id'),NULL,NULL);
        if(isset($_POST['style_type']))
            $fonds['style_type'] = Request::post('style_type');
        
        //Вставляем в POST что бы выводилось уже измененное имя
        $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
        
        //Необязательные поля
        $_POST['title'] = $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
        $_POST['description'] = $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
        // ` - что бы небыло ошибки при вставке
        $_POST['default'] = $fonds['`default`'] =  Request::param(Request::post('default'),NULL,0);
        
        return $fonds;
    }
}
?>