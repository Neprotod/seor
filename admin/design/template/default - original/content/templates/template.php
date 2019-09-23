<form method="POST">
    <div class="block loading">
        <div class="description">
            <div class="box">
                <span class="scring">Заголовок темы</span>
                <input type="text" name="title" value="<?=Request::param($template['title'],TRUE)?>" />
            </div>
            <div class="box">
                <span class="scring">Описание</span>
                <textarea name="description"><?=Request::param($template['description'],TRUE)?></textarea>
            </div>
            <div class="box">
                <span class="scring">Статус</span>
                <div>
                    Использовать<input name="status" type="radio" <?=($template['status'])? 'checked="checked"' : '' ?> /> |
                    Не использовать<input name="status" type="radio" <?=(!$template['status'])? 'checked="checked"' : '' ?> />
                </div>
            </div>
        </div>
    </div>
    <div class="block loading">
        <h2>Типы данных</h2>
        <?php
            echo $type->box($template['id'],$types);
        ?>
    </div>
    <div class="block loading">
        <h2>Стили темы</h2>
        <?php
            echo $style->box($template['id'],$styles);
        ?>
    </div>
</form>
<script>
//ID темы
frame.template_id = "<?=$template['id']?>";

//Расширем и заполяем нужные нам свойства
frame.parent = function($element){
    (this).box = $element.parents(".styles, .types");
    (this).block = $element.parents(".block");
    
    if((this).box.hasClass('styles')){
        (this).module = 'style';
        (this).classes = '.styles';
    }else{
        (this).module = 'type';
        (this).classes = '.types';
    }
}

//Если был использован у фрейма submit
frame.frame_load = function($element){
    var object = this;
    $element.contents().find('input[type="submit"]').click(function(){
        (object).reload = true;
        $element.one('load',function(){
            $.ajax({
              type: "GET",
              url: "<?=Url::root()?>/templates/"+(object).module+"/box/"+(object).template_id,
              dataType: "text",
              data: "return=1",
              success: function(date){
                object.fonds = date;
              }
            });
        });
    });
}

frame.drop = function(){
    var object = this;
    if((object).reload == true){
        (this).reload = false;
        if(object.fonds){
            object.create(object.fonds);
            object.fonds = false;
        }else{
            
            $.ajax({
              type: "GET",
               url: "<?=Url::root()?>/templates/"+(object).module+"/box/"+(object).template_id,
              dataType: "text",
              data: "return=1",
              success: function(date){
                object.create(date);
              }
            });
            object.fonds = false;
        }
        
    }
    this.body.css("overflow",'');
    this.iframe.remove();
}

frame.create = function(fond){
    (this).box.remove();
    (this).block.append(fond);
    (this).block.find('.styles, .types').addClass("animated openIn");
    frame.init((this).classes+' a');
}

frame.init('.types a, .styles a');
</script>