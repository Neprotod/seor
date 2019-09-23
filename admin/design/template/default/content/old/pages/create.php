<?php
    if(isset(Registry::i()->error_errors)){
        foreach(Registry::i()->error_errors AS $error_type => $errors){
            ?>
            <div class="<?=(!empty($error_type))? "error_".$error_type : ''?>">
                <?php
                foreach($errors AS $error):
                ?>
                <div>
                    <?=$error['massage']?>
                </div>
                <?php
                endforeach;
                ?>
            </div>
            <?php
        }
    }
?>
<script>
    function error_errors(){
        var error = <?=(isset(Registry::i()->error_JQuery))?json_encode(Registry::i()->error_JQuery):''?>;
        if(typeof error == 'object')
            for(er in error){
                $(er).addClass(error[er])
            }
    }
</script>

<form method="POST">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <input type="submit" />
    <div class="block loading width">
        <?=Request::param($description)?>
    </div>
    <div class="block loading width">
        <h2>Отображения</h2>
        <?=Request::param($type)?>
    </div>
    <div class="block loading width clear">
        <h2>Стили</h2>
        <?=Request::param($style)?>
    </div>        
    <div class="block loading">
        <h2>Контент</h2>
        <textarea name="content" id="content" class="content editor_large"><?=Str::html_encode(Request::param($param['content']))?></textarea>
    </div>            
    <div class="block loading">
        <h2>Стили</h2>
        <div class="css">
            <h3>CSS</h3>
            <textarea name="css" id="css" style="width:400px;height:400px;"><?=Str::html_encode(Request::param($param['css']))?></textarea>
        </div>
        <div class="js">
            <h3>JS</h3>
            <textarea name="js" id="js" style="width:400px;height:400px;"><?=Str::html_encode(Request::param($param['js']))?></textarea>
        </div>
    </div>                
</form>
<script>
    error_errors();
</script>
<?=$editor?>