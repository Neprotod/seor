<?php

    /*
     * ����� ������ ��������� �����
     *
     * @param int id �����
     */
    function style($id){
        
        //��������� �����
        $style = $this->style->get_style($id);
        $content = '';
        
        if(Request::method('post')){
            
            //��������� � POST ��� �� ���������� ��� ���������� ���
            $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
            
            //�������������� ����
            $_POST['title'] = $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
            $_POST['description'] = $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
            // ` - ��� �� ������ ������ ��� �������
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
                        $this->error->set('����� ��� ����� ��� ����������, �������������� ������');
                    }else{
                        $this->style->style_update($id,$fonds);
                        $new_style = $this->style->get_style($id,TRUE);
                        if(rename($style['path'],$new_style['path'])){
                            $this->error->set('���� ������������',TRUE);
                        }
                        $style = $new_style;
                    }
                    
                    $this->error->set('���� ��������',TRUE);
                }else{
                    $this->error->set('������ ���������� �����');
                }
            }
        }
        
        
        if(!empty($style)){
            $content = (!empty($style['path']))? File::get_content($style['path'],TRUE) : NULL;
            if($content === FALSE){
                $this->error->set('����� �� ����������');
            }
        }else{
            $this->error->set('����� �� ����������');
        }
        
        $template = $this->method->get_template($style['template']);

        //������ ����
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        Registry::i()->fonds['action'] = 'template template_file '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['file'] = $style;
        $date['content'] = $content;
        $date['messages'] = $this->error->get();
        
        //��������� ����
        return Admin_Template::factory(Registry::i()->template,'content_templates_file',$date);
    }
    /*
     * ����� ���� ������
     *
     * @param int id ����
     */
    function styles($id){
        
        //��������� ����
        $template = $this->method->get_template(intval($id));
        
        //����� ��� ���� ������
        $type_list = $this->type->type_list();
        foreach($type_list AS $key => $value){
            $type_list[$key] = array();
        }
        
        $styles = $this->style->get_styles($template['name']);

        //������� ������ ��� ��� ������ ��� ������ �� ���������
        $styles = $this->style->style_sort($styles,FALSE);
        
        //���������� ��� ������� �������
        $styles = Arr::merge($styles,$type_list);

        //������ ����
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $id,
                    );
        
        //��������� � ���� ��������� 
        Registry::i()->title = '����� ����: '.$template['name'];
        Registry::i()->meta_title = '�����';
        
        //��������� �����
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['template'] = $template;
        $date['styles'] = $styles;
        $date['messages'] = $this->error->get();
        
        //��������� ����
        return Admin_Template::factory(Registry::i()->template,'content_templates_styles',$date);
    }
    
    /*
     * �������� �����
     *
     * @param int id ����
     * @param string ��� ������
     * @param string ��� �����
     */
    function create_style($id,$type = NULL,$style_type = NULL){
        
        //��������� ����
        $template = $this->method->get_template(intval($id));
        
        if(Request::method('post')){
            $fonds = array();
            
            //�������� ������
            $fonds['id_template'] = $template['id'];
            //���������� ������������� � ���������� ��� ������ � select
            $fonds['id_type'] = $type = Request::param(Request::post('type_id'),NULL,NULL);
            $fonds['style_type'] = $style_type = Request::post('style_type');
            
            //��������� � POST ��� �� ���������� ��� ���������� ���
            $_POST['name'] = $fonds['name'] = Translit::cyrillicy(Request::post('name','strip'));
            
            //�������������� ����
            $fonds['title'] = Request::param(Request::post('title'),'strip',NULL);
            $fonds['description'] =  Request::param(Request::post('description'),'strip',NULL);
            
            // ` - ��� �� ������ ������ ��� �������
            $fonds['`default`'] =  Request::param(Request::post('default'),NULL,0);

            if(empty($fonds['name'])){
                //���� ��� ����� ������� ������
                $this->error->set("������ ��� �����");
            }
            elseif(!$this->style->get_style($fonds)){
                //���������� � ���� ������
                $result = $this->style->style_set($fonds);
                
                //����� �����, ��� �������� ����
                $get_style = $this->style->get_style($result[0],TRUE);

                if(File::create_file($get_style['path'])){
                    header("Location: ".Url::root()."/templates/style/{$result[0]}".Url::query(array('create'=>1),'auto'));
                }else{
                    //������� �� ���� ������, ���� ���� �� ��������.
                    $this->style->drop_style($result[0]);
                    $this->error->set("���� �� ��������, ���������� � �������������");
                }
            }else{
                $this->error->set("����� ��� ����� ��� ����������");
            }
        }
        
        //����� ��� ���� ������
        $type_list = $this->type->type_list();
        //��������� ������ ������ ���������
        foreach($type_list AS $key => $value){
            $type_list[$key]['exist'] = FALSE;
        }
        
        //��������� ����� �����
        Arr::unshift($type_list, NULL,array('exist'=> FALSE));
        
        //��������� � ������ ���� ���������
        if(array_key_exists($type,$type_list))
            $type_list[$type]['exist'] = TRUE;
        else
            $type_list[NULL]['exist'] = TRUE;

        //����� ��� ���� �����
        $style_list = $this->style->style_list();

        //������ ����
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $id,
                    );
        
        //��������� � ���� ��������� 
        Registry::i()->title = '������� ����� � ����: '.$template['name'];
        Registry::i()->meta_title = '�������� �����';
        
        //��������� �����
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['template'] = $template;
        $date['type_list'] = $type_list;
        $date['style_list'] = $style_list;
        $date['style_type'] = $style_type;
        $date['type_id'] = $type;
        $date['messages'] = $this->error->get();
        
        //��������� ����
        return Admin_Template::factory(Registry::i()->template,'content_templates_create_style',$date);
    }
    /*
     * �������� �����
     *
     * @param int id ����
     * @param string ��� ������
     * @param string ��� �����
     */
    function drop_style($template_id,$id = NULL){
        
        if($id === NULL){
            $id = $template_id;
            unset($template_id);
        }
        //������� ������, ������ ��� �����������
        $date = array();
        
        $style = $this->style->get_style($id);

        //��������� ����
        if(isset($style['template'])){
            $template = $this->method->get_template($style['template']);
        }elseif(isset($template_id)){
            $template = $this->method->get_template(intval($template_id));
        }
        if(!empty($style)){
            
            $style_contents = '';
            if($style_contents = $this->style->style_contents(array("id_style"=>$id,'status'=>1))){
                //������� ������ ��� ��� ������ ��� ������ �� ���������
                $style_contents = $this->style->style_sort($style_contents,FALSE);
                
                $this->error->set('������ ������� �����, ��� ��������� ��������� �������');
            }else{
                if($this->style->drop_style($id)){
                    File::unlink($style['path']);
                    $this->error->set('����� ������',TRUE);
                }else{
                    $this->error->set('����� �� ������');
                }
            }
            //��������� ������ �� �����
            $date['styles'] = $style_contents;
        }else{
            $this->error->set('����� �� ����������');
        }

        //������ ����
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        //��������� � ���� ��������� 
        Registry::i()->title = '�������� ����� ����: '.$template['name'];
        Registry::i()->meta_title = '�������� �����';
        
        //��������� �����
        Registry::i()->fonds['action'] = 'template '.Registry::i()->fonds['action'];
        
        //���������� ��� �������
        $modules = array(
                'page' => 'pages',
                'post' => 'posts',
                'category' => 'categories',
            );
        
        $date['template'] = $template;
        $date['modules'] = $modules;
        $date['messages'] = $this->error->get();
        
        //��������� ����
        return Admin_Template::factory(Registry::i()->template,'content_templates_drop_style',$date);
    }
    
    
    /*
     * ����� ������ ���� ������
     *
     * @param int id �����
     */
    function type($type,$id_template,$types = NULL){
        
        if(Request::method('post')){
            if($content = Request::post('file')){
                $type = $this->type->get_type($id);
                if(File::set_content($type['path'],$content,TRUE))
                    $this->error->set('���� ��������',TRUE);
                else
                    $this->error->set('������ ���������� �����');
            }
        }
        
        //��������� �����
        $type = $this->type->get_type($id);
        $content = '';

        if(!empty($type)){
            $content = (!empty($type['path']))? File::get_content($type['path'],TRUE) : FALSE;
            if($content === FALSE){
                $this->error->set('����� �� ����������');
            }
        }else{
            $this->error->set('���� �� ����������');
        }
        
        $template = $this->method->get_template($type['template']);
        
        //������ ����
        Registry::i()->menu = array(
                        'menu' => 'template',
                        'id' => $template['id'],
                    );
        
        Registry::i()->fonds['action'] = 'template template_file '.Registry::i()->fonds['action'];
        
        $date = array();
        $date['file'] = $type;
        $date['content'] = $content;
        $date['messages'] = $this->error->get();
        
        //��������� ����
        return Admin_Template::factory(Registry::i()->template,'content_templates_file',$date);
    }
?>