<?php

/*
 * ������ ��� ����������� ���������
 */
class Model_Get_Templates_Admin{
    
    
    function __construct(){
        $this->method = Admin_Controller::factory('method','templates');
        $this->type = Admin_Model::factory('type','system');
        $this->style = Admin_Model::factory('style','system');
        $this->error = Admin_Model::factory('error','system');
    }
    
    function fetch($id){
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
        
        $fonds = array();
        $fonds['template'] = $template;
        $fonds['date'] = $date;
        
        return $fonds;
    }
}
?>