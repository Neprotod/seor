<!DOCTYPE HTML>
<html>
    <head>
        <title>Вход</title>
        <link href="/media/css/bootstrap/bootstrap-reboot.css" type="text/css" rel="stylesheet">
        <link href="/media/css/bootstrap/bootstrap.css" type="text/css" rel="stylesheet">
        <link href="<?=$root?>css/login.css" type="text/css" rel="stylesheet">
        <?=$error->header(FALSE)?>
        <script type="text/javascript" src="/media/js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/media/js/bootstrap/bootstrap.js"></script>
    </head>
    <body>
        <div id="login" class="position-absolute">
            <div class="card m-auto">
                <?=$error->output()?>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group control" active-role="login">
                            <label>Login</label>
                            <input name="login" type="text" class="form-control" placeholder="Login" value="<?=Request::post('login','string')?>" />
                        </div>
                        <div class="form-group control" active-role="pass">
                            <label>Password</label>
                            <input name="pass" type="password" class="form-control" placeholder="Password" />
                        </div>
                        <div class="form-group form-check pointer" id="check">
                            <input name="holdme" type="checkbox" class="form-check-input" />
                            <label class="form-check-label" for="check">Запомнить меня</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Зайти</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <script>
        errors.init();
    </script>
</html>