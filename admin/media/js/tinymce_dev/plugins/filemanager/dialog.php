<?php
// Отключаем кеширование
header("Cache-control: no-store,max-age=0");
header("Expires: " . date("r"));
// Тип кодировки
header("Content-type: text/html; charset=utf-8");
session_start();

include('config.php');
include('function.php');
//Директория менеджера
$manager_dir = strtr(getcwd().'/',array('\\'=>'/'));
$manager_dir = strtr($manager_dir,array($root=>''));

$_GET['type'] = isset($_GET['type'])?$_GET['type'] : NULL;

//Берем папку из сессии
if(!array_key_exists('folder',$_GET) AND isset($_SESSION['last_folder'][$_GET['type']])){
    $_GET['folder'] = $_SESSION['last_folder'][$_GET['type']];
}
//Создаем начальные переменные
extract(filter($_GET));

//Путь к корневой папке
$root .= $dir;

//Сменяем корневую директорию
chdir($root);
$file_dir = file_dir($folder,$dir,$manager_dir);
//Обратная ссылка
$backlink = NULL;
if($root != $root.$folder){
    $str = strpos(rtrim($folder,'/'),'/');
    $backlink = substr($folder,0,$str);
}
//Сохраняем для возврата
$_SESSION['last_folder'][$type] = $folder;
//Хлебные кроши
$breadcrumb = breadcrumb($folder);

//Определяем функцию
$function = type_function($type);

$refresh = "{$manager_dir}dialog.php?type={$type}&editor={$editor}&lang={$lang}&subfolder={$subfolder}&dir={$dir}&folder={$folder}";

$default_link = "{$manager_dir}dialog.php?type={$type}&editor={$editor}&lang={$lang}&subfolder={$subfolder}&dir={$dir}";
//var_dump($file_dir);
?>
<!Doctype HTML>
<html>
<head>
    <script type="text/javascript" src="js/jquery.1.9.1.min.js"></script>
    
    <script type="text/javascript" src="js_new/bootstrap.min.js"></script>
    <script type="text/javascript" src="js_new/bootstrap2-lightbox.min.js"></script>
    
    <script type="text/javascript" src="js_new/include.js"></script>
    
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap2-lightbox.min.css" rel="stylesheet" type="text/css" />
    
    <link href="css/style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        //Инициализируем
        $(document).ready(function(){
            manager.init();
        });
    </script>
