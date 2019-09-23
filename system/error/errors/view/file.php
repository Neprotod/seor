<div id="main_file">
    <div id="navigation">
        <h3>Навигация</h3>
        <a href="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('file'=>NULL),'auto')?>">Назад</a>
        <br />
    </div>
    <div id="massage">
        <?=Registry::i()->warning?>
        <?=Registry::i()->massage?>
    </div>
    <br />
    <form method="post">
        <p>Перекодировать с:
            <select name="encode[old]">
                <option>cp1251</option>
                <option>UTF-8</option>
            </select>
            на:
            <select name="encode[new]">
                <option>UTF-8</option>
                <option>cp1251</option>
            </select>
            
            <input type="submit" value="Перекодировать" />
        </p>
    </form>
    <form id="file_form" method="post">
        <input class="source_submit" type="submit" value="Сохранить" />
        <div class="box">
            <div id="number"></div>
            <textarea id="source_textarea" style="display:block;" name="content"><?=$content?></textarea>
            <div id="number"></div>
            <div style="clear:both;"></div>
        </div>
        <div id="results"></div>
        <input name="action" type="hidden" value="save" />
        <input class="source_submit" type="submit" value="Сохранить" />
    </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    /* Вставляем tab при нажатии на tab в поле textarea
    ---------------------------------------------------------------- */
    $("textarea").keydown(function(event){
        // выходим если это не кропка tab
        if( event.keyCode != 9 )
            return;

        event.preventDefault();    

        // Opera, FireFox, Chrome
        var 
        obj = $(this)[0],
        start = obj.selectionStart,
        end = obj.selectionEnd,
        before = obj.value.substring(0, start), 
        after = obj.value.substring(end, obj.value.length);

        obj.value = before + "\t" + after;

        // устанавливаем курсор
        obj.setSelectionRange(start+1, start+1);
    });

});

</script>