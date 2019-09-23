<div class="styles clear">
    <?php
    if(!empty($styles)):
    foreach($styles AS $key => $values):
    ?>
    <div class="box <?=$key?>">
        <h3>Стили: <?=($key == 'default')? 'По умолчанию' : (($key == NULL)? 'Общие' :$key) ?></h3>
        <?php
        if($key != 'default'):
        ?>
        <a href="<?=Url::root()?>/templates/style/create/<?=$template_id?><?=($key)? "/{$key}" : ''?>">Добавить</a>
        <?php
        endif;
        ?>
        <?php
        if($values):
        foreach($values AS $style_type => $value):
        ?>
        <div class="<?=$style_type?>">
            <h4><?=$style_type?></h4>
            <?php
            foreach($value AS $style):
            $type = ($style['type'] == NULL)? 'общий' : $style['type'];
            $style['type'] = ($style['type'] == NULL)? 'general' : $style['type'];
            ?>
            <a href="<?=Url::root()?>/templates/style/get/<?=$style['id']?>" class="style <?=$style['type']?>">
                <div class="information">
                    <span class="type"><?=$type?></span>
                    <span class="name"><?=$style['name']?></span>
                    <?php
                    if(isset($style['path'])):
                    ?>
                    <span class="path"><?=$style['path']?></span>
                    <?php
                    endif;
                    ?>
                    <?php
                    if(isset($style['ext'])):
                    ?>
                    <span class="ext"><?=$style['ext']?></span>
                    <?php
                    endif;
                    ?>
                    <?php
                    if(isset($style['title'])):
                    ?>
                    <div class="split">
                        <span class="string"><?=$style['title']?></span>
                    </div>
                    <?php
                    endif;
                    ?>
                </div>
                <?php
                if(isset($style['description'])):
                ?>
                <div class="description">
                    <div class="hr"></div>
                    <span class="string"><?=$style['description']?></span>
                </div>
                <?php
                endif;
                ?>
            </a>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        endforeach;
        else:
        ?>
        <div>
            Нет стилей
        </div>
        <?php
        endif;
        ?>
    </div>
    <?php
    endforeach;
    else:
    ?>
    Нет стилей
    <?php
    endif;
    ?>
</div>