</head>
<body class="no-touch">
    <input type="hidden" id="manager_dir" value="<?=$manager_dir?>" />
    <input type="hidden" id="root" value="<?=$root?>" />
    <input type="hidden" id="dir" value="<?=$dir?>" />
    <input type="hidden" id="default_link" value="<?=$default_link?>" />
    <div class="container-fluid">
        <div class="row-fluid">
            <ul class="breadcrumb">
                <li class="pull-left">
                    <a href="<?="{$default_link}&folder=/"?>">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="pull-left">
                    <span class="divider">/</span>
                </li>
                <?php
                if(isset($breadcrumb)):
                foreach($breadcrumb AS $key => $value):
                ?>
                <?php
                //Директория в которой находимся
                if($key == $folder):
                ?>
                <li class="active pull-left ellipsis" >
                    <span class="string"><?=$value['name']?></span>
                </li>
                <?php
                else:
                ?>
                <li class="pull-left ellipsis">
                    <a class="string" href="<?=$value['href']?>"><?=$value['name']?></a>
                </li>
                <?php
                endif;
                ?>
                <li class="pull-left">
                    <span class="divider">/</span>
                </li>
                <?php
                endforeach;
                endif;
                ?>
                <li>
                    <small class="hidden-phone">(<?=count($file_dir['file'])?> Files - <?=count($file_dir['dir'])?> Folders)</small>
                </li>
                <li class="pull-right">
                    <a id="refresh" class="btn-small" href="<?=$refresh?>">
                        <i class="icon-refresh"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="row-fluid ff-container">
            <div class="span12">
                <ul id="main-item-container" class="grid cs-style-2 list-view0">
                    <?php
                    //Обратная ссылка
                    if($backlink !== NULL):
                        $link = "{$default_link}&folder={$backlink}";
                    ?>
                    <li class="back ui-droppable">
                        <figure class="back-directory">
                            <a class="folder-link" href="<?=$link?>">
                                <div class="img-precontainer">
                                    <div class="img-container directory">
                                        <img class="directory-img" src="<?=$manager_dir?>/ico/folder_back.png" />
                                    </div>
                                </div>
                            </a>
                            <div class="box no-effect">
                                <h4 class="ellipsis">
                                    <a class="folder-link" href="<?=$link?>">Вернутся</a>
                                </h4>
                            </div>
                        </figure>
                    </li>
                    <?php 
                    endif;
                    ?>
                    
                    <?php 
                    //Директории
                    if(!empty($file_dir['dir'])):
                    foreach($file_dir['dir'] AS $key => $value):
                        $link = "{$default_link}&folder={$folder}{$value['file']}";
                    ?>
                    <li class="dir ui-draggable ui-droppable" data-name="<?=$value['name']?>">
                        <figure class="directory" data-type="<?=$value['rel']?>">
                            <a class="folder-link" href="<?=$link?>" data-folder="">
                                <div class="img-precontainer">
                                    <div class="img-container directory">
                                        <img class="directory-img" src="<?=$value['src']?>" />
                                    </div>
                                </div>
                            </a>
                            <div class="box">
                                <h4 class="ellipsis">
                                    <a class="folder-link" href="<?=$link?>"><?=$value['name']?></a>
                                </h4>
                            </div>
                            <figcaption>
                                <a class="rename-file" data-type="dir" data-name="<?=$value['name']?>" data-folder="<?=$folder?>" data-path="<?=$dir.$folder.$value['file']?>" data-dir="<?=$dir.$folder?>" data-charset="<?=$value['charset']?>" href="javascript:void('')">
                                    <i class="icon-pencil "></i>
                                </a>
                            </figcaption>
                        </figure>
                    </li>
                    <?php 
                    endforeach;
                    endif;
                    ?>
                    <?php 
                    //файлы
                    if(!empty($file_dir['file'])):
                    foreach($file_dir['file'] AS $key => $value):
                        $file_directory = "{$dir}{$folder}{$value['file']}";
                    ?>
                    <li class="file ui-draggable ui-droppable">
                        <figure class="file" cover="<?=$value['cover']?>" data-type="<?=$value['rel']?>" data-name="<?=$value['name']?>" data-function="<?=$function?>" data-file="<?=$value['file']?>" data-action="<?=$type?>" data-path="<?=$file_directory?>">
                            <div class="link" >
                                <div class="img-precontainer">
                                    <div class="filetype"><?=$value['ext']?></div>
                                    <div class="img-container">
                                        <img class="lazy-loaded" src="<?=$value['src']?>" />
                                    </div>
                                    <div class="cover"></div>
                                </div>
                            </div>
                            <div class="box">
                                <h4 class="ellipsis">
                                    <span class="link"><?=$value['name']?></span>
                                </h4>
                            </div>
                            <figcaption>
                                <a class="preview" data-toggle="lightbox" href="#previewLightbox" data-url="<?=$value['src']?>">
                                    <i class=" icon-eye-open"></i>
                                </a>
                                <a class="rename-file" data-type="file" data-name="<?=$value['name']?>" data-path="<?=$value['src']?>" data-charset="<?=$value['charset']?>" data-dir="<?=$dir.$folder?>" data-ext="<?=$value['ext']?>" href="javascript:void('')">
                                    <i class="icon-pencil "></i>
                                </a>
                            </figcaption>
                        </figure>
                    </li>
                    <?php 
                    endforeach;
                    endif;
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <!-- Блок для  Lightbox-->
    <div id="previewLightbox" class="lightbox hide fade" tabindex="-1" role="dialog" data-hidden="true" aria-hidden="true">
        <div class='lightbox-content'>
            <img id="img_lightbox" src="">
        </div>
    </div>
    
    <!--TESTS-->

    <div class="modal fade" id="rename" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div class="alert hide">
                <button type="button" class="close">x</button>
                <div class="massage">
                    <strong>Ошибка!</strong> Файл не переименован.
                </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title" id="exampleModalLabel">Переименование</h2>
          </div>
          <div class="modal-body">
              <div class="form-group">
                <input class="input-block-level" type="text" value="" autocomplete="off">
              </div>
          </div>
          <div class="modal-footer">
                <button class="btn null" href="javascript:;" data-dismiss="modal" aria-hidden="true" data-handler="0">Cancel</button>
                <button class="btn btn-primary start" data-loading-text="OK" href="javascript:;" data-handler="1">OK</button>
          </div>
        </div>
      </div>
    </div>
    
    <div id="result"></div>
    <script>
        $('#myButton').on('click', function () {
            var $btn = $(this).button('loading')
            // business logic...
            $btn.button('reset')
        })
    </script>
</body>
</html>


            