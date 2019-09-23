<?php

function filter(array $array){
    if(!array_key_exists('folder',$array))
        $array['folder'] = '';
    
    if(!array_key_exists('type',$array))
        $array['type'] = NULL;
    
    foreach($array AS $key => &$value){
        switch($key){
            case 'folder': $value = ($value = trim($value,'/'))? $value.'/': ''; break; 
            case 'dir' : $value = ($value = trim($value,'/'))? '/'.$value.'/' :'/'; break; 
        }
    }
    return $array;
}

function charset($string){
    $string = (string)$string;
    if(!preg_match('/(.)/u',$string,$char_map)){
        $charset = 'ASCII';
    }else{
        $charset = 'UTF-8';
    }
    
    return $charset;
}
function lang_convert($name){
    if(charset($name) == 'ASCII'){
        $name = iconv('cp1251','UTF-8',$name);
    }
    return $name;
}

function file_dir($folder,$dir,$manager_dir){
    
    global $ext_img, $ext_video, $ext_music, $ext_archive, $ext_file, $ext, $root,$type;
    
    $scandir = scandir('./'.$folder);
    $fonds = array();
    $fonds['dir'] = array();
    $fonds['file'] = array();
    foreach($scandir AS $element){
        if($element == '.' OR $element == '..'){
            continue;
        }
        
        $charset = charset($element);
        $element = lang_convert($element);
        
        if(is_dir($folder.$element)){
            $fonds['dir'][$dir.$folder.$element]['rel'] = 'folder';
            $fonds['dir'][$dir.$folder.$element]['src'] = $manager_dir.'ico/folder.png';
            $fonds['dir'][$dir.$folder.$element]['name'] = $element;
            $fonds['dir'][$dir.$folder.$element]['file'] = $element;
            $fonds['dir'][$dir.$folder.$element]['charset'] = $charset;
        }else{
            $pathinfo = pathinfo($element);
            $pathinfo['extension'] = strtolower($pathinfo['extension']);
            $fond = array();
            //���������� �������� �� ���
            if(in_array($pathinfo['extension'],$ext_img) AND ($type < 2 OR $type == 5)){
                $fond['src'] = $dir.$folder.$element;
                $fond['rel'] = 'image';
                $fond['cover'] = 'no';
            }else{
                //���� ����� ���������� ������ �����������
                if($type == 1 OR $type == 5){
                    continue;
                }
                
                $fond['cover'] = 'yes';
                //��� �����?
                if(in_array($pathinfo['extension'],$ext_video)){
                    $fond['rel'] = 'video';
                }
                //��� ������?
                elseif(in_array($pathinfo['extension'],$ext_music)){
                    $fond['rel'] = 'music';
                }
                elseif($type > 2 AND $type < 5){
                    continue;
                }
                //��� �����?
                elseif(in_array($pathinfo['extension'],$ext_archive)){
                    $fond['rel'] = 'archive';
                }
                //��� ����?
                elseif(in_array($pathinfo['extension'],$ext_file)){
                    $fond['rel'] = 'file';
                }
                else{
                    $fond['rel'] = 'file';
                    $fond['cover'] = 'no';
                }
                //���������� ����
                $fond['src'] = $manager_dir.'ico/'.$pathinfo['extension'].'.jpg';
            }
            
            $fond['name'] = $pathinfo['filename'];
            $fond['ext'] = $pathinfo['extension'];
            $fond['file'] = $element;
            $fond['charset'] = $charset;
            
            $fonds['file'][$dir.$folder.$element] = $fond;
        }
    }
    //var_dump($fonds);
    return $fonds;
}

//������� ������
function breadcrumb($folder){
    
    global $manager_dir, $type, $editor, $lang, $subfolder, $dir;
    
    $fonds = array();
    $folder = rtrim($folder,'/');
    
    $folders = explode('/',$folder);
    
    //������ ��� �������� ������
    $explode = $folders;
    //������ ���� � �������� �������
    $folders = array_reverse($folders);
    if(!empty($folders))
        foreach($folders AS $value){
            if(empty($value))
                continue;
            //������� ������
            $key = implode('/',$explode).'/';
            $fonds[$key]['name'] = $value;
            $fonds[$key]['href'] = "{$manager_dir}dialog.php?type={$type}&editor={$editor}&lang={$lang}&subfolder={$subfolder}&dir={$dir}&folder={$key}";
            //������� ��������� ������ ��� ������������ ������
            array_pop($explode);
        }

    return array_reverse($fonds);
}

function type_function($type){
    $function = NULL;
    switch($type){
        case NULL: $function = 'apply'; break;
        case 1: $function = 'apply_image'; break;
        case 3: $function = 'apply_video'; break;
        case 4: $function = 'apply_video'; break;
        case 5: $function = 'apply_image'; break;
    }
    return $function;
}