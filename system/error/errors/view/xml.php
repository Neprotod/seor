<div id="navigation">
    <h3>Навигация</h3>
    <a href="/<?=Registry::i()->class_link?>">Вернутся</a>
    <br />
</div>
<div>
    <p>Количество ошибок: <?=count($errors)?></p>
</div>
<table id="error_table">
    <?php
    if(!empty($errors))
        foreach($errors as $id => $error):
    ?>
    <tr>
        <td>
            <div class="box">
                <form method="post" class="form">
                    <input type="submit" value="Удалить" />
                    <input type="hidden" name="drop" value="<?=$id?>" />
                </form>
                <p><b>Тип:</b> <?=$error['type']?></p>
                <p><b>Код ошибка:</b> <?=$error['code']?></p>
                <p><b>Сообщение:</b> <?=$error['message']?></p>
                <p><b>Файл:</b> <?=$error['file']?></p>
                <p><b>На линии:</b> <?=$error['line']?></p>
                <p><b>Класс:</b> <?=$error['class']?></p>
                <p><?=$error['debug']?></p>
                <p style="border:1px solid #000;">Полный путь читается с конца до верха</p>
                <?php
                $true = FALSE;
                if(!empty($error['trace'])):
                    $trace = unserialize($error['trace']);
                    foreach($trace as $trac):
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
                <p>Класс запускаемой функции: <b><?=(isset($trac['class']))? $trac['function'] :'' ?></b></p>
                <?php
                    endforeach;
                endif;
                
                ?>
            </div>
            <hr />
        </td>
    </tr>
    <?php
        endforeach;
    ?>
</table>