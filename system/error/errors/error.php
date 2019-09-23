<?php
$cheak =  explode('/',$_SERVER["REQUEST_URI"]);
if(empty($cheak[0]))
    array_shift($cheak);
$root =  getcwd();
chdir("../../../");
if(reset($cheak) != $_SERVER['HTTP_HOST'] AND ($_SERVER['HTTP_TYPE'] != 'socket' AND $_SERVER['HTTP_INIT'] != '0000'))
    include "index.php";
    
// Подключаем классы необходимые для работы
include $root."/classes/Arr.php";
include $root."/classes/Url.php";
include $root."/classes/xml.php";
include $root."/classes/Registry.php";
include $root."/classes/Str.php";
Registry::i()->root = $root;
include $root."/classes/router.php";
$router = new Router();
include $root."/classes/filemanager.php";
$filemanager = new Filemanager();
ob_start();
$filemanager->fetch();
$content = ob_get_clean();
include $root."/view/content.php";

?>