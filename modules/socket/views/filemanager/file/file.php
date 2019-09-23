<?php
// Опредляем расширение файла
if(!Request::get('mode')){
    if(preg_match("/\.(php|txt|sql|html|tcl|js|css|xml|xsl|xsd|json)$/i", $file,$ext)){
        $ext = strtolower($ext[1]);
    }else{
        $ext = 'php';
    }
}else{
    $ext = Request::get('mode');
}
// Размер шрифта в редакторе
if(!isset($_SESSION['redactor']['redactor_size']))
    $redactor_size = '16';
else
    $redactor_size = $_SESSION['redactor']['redactor_size'];
?>
<div id="main_file">
    <div id="navigation">
        <h3>Навигация</h3>
        <a  class="link" href="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('file'=>NULL),'auto')?>">Назад</a>
    </div>
    <div id="massage">
        <?=Request::param(Registry::i()->warning)?>
        <?=Request::param(Registry::i()->massage)?>
    </div>
    <div id="file_name">
        <span>Файл: </span><span class="string"><?=$file?></span>
    </div>
    <form method="post">
        <?php
        if(!Request::param($_SESSION['file']['no-redactor'])):
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
    <?php
    // Настройки для редактора
    if(!Request::param($_SESSION['file']['no-redactor'])):
    ?>
    <div id="redactor_options">
        <h4>Настройки редактора</h4>
        <form method="get">
            <input type="hidden" name="dir" value="<?=Request::get('dir')?>" />
            <input type="hidden" name="file" value="<?=Request::get('file')?>" />
            <p>
                <span>Сменить язык</span>
                <select name="mode">
                    <option <?=($ext == 'html')? 'selected="selected"': ''?>>html</option>
                    <option <?=($ext == 'css')? 'selected="selected"': ''?>>css</option>
                    <option <?=($ext == 'php')? 'selected="selected"': ''?>>php</option>
                    <option <?=($ext == 'js')? 'selected="selected"': ''?>>js</option>
                    <option <?=($ext == 'sql')? 'selected="selected"': ''?>>sql</option>
                    <option <?=($ext == 'xml')? 'selected="selected"': ''?>>xml</option>
                    <option <?=($ext == 'txt')? 'selected="selected"': ''?>>txt</option>
                </select>
                
                <input type="submit" value="Сменить" />
            </p>
        </form>
        <form method="post">
            <p>
                <span>Размер шрифта</span>
                <select name="redactor[redactor_size]">
                    <option <?=($redactor_size == 17)? 'selected="selected"': ''?>>17</option>
                    <option <?=($redactor_size == 16.5)? 'selected="selected"': ''?>>16.5</option>
                    <option <?=($redactor_size == 16)? 'selected="selected"': ''?>>16</option>
                    <option <?=($redactor_size == 15.5)? 'selected="selected"': ''?>>15.5</option>
                    <option <?=($redactor_size == '15')? 'selected="selected"': ''?>>15</option>
                </select>
                
                <input type="submit" name="action[redactor_size]" value="Сменить" />
            </p>
        </form>
        <form method="post">
            <p>
                <span>Sroll </span>
                
                <?php
                if(!isset($_SESSION['file']['scroll'])):
                ?>
                <input type="hidden" name="scroll" value="on" />
                <input type="submit" name="action[scroll]" value="Sroll" />
                <?php
                else:
                ?>
                <input type="hidden" name="scroll" value="off" />
                <input type="submit" name="action[scroll]" value="No sroll" />
                <?php
                endif;
                ?>
            </p>
        </form>
        <div style="clear:both;"></div>
    </div>
    <?php
    else:
    ?>
    <form method="post">
        <p>
            <span>Sroll </span>
            
            <?php
            if(!isset($_SESSION['file']['scroll'])):
            ?>
            <input type="hidden" name="scroll" value="on" />
            <input type="submit" name="action[scroll]" value="Sroll" />
            <?php
            else:
            ?>
            <input type="hidden" name="scroll" value="off" />
            <input type="submit" name="action[scroll]" value="No sroll" />
            <?php
            endif;
            ?>
        </p>
    </form>
    <?php
    endif;
    ?>
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
            <textarea id="source_textarea" style="display:block;" name="content"><?=$content?></textarea>
            <div style="clear:both;"></div>
        </div>
        <div id="results"></div>
        <input name="action" type="hidden" value="save" />
        <input class="source_submit" type="submit" value="Сохранить" />
    </form>
</div>
<script src="/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
<script src="/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>
<?php
switch($ext){
    case 'txt'  :  $ext = 'text'; break;
    case 'js'   :  $ext = 'javascript'; break;
    case 'xsl'  :  $ext = 'xml'; break;
    case 'xsd'  :  $ext = 'xml'; break;
}
$style = array(
    'html' => TRUE,
    'css' => TRUE,
    'php' => TRUE,
    'javascript' => TRUE,
    'xml' => TRUE,
    'sql' => TRUE,
    'tcl' => TRUE,
);
?>
<?php
if(isset($style[$ext])):
?>
<link href="/media/css/ace/<?=$ext?>.css" rel="stylesheet" type="text/css">
<?php
endif;
?>
<style type="text/css">
<?=Request::param($style)?>
</style>
<script src="/media/js/jquery-ace-master/ace/mode-<?=Request::param($ext)?>.js" type="text/javascript"></script>

<script type="text/javascript">
<?php
if(isset($_SESSION['file']['scroll']) AND $_SESSION['file']['scroll'] == TRUE):
?>
var sroll_height = $(window).height();
$(window).scrollTop(sroll_height);
<?php
endif;
?>
<?php
if(!Request::param($_SESSION['file']['no-redactor'])):
?>
// Если редактор включен
var $source_textarea = $("#source_textarea");

$source_textarea.ace({ theme: 'xcode', lang : '<?=$ext?>'});

var decorator = $source_textarea.data("ace");

// шрифт самого редактора
$(".ace_editor").css('font-size','<?=$redactor_size?>px')

// Убераем линию и добавляем перенос строк
decorator.editor.ace.getSession().setUseWrapMode(true);
decorator.editor.ace.setShowPrintMargin(false);

<?php
else:
?>
jQuery(document).ready(function($){
    //Вставляем tab при нажатии на tab в поле textarea
    //----------------------------------------------------------------
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
<?php
endif;
?>
/*
// Получаем количество строк
$("body").append('<div id="pre_string" style="display:none;"></div>');
var height;
var $source_textarea = $("#source_textarea");


var val_string;
var count;
function number(string){
    var width = $source_textarea.width();
    if(!string)
        val_string = $source_textarea.val();
    else
        val_string = string;
    var reg = /^.*(\r\n|\n|$)/gim;
    var result = val_string.match(reg);
    // Чтоб не пересохранять
    if(count && result)
        if(count == result.length)
            return;
            
    if(result){
        var i = 0;
        var str = '';
        
        result.forEach(function(element){
            i++;
            str += '<div class="num"><span>'+i+'</span></div>';
        });
        height = $("#number").html(str).height();
        
        $source_textarea.css('height',height+'px');
        count = result.length;
    }
}
number();
$source_textarea.keyup(function(event){
    //if((var string = $(this).val()) != val_string);
        number();
    
});*/
</script>