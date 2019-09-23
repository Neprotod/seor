<!DOCTYPE HTML>
<html>
<head>
    <style type="text/css">
        #navigation{
            margin:20px;
        }
    </style>
    <script type="text/javascript" src="/media/js/jquery/jquerymin.js"></script>
    
    <link type="text/css" rel="stylesheet" href="/media/css/common.css" />
    
    <script type="text/javascript">
        
    </script>
<head>
<body>
<div id="main">
    <div id="reset">
        <a class="link" href="/<?=Url::instance()?>">Обновить</a>
        <h3 class="host_name"><?=$_SERVER['HTTP_HOST']?></h3>
    </div>
    <?=$content?>
</div>
</body>
</html>