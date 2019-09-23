<?php

function filter(array $array){
    if(!array_key_exists('folder',$array))
        $array['folder'] = '';
    foreach($array AS $key => &$value){
        switch($key){
            case 'folder': $value = ($value = trim($value,'/'))? $value.'/': ''; break; 
            case 'dir' : $value = ($value = trim($value,'/'))? '/'.$value.'/' :'/'; break; 
        }
    }
    return $array;
}

function file_dir($folder,$manager_dir){
    $config = new Config();
    
    $scandir = scandir('./'.$folder);
    $fonds = array();
    foreach($scandir AS $element){
        if($element == '.' OR $element == '..'){
            continue;
        }
        if(is_dir($folder.$element)){
            $fonds['dir'][$folder.$element] = $manager_dir.'ico/folder.png';
        }else{
            $pathinfo = pathinfo($element);
            $pathinfo['extension'] = strtolower($pathinfo['extension']);
            var_dump($pathinfo);
            $fond = array();
            //Определяем картинка ли это
            if(array_key_exists($pathinfo['extension'],$config->ext_img)){
                //fsf
            }else{
                //Это видео?
                if(array_key_exists($pathinfo['extension'],$config->ext_video)){
                    $fond['rel'] = 'video';
                }
                //Это музыка?
                elseif(array_key_exists($pathinfo['extension'],$config->ext_music)){
                    $fond['rel'] = 'music';
                }
                //Это архив?
                elseif(array_key_exists($pathinfo['extension'],$config->ext_archive)){
                    $fond['rel'] = 'archive';
                }
                //Это файл?
                elseif(array_key_exists($pathinfo['extension'],$config->ext_archive)){
                    $fond['rel'] = 'file';
                }
                else{
                    $fond['rel'] = 'file';
                }
                //Подключаем файл
                $fond['src'] = $manager_dir.'ico/'.strtoupper($pathinfo['extension']).'.png';
                if(is_file($fond['src'])){
                    echo 1;
                }
            }
            //$fonds['file'][$element] = $manager_dir.'ico/folder.png';
        }
    }
    
    return $fonds;
}