<div style="max-width:400px; overflow:hidden; margin:10px auto; background:#fff; font-family: 'PT Sans', sans-serif; position:relative; border-radius:3px; box-shadow: 0 0 4px 0 rgba(0,0,0,.08), 0 2px 4px 0 rgba(0,0,0,.12); border:1px solid #ebebeb;">
     <style>
        @keyframes rainbow {
            0% {
                transform:translateX(0%)
            }
            100%{
                transform:translateX(-50%)
            }
        }
    </style>
    <a href="<?=$site?>" style="display: block;width: 142px;height: 131px;background: url('http://seor.ua/application/template/default/img/SEOR_logo_14v_142.png'); top:20px; margin:10px auto; "></a>
    <div style="padding:20px;">
        <h2 style="margin:0;padding:10px 10px 20px 10px;text-align: center;font-size: 28px;font-weight: 400;font-family: 'PT Sans', sans-serif;">Спасибо за регистрацию</h2>
        <div style="padding:10px 0;">
            <b>Ваш логин:</b> <span><?=$email?></span>
        </div>
        <div style="padding:10px 0;">
            <b>Ваш пароль:</b> <span><?=$password?></span>
        </div>
        <div style="text-align:center;">
            <a class="btn" href="<?=$site?>/registr?active=<?=$key?>" style="display:inline-block; margin:auto; padding:10px; background:#81c757; text-decoration:none; color:#f5f5f5; border-radius:5px; margin-top:10px;">Авторизоваться</a>
        </div>
    </div>
    <div style="font-size: 16px; font-family: 'Roboto', sans-serif; width: 240%;position: relative;left:0; animation: rainbow 5s infinite linear">
        <div style="width: 50%;height: 6px;background: linear-gradient(to right, #2132b4 0%, ForestGreen 25%, Purple 50%, gold 75%, #2132b4 100%);float: left;"></div>
        <div style="width: 50%;height: 6px;background: linear-gradient(to right, #2132b4 0%, ForestGreen 25%, Purple 50%, gold 75%, #2132b4 100%);float: left;"></div>
    </div>
    <div style="padding:20px; background:#f7f7f7; font-size:14px; color:#666; text-align:center;">
        <a href="<?=$site?>/support" style="padding:0px 0; text-decoration:underline; color:inherit;">
            Связаться с тех поддержкой
        </a>
    </div>
</div>