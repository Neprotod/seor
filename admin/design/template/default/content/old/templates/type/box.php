<div class="types clear">
    <?php
    if(!empty($types)):
    foreach($types AS $key => $values):
    ?>
    <div class="box">
        <span class="string">Тип данных: <b><?=$key?></b></span>
        <?php
        foreach($values AS $type):
        ?>
        <a href="<?=Url::root()?>/templates/type/get/<?=$type['id']?>" class="type <?=$key?>">
            <div class="information">
                <span class="name"><?=$type['name']?></span>
                <?php
                if(isset($type['path'])):
                ?>
                <span class="path"><?=$type['path']?></span>
                <?php
                endif;
                ?>
                <?php
                if(isset($type['ext'])):
                ?>
                <span class="ext"><?=$type['ext']?></span>
                <?php
                endif;
                ?>
                <?php
                if(isset($type['title'])):
                ?>
                <div class="split">
                    <span class="string"><?=$type['title']?></span>
                </div>
                <?php
                endif;
                ?>
            </div>
            <?php
            if(isset($type['description'])):
            ?>
            <div class="description">
                <div class="hr"></div>
                <span class="string"><?=$type['description']?></span>
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
    Нет типов
    <?php
    endif;
    ?>
</div>