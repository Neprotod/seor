<?php
if(!empty($style_contents)):
?>
<div class="interactive">
    <div class="styles">
    <h3>Используемые стили</h3>
        <?php
        foreach($style_contents AS $key => $values):
            $title = '';
            switch($key){
                case 'default':
                    $title = 'По умолчанию';
                    break;
                case 'enabled':
                    $title = 'Подключенные';
                    break;
                case 'disabled':
                    $title = 'Отключенные';
                    break;
            }
        ?>
        <div class="box <?=$key?>">
            <h4><?=$title?></h4>
            <?php
            $fond = array();
            foreach($values AS $fonds){
                $fond = Arr::merge($fond,$fonds);
            }
            
            $values = $fond;
            
            foreach($values AS $style_type => $value):
            ?>
            <div class="<?=$style_type?>">
                <h5><?=$style_type?></h5>
            <?php
            foreach($value AS $id => $style):
            $type = ($style['type'] == NULL)? 'общий' : $style['type'];
            $style['type'] = ($style['type'] == NULL)? 'general' : $style['type'];
            ?>
                <label class="style <?=$style['type']?>">
                    <div class="action">
                        <input type="checkbox" <?=((isset($style['default']) AND (!isset($style['status']) OR $style['status'] != 0) OR (isset($style['status']) AND  $style['status'] != 0)) )? 'checked="checked"' : ''?> value="1" name="style[<?=$id?>]" style="">
                    </div>
                    <div class="information">
                        <span class="type"><?=$type?></span>
                        <span class="name"><?=$style['name']?></span>
                        <?php
                        if(isset($style['path'])):
                        ?>
                        <span class="path"><?=$style['path']?></span>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(isset($style['ext'])):
                        ?>
                        <span class="ext"><?=$style['ext']?></span>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(isset($style['title'])):
                        ?>
                        <div class="split">
                            <span class="string"><?=$style['title']?></span>
                        </div>
                        <?php
                        endif;
                        ?>
                    </div>
                    <?php
                    if(isset($style['description'])):
                    ?>
                    <div class="description">
                        <div class="hr"></div>
                        <span class="string"><?=$style['description']?></span>
                    </div>
                    <?php
                    endif;
                    ?>
                </label>
            <?php
            endforeach;
            ?>
            </div>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        endforeach;
        ?>
    </div>
</div>
<?php
endif;
?>

<?php
if(!empty($styles)):
?>
<div class="not_used">
    <div class="styles">
        <h3>Не используемые стили</h3>
        <?php
        foreach($styles AS $key => $values):
        ?>
        <div class="box <?=$key?>">
            <h3>Стили: <?=($key == NULL)? 'Общие' :$key ?></h3>
            <?php
            foreach($values AS $style_type => $value):
            ?>        
            <div class="<?=$style_type?>">
                <h4><?=$style_type?></h4>
                <?php
                foreach($value AS $id => $style):
                echo $id;
                $type = ($style['type'] == NULL)? 'общий' : $style['type'];
                $style['type'] = ($style['type'] == NULL)? 'general' : $style['type'];
                ?>
                <label class="style <?=$style['type']?>">
                    <div class="action">
                        <input type="checkbox" <?=(isset($style['status']) AND  $style['status'] != 0)? 'checked="checked"' : ''?> value="1" name="style[<?=$id?>]" style="">
                    </div>
                    <div class="information">
                        <span class="type"><?=$type?></span>
                        <span class="name"><?=$style['name']?></span>
                        <?php
                        if(isset($style['path'])):
                        ?>
                        <span class="path"><?=$style['path']?></span>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(isset($style['ext'])):
                        ?>
                        <span class="ext"><?=$style['ext']?></span>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(isset($style['title'])):
                        ?>
                        <div class="split">
                            <span class="string"><?=$style['title']?></span>
                        </div>
                        <?php
                        endif;
                        ?>
                    </div>
                    <?php
                    if(isset($style['description'])):
                    ?>
                    <div class="description">
                        <div class="hr"></div>
                        <span class="string"><?=$style['description']?></span>
                    </div>
                    <?php
                    endif;
                    ?>
                </label>
            </div>
                <?php
                endforeach;
                ?>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        endforeach;
        ?>
    </div>
</div>
<?php
endif;
?>
<script type="text/javascript">
    var style = $(".styles .style");
    
    style.each(function(){
            if($(this).find('.action input:checkbox:checked').length > 0)
                $(this).addClass('active');
    });
    
    style.click(function(){
        if($(this).find('.action input:checkbox:checked').length > 0){
            $(this).addClass('active');
        }else{
            $(this).removeClass('active');
        }
    });
</script>