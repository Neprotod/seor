<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");    
include 'bootstrap.php';

$url = Request::post('url');
$id = Request::post('id');

if(!empty($url) AND empty($id)){
    $url = explode('/',$url);
    $id = array_pop($url);
}
if(empty($id)){
    return FALSE;
}

$style = Admin_Model::factory('style','system');

print json_encode($style->get_style($id));
