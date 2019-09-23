<!DOCTYPE HTML>
<html>
    <head>
        <title>404</title>
        <style type="text/css">
            body{
                background:url('<?=$viewRoot?>/eror404.jpg') center no-repeat;
                width:100%;
                height:100%;
                margin:0;
                padding:0;
            }
            html{
                width:100%;
                height:100%;
            }
            #error{
                position:absolute;
                top:50%;
                margin-top:-10px;
                left:50%;
                margin-left:50px;
            }
        </style>
    </head>
    <body>
        <?=$viewRoot?>
        <div id="error">
            <p>А тут поиск: <form action="/catalog" method="GET" ><input type="text" name="keyword" /><input type="submit" value="Найти" /></p>
            <a href="/">Главная</a>
        </div>
        <!-- Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE  Идиотизм для IE-->
    </body>
</html>