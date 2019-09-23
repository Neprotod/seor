<?php
    function correlate($fonds,$margin = 0,$action = NULL){
        
    ?>
        <div class="box <?=$action?>" style="margin-left:<?=$margin?>px;">
            <?php
            foreach($fonds as $name => $value):
                if(is_array($value)):
                    if($name == 'Добавлено'){
                        $action = 'create';
                    }elseif($name == 'Удалено'){
                        $action = 'drop';
                    }else{
                        $action = 'NULL';
                    }
                    if(Str::charset($name) == 'ASCII')
                        $name = iconv('cp1251','UTF-8',$name);
            ?>
                <p class="<?=($margin==0)?"first_name ":''?>"><span class="string_name"><?=$name?></span></p>
                <?=correlate($value,$margin+20,$action)?>
            <?php
                else:
                if(Str::charset($value) == 'ASCII')
                        $value = iconv('cp1251','UTF-8',$value);
            ?>    
                <p class="string_value"><span class="scring"><?=$value?></span></p>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    <?php
    }
?>
<div id="main_correlate">
    <div id="navigation">
        <h3>Навигация</h3>
        <a href="/<?=Registry::i()->class_link?>/directory">Назад</a>
        <br />
    </div>
    <div id="massage">
        <?=Request::param(Registry::i()->warning)?>
        <?=Request::param(Registry::i()->massage)?>
    </div>
    <br />
    <div id="correlate_controller">
        <form method="post">
            <input name="update" type="submit" value="Обновить файл" />
        </form>
    </div>
    <div id="file_correlate">
    <?php
    if(!empty($fonds)):
    ?>
        <h3 class="error_correlate">Файлы были изменены:</h3>
        <?=correlate($fonds)?>
    <?php
    else:
    ?>
        <h3 class="place_correlate">Файлы не изменены:</h3>
    <?php
    endif;
    ?>
    </div>
</div>