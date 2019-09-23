<div class="types">
    <div class="box">
        <?php
        if(!empty($content_type)):
        foreach($content_type AS $key => $type):
        if($key == NULL):
        ?>
        <h3>Используется</h3>
        <?php
        endif;
        ?>
        <label class="type <?=$type['type']?> <?=($key == NULL)? 'default' : '' ?>">
            <div class="information">
                <input name="content_type" <?=(($key == NULL) OR (Request::post('content_type',NULL,FALSE) == $key))? 'checked="checked"' : '' ?> type="radio" value="<?=$type['id']?>" />
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
        </label>
        <?php
        endforeach;
        else:
        ?>
        <div class="errors">
        Нет типов отображения, это вызовет критическое ошибку на сайте. Перейдите к <a href="<?=Url::root()?>/templates/template/<?=$template['id']?>">теме</a> и создайте тип данных.
        </div>
        <?php
        endif;
        ?>
    </div>
</div>