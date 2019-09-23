<?php

    /*
     * Вывод одного стиливого файла
     *
     * @param int id стиля
     */
    function style($id){
        
        //Загружаем стиль
        $style = $this->style->get_style($id);
        $content = '';
        
        if(Request::method('post')){
            
            //Вставляем в POST что бы выводилось уже измененное имя
            $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
            
            //Необязательные поля
            $_POST['title'] = $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
            $_POST['description'] = $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
            // ` - что бы небыло ошибки при вставке
            $fonds['`default`'] =  Request::param(Request::post('default'),NULL,0);
            
            
            if($content = Request::post('file') OR $content !== FALSE){
                $style = $this->style->get_style($id);
                if(File::set_content($style['path'],$content,TRUE) !== FALSE){
                    
                    $new_fonds = array(
                        'name' => $fonds['name'],
                        'id_template' => $style['id_template'],
                        'id_type' => $style['id_type'],
                        'style_type' => $style['id_style_type'],
                    );
                    if(($test_style = $this->style->get_style($new_fonds) AND !empty($test_style)) AND $test_style['id'] != $id){
                        $this->error->set('Такое имя файла уже существует, переименования небыло');
                    }else{
                        $this->style->style_update($id,$fonds);
                        $new_style = $this->style->get_style($id,TRUE);
                        if(rename($style['path'],$new_style['path'])){
                            $this->error->set('Файл переименован',TRUE);
                        }
                        $style = $new_style;
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
        $date['messages'] = $this->error->get();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_file',$date);
    }
    /*
     * Вывод всех стилей
     *
     * @param int id темы
     */
    function styles($id){
        
        //Загружаем тему
        $template = $this->method->get_template(intval($id));
        
        //Берем все типы данных
        $type_list = $this->type->type_list();
        foreach($type_list AS $key => $value){
            $type_list[$key] = array();
        }
        
        $styles = $this->style->get_styles($template['name']);

        //Создаем нужный для нас массив без файлов по умолчанию
        $styles = $this->style->style_sort($styles,FALSE);
        
        //Объеденяем для полноты массива
        $styles = Arr::merge($styles,$type_list);

        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $id,
                    );
        
        //Заголовое и мета заголовок 
        Registry::i()->title = 'Стили темы: '.$template['name'];
        Registry::i()->meta_title = 'Стили';
        
        //Дополняем стили
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['template'] = $template;
        $date['styles'] = $styles;
        $date['messages'] = $this->error->get();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_styles',$date);
    }
    
    /*
     * Создание стиля
     *
     * @param int id темы
     * @param string тип данных
     * @param string тип стиля
     */
    function create_style($id,$type = NULL,$style_type = NULL){
        
        //Загружаем тему
        $template = $this->method->get_template(intval($id));
        
        if(Request::method('post')){
            $fonds = array();
            
            //Собераем данные
            $fonds['id_template'] = $template['id'];
            //Записываем дополнительно в переменные для вывода в select
            $fonds['id_type'] = $type = Request::param(Request::post('type_id'),NULL,NULL);
            $fonds['style_type'] = $style_type = Request::post('style_type');
            
            //Вставляем в POST что бы выводилось уже измененное имя
            $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
            
            //Необязательные поля
            $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
            $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
            // ` - что бы небыло ошибки при вставке
            $fonds['`default`'] =  Request::param(Request::post('default'),NULL,0);

            if(empty($fonds['name'])){
                //Если нет имени выводим ошибку
                $this->error->set("Пустое имя файла");
            }
            elseif(!$this->style->get_style($fonds)){
                //Записываем в базу данных
                $result = $this->style->style_set($fonds);
                
                //Берем стиль, для создания пути
                $get_style = $this->style->get_style($result[0],TRUE);

                if(File::create_file($get_style['path'])){
                    header("Location: ".Url::root()."/templates/style/{$result[0]}".Url::query(array('create'=>1),'auto'));
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
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_create_style',$date);
    }
    /*
     * Удаление стиля
     *
     * @param int id темы
     * @param string тип данных
     * @param string тип стиля
     */
    function drop_style($template_id,$id = NULL){
        
        if($id === NULL){
            $id = $template_id;
            unset($template_id);
        }
        //Выводим наверх, массив для отображения
        $date = array();
        
        $style = $this->style->get_style($id);

        //Загружаем тему
        if(isset($style['template'])){
            $template = $this->method->get_template($style['template']);
        }elseif(isset($template_id)){
            $template = $this->method->get_template(intval($template_id));
        }
        if(!empty($style)){
            
            $style_contents = '';
            if($style_contents = $this->style->style_contents(array("id_style"=>$id,'status'=>1))){
                //Создаем нужный для нас массив без файлов по умолчанию
                $style_contents = $this->style->style_sort($style_contents,FALSE);
                
                $this->error->set('Нельзя удалять стиль, его использут следующие разделы');
            }else{
                if($this->style->drop_style($id)){
                    File::unlink($style['path']);
                    $this->error->set('Стиль удален',TRUE);
                }else{
                    $this->error->set('Стиль не удален');
                }
            }
            //Заполняем массив на вывод
            $date['styles'] = $style_contents;
        }else{
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
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_drop_style',$date);
    }
    
    
    /*
     * Вывод одного типа данных
     *
     * @param int id стиля
     */
    function type($type,$id_template,$types = NULL){
        
        if(Request::method('post')){
            if($content = Request::post('file')){
                $type = $this->type->get_type($id);
                if(File::set_content($type['path'],$content,TRUE))
                    $this->error->set('Файл обновлен',TRUE);
                else
                    $this->error->set('Ошибка обновления файла');
            }
        }
        
        //Загружаем стиль
        $type = $this->type->get_type($id);
        $content = '';

        if(!empty($type)){
            $content = (!empty($type['path']))? File::get_content($type['path'],TRUE) : FALSE;
            if($content === FALSE){
                $this->error->set('Файла не существует');
            }
        }else{
            $this->error->set('Типа не существует');
        }
        
        $template = $this->method->get_template($type['template']);
        
        //Пункты меню
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        Registry::i()->fonds['action'] = 'template template_file '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['file'] = $type;
        $date['content'] = $content;
        $date['messages'] = $this->error->get();
        
        //Загружаем тему
        return Admin_Template::factory(Registry::i()->template,'content_templates_file',$date);
    }
?>