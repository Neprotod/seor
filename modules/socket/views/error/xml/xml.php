<style type="text/css">
.highlight {
    background: none repeat scroll 0 0 #7CED7C;
}
.line_separator{
    width:50%;
    margin-left:0%;
    border-bottom:1px solid;
}
#more_attribute{
    margin-left:30px;
    padding:5px;
    border:1px solid;
    display:table;
    background:#ff9;
}
#more_attribute p{
    margin:5px;
    border-bottom:1px solid;
}
</style>
<div id="navigation">
    <h3>Навигация</h3>
    <a class="link" href="/<?=Registry::i()->host?>">К статусу</a>
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
                <?php
                    if(isset($error['more_attribute'])){
                ?>
                <p><b>Дополнительные атрибуты:</b></p>
                <div id="more_attribute">
                    <?php
                        foreach($error['more_attribute'] as $name => $value){
                            echo "<p><b>$name:</b> <span>$value</span></p>";
                        }
                    ?>
                </div>
                <?php
                    }
                ?>
                <p><b>Код ошибки:</b> <?=$error['code']?></p>
                <p><b>Сообщение:</b> <?=$error['message']?></p>
                <div class="line_separator"></div>
                
                <p><b>Класс:</b> <?=$error['class']?></p>
                <p><b>Дата ошибки:</b> <?=$error['date']?></p>
                <div class="line_separator"></div>
                
                <p><b>Файл:</b> <?=$error['file']?></p>
                <p><b>На линии:</b> <?=$error['line']?></p>
                <p><?=$error['debug']?></p>
                <p style="border:1px solid #000; padding:5px;">Полный путь читается с конца до верха</p>
                <?php
                $true = FALSE;
                if(!empty($error['trace'])):
                    $report = error_reporting();
                    error_reporting(0);
                    $trace = Arr::is_serialized($error['trace']); 
                    error_reporting($report);
                    
                    foreach((array)$trace as $trac):
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