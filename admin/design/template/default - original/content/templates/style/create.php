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
        <div class="name">
            <span class="string">Имя</span>
            <input type="text" name="name" value="<?=Request::post('name')?>" />
        </div>
        <div class="style">
            <span class="string">Тип стиля </span>
            <select name="style_type">
                <?php
                foreach($style_list AS $key => $style):
                ?>
                <option <?=($key == $style_type OR $style['id'] == $style_type)? 'selected="selected"' : ''?> value="<?=$style['id']?>"><?=$key?></option>
                <?php
                endforeach;
                ?>
            </select>
        </div>
        <div class="type">
            <span class="string">Тип данных </span>
            <select name="type_id">
                <?php
                foreach($type_list AS $key => $type):
                ?>
                <option <?=($type['exist'] OR (isset($type['id']) AND $type['id'] == $type_id))? 'selected="selected"' : ''?> value="<?=(!empty($key))?$type['id'] : NULL ?>"><?=(!empty($key))?$type['type'] : 'общий'?></option>
                <?php
                endforeach;
                ?>
            </select>
        </div>
        <div class="information">
            <fieldset>
                <legend>Не обязательные поля</legend>
                <div class="title">
                    <span class="string">Заголовок </span>
                    <input type="text" name="title" value="<?=Request::post('title')?>" />
                </div>
                <div class="description">
                    <span class="string">Описание </span>
                    <textarea name="description"><?=Request::post('description')?></textarea>
                </div>
                <div class="default">
                    <span class="string">Использовать по умолчанию? </span>
                    <input type="checkbox" <?=(Request::param('default'))? 'selected="selected"' : '' ?> name="default" value="1" />
                </div>
            </fieldset>
        </div>
        <div class="action">
            <input type="submit" value="Создать" />
        </div>
    </div>
</form>