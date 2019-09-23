<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <div class="block loading">
        <?php
        if(isset($messages))
        foreach($messages AS $key => $value):
        ?>
        <div class="<?=$key?>">
            <?php
            foreach($value AS $message):
            ?>
            <?=$message?>
            <?php
            endforeach;
            ?>
            
        </div>
        <?php
        endforeach;
        ?>
    </div>
    <div class="block loading">
        <?php
        if(!empty($styles)):
        ?>
        <h2>Стили темы</h2>
        <div class="styles clear">
            <?php
            foreach($styles AS $key => $values):
            ?>
            <div class="box <?=$key?>">
                <h3>Стили: <?=($key == 'default')? 'По умолчанию' : (($key == 'general')? 'Общие' :$key) ?></h3>
                <?php
                foreach($values AS $style_type => $value):
                ?>
                <div class="<?=$style_type?>">
                    <h4><?=$style_type?></h4>
                    <?php
                    foreach($value AS $style):
                    $type = ($style['type'] == NULL)? 'общий' : $style['type'];
                    $style['type'] = ($style['type'] == NULL)? 'general' : $style['type'];
                    $module = $modules[$style['type']];
                    ?>
                    <a href="<?=Url::root()?>/<?=$module?>/<?=$style['type']?>/<?=$style['id_table']?>" class="style <?=$style['type']?>">
                        <div class="information">
                            <span class="type"><?=$type?></span>
                            <span class="name"><?=$style['id_table']?></span>
                            <div class="extends"><?=($style['extends'])? 'Расширяет post' : '' ?></div>
                        </div>
                    </a>
                    <?php
                    endforeach;
                    ?>
                </div>
                <?php
                endforeach;
                ?>
            </div>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        elseif($drop === FALSE):
        ?>
        <h2>Стиль не используется</h2>
        <input type="submit" value="Удалить">
        <?php
        endif;
        ?>
    </div>
</form>