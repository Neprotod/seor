<!DOCTYPE HTML>
<html>
<head>
<title>Вход</title>
<style type="text/css">
form{
    width:220px;
}
input[type="text"],input[type="password"]{
    float:right;
    width:150px;
}
label{
    float:left;
}
.clear{
    clear:both;
    height:5px;
}
</style>
</head>
<body>
    <h1>Зайдите в систему</h1>
    <p>
        <?=$msg?>
    </p>
    <form method="post" enctype="application/x-www-form-urlencoded">
        <label>Логин</label><input type="text" name="login" value="<?=$login?>" /> <br/>
        <div  class="clear"></div>
        <label>Пароль</label><input type="password" name="pass" /><br/>
        <div class="clear"></div>
        <input type="submit" name="enter" value="Войти" />
    </form>
</body>
</html>