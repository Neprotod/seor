<?php

/*
 * Модель для отображения получения
 */
class Model_Type_Templates_Admin{
    
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','templates');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
    }
    
    function get($id){
        //Загружаем стиль
        $type = $this->type->get_type($id,TRUE);
        $content = '';
        
        if(Request::method('post')){
            //Берем массив с POST запросом
            $fond = $this->post();
            if($content = Request::post('file') OR $content !== FALSE){
                if(File::set_content($type['path'],$content,TRUE) !== FALSE){
                    //Массив для проверки
                    $check = array(
                        'name' => $fond['name'],
                        'id_template' => $type['id_template'],
                        'id_type' => $type['id_type'],
                    );
                    //Проверяем есть ли такое же имя
                    $check = $this->type->get_type($check);
                    
                    if(empty($check) OR $check['id'] == $type['id']){
                        //Записываем новое значение
                        $this->type->type_update($type['id'],$fond);
                        //Берем новое значение
                        $check = $this->type->get_type($type['id'],TRUE);
                        
                        if($check['name'] != $type['name']){
                            if(File::rename($type['path'],$check['path'])){
                                //Файл переименован, сохраняем для вывода
                                $type = $check;
                                $this->error->set('Файл переименован',TRUE);
                            }else{
                                //Ошибка переименования, откат к предыдущему имени
                                $this->type->type_update($type['id'],array('name'=>$type['name']));
                                
                                $this->error->set('Ошибка переименования, скорее всего имя уже существует');
                            }
                        }
                        
                    }else{
                        //На всякий случай, если имени нет
                        if(!isset($check['name']))
                            $check['name'] = '';
                        
                        $this->error->set("Имя файла <b>{$check['name']}</b> уже существует");
                    }
                    $this->error->set('massage','success',array('massage'=>'Файл обновлен.'));
                }else{
                    $this->error->set('error','error',array('massage'=>'Ошибка обновления файла'));
                }
            }
        }
        
        
        if(!empty($type)){
            
            $content = (!empty($type['path']))? File::get_content($type['path'],TRUE) : FALSE;
            if($content === FALSE){
                $this->error->set('Файла не существует');
                if(File::create_file($type['path'])){
                    $this->error->set('Файл создан',TRUE);
                }
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
        $date['messages'] = $this->error->output();
        $date['template'] = $template;
        $fonds = array();
        $fonds['date'] = $date;
        $fonds['date'] = $date;
        //Загружаем тему
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
        
    }
    /*
     * Удалить стиль
     *
     * @param int id темы
     * @param string тип данных
     * @param string тип стиля
     */
    function drop($id_template,$id = NULL){
        
    }
    /*
     * Метод заполняет HTML типа
     *
     * @param  array массив значений
     * @return string строка уже готового типа
     */
    function box($template_id,$types = NULL){
        
        $return = FALSE;
        if(empty($types)){
            $return = TRUE;
            $types = $this->type->get_types(intval($template_id));
        }
        //Берем все типы данных
        $type_list = $this->type->type_list();
        foreach($type_list AS $key => $value){
            $type_list[$key] = array();
        }
        
        //Создаем нужный для нас массив без файлов по умолчанию
        $types = $this->type->type_sort($types);

        //Объеденяем для полноты массива
        $types = Arr::merge($type_list,$types);
        
        $date = array();
        $date['types'] = $types;
        $date['template_id'] = $template_id;
        if($return === FALSE){
            return Admin_Template::factory(Registry::i()->template,'content_templates_type_box',$date);
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
        //Берем расширение файла если
        if(isset($_POST['ext']))
            $_POST['ext'] = $fonds['ext'] =  Request::param(Request::post('ext'),NULL,NULL);
        
        //Вставляем в POST что бы выводилось уже измененное имя
        $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
        //Необязательные поля
        echo Request::post('title');
        $_POST['title'] = $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
        $_POST['description'] = $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
        
        
        return $fonds;
    }
}
?>