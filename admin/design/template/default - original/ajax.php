<!DOCTYPE html>
<html>
<head>
    <link href="<?=$root?>/css/common.css" type="text/css" rel="stylesheet">
    <link href="<?=$root?>/css/ajax.css" type="text/css" rel="stylesheet">
    <link href="<?=$root?>/css/animate.css" type="text/css" rel="stylesheet">
    <script src="<?=$root?>/js/jquery/jquery.js" type="text/javascript"></script>
    <script src="<?=$root?>/js/loading.js" type="text/javascript"></script>
    <script src="<?=Url::root()?>/media/js/function.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
        });
    </script>
</head>
<body>
<div id="wrap" class="<?=(!empty($fonds['action']))? $fonds['action'] : $fonds['module']?>">
    <div id="main">
        <div id="drop">X</div>
        <?=$content?>
    </div>
</div>
</body>
</html>