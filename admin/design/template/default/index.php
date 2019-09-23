<!DOCTYPE html>
<html>
    <head>
        <title><?=$meta_title?></title>
        <link href="/media/css/bootstrap/bootstrap-reboot.css" type="text/css" rel="stylesheet">
        <link href="/media/css/bootstrap/bootstrap.css" type="text/css" rel="stylesheet">
        <link href="<?=$root?>/css/common.css" type="text/css" rel="stylesheet">
        <link href="<?=$root?>/css/animate.css" type="text/css" rel="stylesheet">
        <link href="<?=$root?>/css/error.css" type="text/css" rel="stylesheet">
        
        <link href="/media/soft/prism/prism.css" type="text/css" rel="stylesheet">
        
        <script type="text/javascript" src="/media/js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/media/js/bootstrap/bootstrap.bundle.js"></script>
        
        <script src="/media/soft/prism/prism.js" type="text/javascript"></script>
        
        <script src="<?=$root?>/js/menu.js" type="text/javascript"></script>
        <script src="<?=$root?>/js/loading.js" type="text/javascript"></script>
        <script src="<?=$root?>/js/frame.js" type="text/javascript"></script>
        <script src="<?=$root?>/js/error.js" type="text/javascript"></script>
        <script src="<?=$root?>/js/preload.js" type="text/javascript"></script>
        <script src="<?=Url::root()?>/media/js/function.js" type="text/javascript"></script>
        <script src="<?=Url::root()?>/media/js/help.js" type="text/javascript"></script>
        <script type="text/javascript" src="<?=$root?>/js/additional.js"></script>
        <?=Admin_Module::factory("ajax", TRUE)->script()?>
        <script>
            ajax.init('<?=Session::i()->id()?>');
        </script>
    </head>
    <body>
        <div id="wrap" class="container-fluid">
            <div id="top_menu" class="row shadow">
                <a id="company" class="col-2 fix-weight bg-dark text-white" href="<?=Url::root()?>">
                    <div class="table_box">
                        <div class="align-middle">
                            <span>Three FrameWork</span>
                            <span id="webdzen">WebDzen®</span>
                        </div>
                    </div>
                </a>
                <div id="title" class="col bg-grey">
                    <div class="table_box">
                        <div class="align-middle">
                            <div id="help_line"></div>
                        </div>
                    </div>
                </div>
                <a id="logout" class="px-2 text-center" href="<?=Url::root()?>/auth/logout">
                    <div class="table_box">
                        <div class="align-middle">Выйти</div>
                    </div>
                </a>
            </div>
            <div id="main" class="container-fluid">
                <div class="row flex-nowrap">
                    <div id="sidebar" class="col-2 fix-weight bg-light">
                        <ul class="menu_box">
                            <?=$menu?>
                        </ul>
                    </div>
                    <div id="spacing" class="col-2 fix-weight"></div>
                    <div id="content_box" class="col">
                        <div class="content_wrap">
                            <h2><?=$title?></h2>
                            <?=$errors?>
                            <div id="content">
                                <?=$content?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            help.init();
            errors.init();
        </script>
    </body>
</html>