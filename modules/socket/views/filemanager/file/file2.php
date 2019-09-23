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
        <span class="string"><?=$file?></span>
    </div>
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
    <textarea id="test" style="display:block;" name=""></textarea>
</div>
<script type="text/javascript" src="/media/js/jquery/ata.js"></script>
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
            str += '<div class="num"><span>'+i+'</span><pre style="visibility:hidden;width:'+width+'px; margin-top:-17px; float:left;">'+element+'</pre></div>';
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
    
});
</script>