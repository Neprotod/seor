<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/ace.js" type="text/javascript"></script>
<script src="<?=Url::root()?>/media/js/jquery-ace-master/jquery-ace.min.js" type="text/javascript"></script>

<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <div class="block loading">
        <?php
        if(!empty($messages))
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
        if(!empty($file) AND !empty($file['path'])):
        ?>
        <div class="infornation">
            <div class="box">
                <h2 class="name">Файл: <input type="text" name="name" value="<?=($name = Request::post('name'))? $name : Request::param($file['name'],TRUE)?>" /></h2>
                <?php
                if(isset($file['style_type'])):
                ?>
                <div>Тип стиля: <?=$file['style_type']?></div>
                <?php
                else:
                ?>
                <div>Расширение: <?=$file['ext']?></div>
                <?php
                endif;
                ?>
                <div>Тип: <?=Request::param($file['type'],NULL,'общий')?></div>
            </div>
            <div class="box">
                <div class="title">
                    <span class="string">Заголовок:</span>
                    <input type="text" name="title" value="<?=($title = Request::post('title'))? $title : Request::param($file['title'],TRUE)?>" />
                </div>
                <div class="description">
                    <span class="string">Описание </span>
                    <textarea name="description"><?=($description = Request::post('description'))? $description : Request::param($file['description'],TRUE)?></textarea>
                </div>
                <div class="default">
                    <span class="string">Использовать по умолчанию? </span>
                    <?php
                    
                    $default = (Request::method('post'))? Request::post('default') : Request::param($file['default']);
                    ?>
                    <input type="checkbox" <?=($default)? 'checked="checked"' : '' ?> name="default" value="1" />
                </div>
            </div>
        </div>
        <div class="file">
            <textarea id="file" name="file"><?=$content?></textarea>
        </div>
        <div class="action">
            <input type="submit" />
        </div>
        <div class="delete">
            <a href="<?=Url::root()?>/templates/style/drop/<?=$template['id']?>/<?=$file['id']?>?ajax">Удалить</a>
        </div>
        <?php
        endif;
        ?>
    </div>
</form>

<?php
if(isset($file['type_name'])):
?>
<link rel="stylesheet" type="text/css" href="<?=Url::root()?>/media/css/ace/<?=$file['type_name']?>.css" />
<script src="<?=Url::root()?>/media/js/jquery-ace-master/ace/mode-<?=$file['type_name']?>.js" type="text/javascript"></script>
<script type="text/javascript">
    // Если редактор включен
    var $source_textarea = $("#file");

    $source_textarea.ace({ theme: 'xcode', lang : '<?=$file['type_name']?>'});

    var decorator = $source_textarea.data("ace");
    
    $(".ace_editor").css('font-size','<?=$config['size']?>px')
    // Убераем линию и добавляем перенос строк
    decorator.editor.ace.getSession().setUseWrapMode(true);
    decorator.editor.ace.setShowPrintMargin(false);
    
</script>
<?php
endif;
?>