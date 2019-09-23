<?php
function parse($results,$margin = 0){
    ?>
    <div style="margin-left:<?=$margin?>px;">
    <?php
    foreach($results as $key => $result):
        if(is_array($result)){
            parse($result,$margin+20);
            continue;
        }
    ?>
        <div class="box_result">
            <div class="column_name"><?=$key .' : '?></div>
            <div class="column_body">
                <code><?=($result === NULL)? "NULL" : htmlentities($result,ENT_NOQUOTES,'UTF-8')?></code>
            </div>
        </div>
    <?php
    endforeach;    
    ?>
    <hr />
    </div>
    <?php
}
function parse_form($results,$margin = 0,$database = NULL){
    foreach((array)$results as $key => $result):
        ?>
    <div style="margin-left:<?=$margin?>px;">
        <?php
        if(is_array($result))
            foreach($result as $name => $value):
        ?>
            <div>
                <?=$name .' : '. $value?>
            </div>
            <?php
                if($name == 'table_name'){
                    $name_table = $value;
                }
            ?>
    
    <?php
            endforeach;
        ?>
        <form method="post" style="display:inline;">
            <input name="table" type="hidden" value="<?=$name_table?>" />
            <input name="drop" type="submit" value="Drop" />
        </form>
        <form method="post" style="display:inline;">
            <input name="key" type="hidden" value="<?=$key?>" />
            <input name="table" type="hidden" value="<?=$name_table?>" />
            <input name="tree[get][file]" type="hidden" value="<?=$database.'_'.$name_table.date("d.m.Y")?>.sql" />
            <input name="save" type="submit" value="Save" />
        </form>
        <hr />
    </div>
        <?php
    endforeach;
}
/************************/
?>
<div id="main_sql">
    <div id="navigation">
        <h3>Навигация</h3>
        <a class="link" href="/<?=Registry::i()->host?>">К статусу</a>
        <a class="link" href="/<?=Registry::i()->class_link?>/backup">К backup</a>
    </div>
    <?php
    if(isset(Registry::i()->massage)):
    ?>
    <div id="massage">
        <?=Registry::i()->massage?>
    <div>
    <?php
    endif;
    ?>
    <form method="post">
    <?php
    if(!Request::param($_SESSION['mysql']['no-redactor'])):
    ?>
        <input name="action[no-redactor]" type="submit" value="Отключить редактор" />
    <?php
    else:
    ?>
        <input name="action[redactor]" type="submit" value="Редактор" />
    <?php
    endif;
    ?>
    </form>
    <div id="mysql">
        <form class="form_left" method="post" style="float:left;">
            <p>База данных:    <?=$database?></p>
            <textarea id="sql" name="mysql[sql]"><?=Request::param($_POST['mysql']['sql'])?></textarea>
            <select name="mysql[type]">
                <option <?=($_POST['mysql']['type'] == '1')?'selected="selected"':'';?> value="1">SELECT</option>
                <option <?=($_POST['mysql']['type'] == '2')?'selected="selected"':'';?> value="2">INSERT</option>
                <option <?=($_POST['mysql']['type'] == '3')?'selected="selected"':'';?> value="3">UPDATE</option>
                <option <?=($_POST['mysql']['type'] == '4')?'selected="selected"':'';?> value="4">DELETE</option>
            </select>
            <input type="submit" value="Запрос" />
        </form>
        <div id="show_table">
            <?php
                if(Request::param($tables)){
                    parse_form($tables,0,$database);
                }
            ?>
        </div>
    <div style="clear:both;"></div>
    </div>
    <div id="result">
        <?php
            if(Request::param($result)){
                parse($result);
            }
        ?>
    </div>
</div>
<?php
if(!Request::param($_SESSION['mysql']['no-redactor'])):
?>
<script src="/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
<script src="/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>
<script src="/media/js/jquery-ace-master/ace/mode-sql.js" type="text/javascript"></script>
<script src="/media/js/jquery-ace-master/ace/mode-sql.js" type="text/javascript"></script>
<?php
endif;
?>
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
        //var 
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
$('input[name="drop"]').click(function(){
    return confirm("Удалить таблицу?");
});
<?php
if(!Request::param($_SESSION['file']['no-redactor'])):
?>
// Если редактор включен
var $source_textarea = $("#sql");
$source_textarea.width($source_textarea.width()+80);
$source_textarea.height($source_textarea.height()+40);
$source_textarea.ace({ theme: 'xcode', lang : '<?=$ext?>'});

var decorator = $source_textarea.data("ace");

// шрифт самого редактора
$(".ace_editor").css('font-size','16px')

// Убераем линию и добавляем перенос строк
decorator.editor.ace.getSession().setUseWrapMode(true);
decorator.editor.ace.setShowPrintMargin(false);

<?php
endif;
?>
</script>