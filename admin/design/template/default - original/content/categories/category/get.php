<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <input type="submit" />
    <div class="block loading width">
        <div class="description">
            <h2 class="title">
                <span class="name">Заголовок</span>
                <span class="string">
                    <input type="text" name="title" value="<?=$category['title']?>" />
                </span>
            </h2>
            <div class="url">
                <span class="name">URL</span>
                <span class="string">
                    <input type="text" name="url" value="<?=$category['url']?>" />
                </span>
            </div>
            <div class="date">
                <h2>Дата</h2>
                <div class="create">
                    <span class="name">Дата создания</span>
                    <span class="string"><?=$category['date']?></span>
                </div>
                <div class="modified">
                    <?php
                    if(!empty($category['modified'])):
                    ?>
                    <span class="name">Дата изменения</span>
                    <span class="string"><?=$category['modified']?></span>
                    <?php
                    else:
                    ?>
                    <span class="name">Не изменялся</span>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
            <div class="status">
                <h2>Статус</h2>
                <label>
                    <input <?=($category['status'] == 1)? 'checked="checked"' : '' ?> type="radio" name="status" value="1" /> 
                    Активен
                </label>
                <label>
                    <input <?=($category['status'] == 0)? 'checked="checked"' : '' ?> type="radio" name="status" value="0" /> 
                    Откличен
                </label>
            </div>
            <div class="meta">
                <h2>Мета данные</h2>
                <div class="meta_title">
                    <span class="name">Мета заголовок</span>
                    <span class="string">
                        <input type="text" name="meta_title" value="<?=Request::param($category['meta_title'])?>" />
                    </span>
                </div>
                <div class="meta_description">
                    <span class="name">Мета описание</span>
                    <div class="string">
                        <textarea name="description"><?=Request::param($category['description'],TRUE)?></textarea>
                    </div>
                </div>
                <div class="meta_keywords">
                    <span class="name" style="font-weight:bold;">Сделать выпадающий список</span>
                </div>
                <div class="robots">
                    <h3>Индексация</h3>
                    <div class="index">
                        <label>
                            <input <?=(isset($robots['index']))? 'checked="checked"' : '' ?> id="index" type="radio" name="robots[index]" value="index" /> 
                            <span class="name">Индексировать</span>
                        </label>
                        <label>
                            <input <?=(isset($robots['noindex']))? 'checked="checked"' : '' ?> type="radio" name="robots[index]" value="noindex" /> 
                            <span class="name">Не индексировать</span>
                        </label>
                    </div>
                    <div class="follow">
                        <label>
                            <input <?=(isset($robots['follow']))? 'checked="checked"' : '' ?> type="radio" name="robots[follow]" value="follow" />
                            <span class="name">Переходить по ссылке</span>
                        </label>
                        <label>
                            <input <?=(isset($robots['nofollow']))? 'checked="checked"' : '' ?> type="radio" name="robots[follow]" value="nofollow" />
                            <span class="name">Не переходить по ссылке</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="block loading width">
        <h2>Отображения</h2>
        <?=$type?>
    </div>
    <div class="block loading width clear">
        <h2>Стили</h2>
        <?=$style?>
    </div>        
    <div class="block loading">
        <h2>Контент</h2>
        <textarea name="content" id="content" class="content editor_large"><?=Str::html_encode(Request::param($category['content']))?></textarea>
    </div>            
    <div class="block loading">
        <h2>Стили</h2>
        <div class="css">
            <h3>CSS</h3>
            <textarea name="css" id="css" style="width:400px;height:400px;"><?=Str::html_encode(Request::param($category['css']))?></textarea>
        </div>
        <div class="js">
            <h3>JS</h3>
            <textarea name="js" id="js" style="width:400px;height:400px;"><?=Str::html_encode(Request::param($category['js']))?></textarea>
        </div>
    </div>                
</form>

<!-- Редактор кода -->
<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
<script src="<?=Url::root()?>/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?=Url::root()?>/media/css/ace/css.css" />
<link rel="stylesheet" type="text/css" href="<?=Url::root()?>/media/css/ace/javascript-clouds.css" />
<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/mode-css.js" type="text/javascript"></script>
<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/mode-javascript.js" type="text/javascript"></script>

<!-- Редактор контента -->
<?php 
    $editor =  ".".Registry::i()->root."/editor/tinymce.php";
    if(is_file($editor)){
        include "$editor";
    }
?>
<script>
    $(".type input").each(function(){
        $(this).css('display','none');
        if($(this).attr("checked")){
            $(this).parents('.type').addClass('checked');
        }
    });
    
    $(".type").click(function(){
        $(".type").each(function(){
            if($(this).find('input').attr("checked")){
                $(this).addClass('checked');
            }else{
                $(this).removeClass('checked');
            }
            
        });
    });
    
    function get_edit($id,lang,theme){
        
        if(!theme){
            theme = 'xcode';
        }
        // Если редактор включен
        var $source_textarea = $("#"+$id);

        $source_textarea.ace({ theme: theme, lang : lang});

        var decorator = $source_textarea.data("ace");
        
        $(".ace_editor").css('font-size','16.5px')
        // Убераем линию и добавляем перенос строк
        decorator.editor.ace.getSession().setUseWrapMode(true);
        decorator.editor.ace.setShowPrintMargin(false);
    }
    get_edit("css",'css');
    get_edit("js",'javascript','clouds');
    
    
    //Отлавливать событие нажатие CTRL + S

    $(window).keydown(function($event){
        if($event.ctrlKey == true && $event.which == 83){
            return false;
        }
        
    });
</script>