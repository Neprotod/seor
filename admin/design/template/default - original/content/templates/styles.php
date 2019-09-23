<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
<script src="<?=Url::root()?>/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>
<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <input type="submit" value="Применить" />
    <?php
    if(!empty($styles)):
    foreach($styles AS $key => $values):
    $type = ($key == NULL)? 'general' : $key ;
    $type_name = ($key == NULL)? 'Общие стили' : $key ;
    ?>
    <div class="block loading <?=$type?>">
        <h2><?=$type_name?> <a href="<?=Url::root()?>/templates/create_style/<?=$template['id']?>/<?=$type?>" class="create"><span>Добавить</span></a></h2>
        <?php
        if(!empty($values)):
        foreach($values AS $style_type => $value):
        ?>
        <div class="style">
            <h3><?=$style_type?></h3>
            <table class="box">
                <tr>
                    <th>Имя файла</th>
                    <th>Заголовок</th>
                    <th>Описание</th>
                    <th>По умолчанию</th>
                    <th>Действия</th>
                </tr>
            <?php
            foreach($value AS $style):
            ?>
                <tr>
                    <td class="name">
                        <span class="string"><?=$style['name']?></span>
                    </td>
                    <td class="title">
                        <span class="string">
                            <?=Request::param($style['title'],TRUE)?>
                        </span>
                    </td>
                    <td class="description">
                        <span class="string">
                            <?=Request::param($style['description'],TRUE)?>
                        </span>
                    </td>
                    <td class="default">
                        <span class="string">
                            <?=($style['default']) ? 'V' : '-'?>
                        </span>
                    </td>
                    <td class="actions">
                        <div class="action">
                            <a href="<?=Url::root()?>/templates/style/<?=$style['id']?>">Редактировать</a>
                            <a href="<?=Url::root()?>/templates/drop_style/<?=$style['id_template']?>/<?=$style['id']?>">Удалить</a>
                        </div>
                    </td>
                </tr>
            <?php
            endforeach;
            ?>
            </table>
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
    <?php
    endforeach;
    else:
    ?>
    <div class="string">Нет стилей</div>
    <?php
    endif;
    ?>
</form>
<script>
frame.parent = function(element){
    (this).box = element.parents('.box');
    (this).tr = element.parents('tr');
    (this).style = (this).box.parents('.style');
    (this).frame = (this).iframe.find('iframe');
}
frame.create = function(href){
    var object = this;
    $.ajax({
      type: "POST",
      url: "<?=Url::root()?>/system/ajax/style.php",
      data: {"url":href},
      dataType: "json",
      success: function(data){
        //Если есть результат
        if(data != ''){
            //Заполняем нужными полями
            if((object).tr.length > 0){
                var tr = (object).tr.clone();
            }else{
                window.location.reload()
            }
            tr.find('.name .string').text(data.name);
            tr.find('.title input').val(data.title).attr('name','title['+data.id+']');
            tr.find('.default input').val(data.title).attr('name','default['+data.id+']');
            if(data.default == 1)
                tr.find('.default input').attr('checked','checked');
            else
                tr.find('.default input').removeAttr('checked');
            
            tr.find('.description textarea').val(data.title).attr('name','description['+data.id+']');
            tr.find('.actions .action a').each(function(){
                var href = $(this).attr('href');
                var href = (object).parse_url(href,data.id);
                $(this).attr('href',href);
            });
            
            (object).box.find('tr').eq(0).after(tr);
        }
      }
    });
}
frame.delet = function(href){
    var object = this;
    $.ajax({
      type: "POST",
      url: "<?=Url::root()?>/system/ajax/style.php",
      data: {"url":href},
      dataType: "json",
      success: function(data){
        //Если есть результат
        if(data == ''){
            //Заполняем нужными полями
            (object).tr.remove();
            if((object).box.find('tr').length == 1){
                (object).style.html("Нет стилей");
            }
        }
      }
    });
}
frame.parse_url = function(href,id){
    var length = href.length;
            
    var index = href.lastIndexOf('/');
    
    var get = getUrlVars(href);
    var query = '';
    if(get)
        for(q in get)
                if(!query)
                    query += "?"+q + ((get[q])? '='+ get[q]: '');
                else
                    query += "&"+q + ((get[q])? '='+ get[q]: '');
    
    href = href.slice(0, index) + '/';
    href += id;
    href += query;
    return href;
}
frame.drop = function(){
    var href = this.frame.contents().get(0).location.href;
    var get = getUrlVars(href);
    
    if(get.create)
        (this).create(href);
    
    if(href.lastIndexOf('drop_style') != -1)
        (this).delet(href);
    
    (this).body.css("overflow",'');
    (this).iframe.remove();
}
frame.init('a');
</script>