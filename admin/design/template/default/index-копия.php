<!DOCTYPE html>
<html>
<head>
    <title><?=$meta_title?></title>
    <link href="<?=$root?>/css/common.css" type="text/css" rel="stylesheet">
    <link href="<?=$root?>/css/animate.css" type="text/css" rel="stylesheet">
    <script src="<?=$root?>/js/jquery/jquery.js" type="text/javascript"></script>
    <script src="<?=$root?>/js/menu.js" type="text/javascript"></script>
    <script src="<?=$root?>/js/loading.js" type="text/javascript"></script>
    <script src="<?=$root?>/js/frame.js" type="text/javascript"></script>
    <script src="<?=Url::root()?>/media/js/function.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            //Появление меню и шапки
            $("header, footer").addClass('animated fadeIn');
            
            /*Временно отключаем любую анимацию*/
            //loadind.init();
            
            //Запускаем анимацию меню
            menu.init();
            menu.on();
        });
    </script>
    <?php
    if(isset(Registry::i()->editor)):
    ?>
    <!-- Редактор кода -->
    <script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
    <script src="<?=Url::root()?>/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="<?=Url::root()?>/media/css/ace/css.css" />
    <link rel="stylesheet" type="text/css" href="<?=Url::root()?>/media/css/ace/javascript-clouds.css" />
    <script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/mode-css.js" type="text/javascript"></script>
    <script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/mode-javascript.js" type="text/javascript"></script>
    
    <!-- Редактор контента -->
    <?php 
        $editor =  ".".Registry::i()->root."/editor/tinymce.php";
        if(is_file($editor)){
            include "$editor";
        }
    ?>
    <?php
    endif;
    ?>
</head>
<body>
<div id="wrap" class="<?=(!empty($fonds['action']))? $fonds['action'] : $fonds['module']?>">
    <header class="box-sizing" id="header" class="clear">
        <div id="logo" class="clear box-sizing">
            <a href="<?=Url::i()->root()?>">WebDzen tree</a>
        </div>
        <div id="top_menu">
            <?php
            if(isset($menu)):
            echo $menu;
            endif;
            ?>
        </div>
    </header>
    <div id="main" class="clear">
        <h1 id="title" class=""><?=$title?></h1>
        <?=$content?>
    </div>
    <footer>
        <div id="nav_footer" class="">
            <div id="nav_footer_action" class="">
                <div class="image_footer">
                    <div class="image">
                        <img src="<?=$root?>/img/logo.png" width="30" alt="WebDzen" />
                    </div>
                    <div class="action"></div>
                </div>
            </div>
            <div id="nav_footer_wrap">
                <ul>
                    <li><a href="<?=Url::i()->root()?>/pages">Страницы</a></li>
                    <li class="small_nav"><a href="<?=Url::i()->root()?>/pages/page">+ страницу</a></li>
                    
                    <li><a href="<?=Url::i()->root()?>/categories">Категории</a></li>
                    <li class="small_nav"><a href="<?=Url::i()->root()?>/categories/category">+ категорию</a></li>
                    
                    <li><a href="<?=Url::i()->root()?>/posts">Посты</a></li>
                    <li class="small_nav"><a href="<?=Url::i()->root()?>/posts/post">+ пост</a></li>
                    <li><a href="<?=Url::i()->root()?>/templates">Темы</a></li>
                    <li><a href="<?=Url::i()->root()?>/template">Настройки</a></li>
                </ul>
                <div class="footer">
                    <div class="box">
                        <span class="company">WebDzen®</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>