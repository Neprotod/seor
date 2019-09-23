<html>
<head>
    <title>Ошибка</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        #inline{
            color:blue;
        }
        .highlight{
            background:#7CED7C;
        }
    </style>
    <meta name="robots" content="noindex, nofollow" />
</head>
<body>
    <h3 id='information'>Тип ошибки: <?=(isset($code))? $code:'Исключение'?></h3>
    <hr />
    <p><b>Перехватил:</b> <?=$type?></p>
    <p><b>Сообщение:</b> <?=$message?></p>
    <hr />
    <h4 id='information'>Основная информация</h4>
    <p><b>В классе:</b> <?=$trace[0]['class']?></p>
    <p><b>В методе:</b> <?php
                            if($trace[0]['function'] == 'require')
                                echo 'Включаемый файл с помощью ' . $trace[0]['function'];
                            else
                                echo $trace[0]['function']
                        ?>
    </p>
    <hr />
    <p><b>В файле:</b> <?=$file?></p>
    <p><b>На линии:</b> <?=$line?></p>
    <?php echo Debug::source($file, $line) ?>

    <hr />
    <h3 id='information'>Пути до ошибки</h3>
    <p>Полный путь читается с конца до верха</p>
    <hr />
    <?php
    $true = FALSE;

    foreach($trace as $trac){
    ?>
    <?php
        if($true === TRUE):
    ?>
    <pre>
         |  |
        _|  |_
        \    /
         \  /
          \/
    </pre>
    <?php
        else:
            $true = TRUE;
        endif;
    ?>
    <p>Файл: <b><?=(isset($trac['file']))? $trac['file']: ''?></b></p>
    <p>Линия запуска: <b><?=(isset($trac['line']))? $trac['line'] : ''?></b></p>
    <p>Функция которую запустили: <b><?=(isset($trac['function']))? $trac['function'] : '' ?></b></p>
    <p>Класс запускаемой функции: <b><?=(isset($trac['class']))? $trac['class'] :'' ?></b></p>
    <?php
    }
    ?>
</body>
</html>