<?=$error?>
<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <input type="submit" />
    <div class="block loading width">
        <?=$description?>
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
        <textarea name="content" id="content" class="content editor_large"><?=Str::html_encode($param['content'])?></textarea>
    </div>            
    <div class="block loading">
        <h2>Стили</h2>
        <div class="css">
            <h3>CSS</h3>
            <textarea name="css" id="css" style="width:400px;height:400px;"><?=Str::html_encode($param['css'])?></textarea>
        </div>
        <div class="js">
            <h3>JS</h3>
            <textarea name="js" id="js" style="width:400px;height:400px;"><?=Str::html_encode($param['js'])?></textarea>
        </div>
    </div>                
</form>

<?=$editor?>