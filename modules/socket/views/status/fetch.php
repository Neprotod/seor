<div id="navigation">
    <h3>Навигация</h3>
    <a class="link" href="/<?=Registry::i()->host?>/filemanager">filemanager</a>
    <a class="link" href="/<?=Registry::i()->host?>/mysql">mysql</a>
    <a class="link" href="/<?=Registry::i()->host?>/error">error</a>
</div>
<div id="status_box">
    <h1>Статус</h1>
    <form method="post" id="status_information">
        <p class="status_version">
            <span class="name">Версия: </span> 
            <?php
            $versions =  explode('.',Core::VERSION);
            foreach($versions as $key => $version):
            ?>
            <input style="width:<?=max(20,(Utf8::strlen($version)*10))?>px;" type="text" name="status[version][<?=$key?>]" value="<?=$version?>" />
            <?php
            if(isset($versions[$key+1]))
                echo '.';
            ?>
            <?php
            endforeach;
            ?>
        </p>

        <p class="status_mode">
            <span class="name">Режим:</span> 
            <select name="status[mode]">
                 <option value="1" <?=(Core::$selected_mode == 1)?'selected="selected"':''?>>PRODUCTION</option>
                 <option value="2" <?=(Core::$selected_mode == 2)?'selected="selected"':''?>>STAGING</option>
                 <option value="3" <?=(Core::$selected_mode == 3)?'selected="selected"':''?>>TESTING</option>
                 <option value="4" <?=(Core::$selected_mode == 4)?'selected="selected"':''?>>DEVELOPMENT</option>
            </select>
        </p>
        <p class="status_id">
            <span class="name">Идентификатор:</span> 
            <input type="text" name="status[id]" value="<?=Core::TREE_ID?>" />
        </p>
        <p class="status_host">
            <span class="name">Хост:</span>
            <span class="string"><?=$_SERVER['HTTP_HOST']?></span>
        </p>

        <input type="submit" value="Переключить" />
    </form>
    <?php
    if(defined("Core::TREE_HOST")):
        $host = str_replace('www.','',$_SERVER['HTTP_HOST']);
        if(Core::TREE_HOST != $host):
    ?>
    <form method="post" id="status_activate">
        <input type="submit" name="tree[activate]" value="Активировать" />
    </form>
    <?php
        else:
    ?>
    <form method="post" id="status_deactivate">
        <input type="submit" name="tree[deactivation]" value="Деактивировать" />
    </form>
    <?php
        endif;
    ?>
    <?php
    else:
    ?>
    <div>Удалили Core::TREE_HOST</div>
    <?php
    endif;
    ?>
</div>
<div id="view" class="">
    <span id="span_view" style="border-bottom:1px solid;color:blue;cursor:pointer;">Вывести на экран</span>
</div>
<div id="admin" class="">
    <span id="span_admin" style="border-bottom:1px solid;color:blue;cursor:pointer;">Административная панель</span>
</div>
<div id="box_module_error">
    <div id="status_module" style="">
        <p>Все подключенные модули:</p>
        <hr />
        <?php
        $modules = Module::module_path();
        foreach($modules as $module):
        Module::factory($module);
        ?>
        <p style=" padding-bottom:5px;border-bottom:1px solid #000;"><?=$module?> <span style="float:right;"><?=(defined("{$module}_Module::VERSION"))? " | версия: " . constant("{$module}_Module::VERSION") : ''?><span></p>
        <?php
        endforeach;
        ?>
        
    </div>
    <?php
    $xml = Model::factory("error_xml","socket");
    if(!empty($xml->errors)):
    ?>
    <div id="status_error" style="display:table;">
        <p>Ошибки:</p>
        <hr />
        <?php
        foreach($xml->errors as $errors):
        ?>
        <div class="box">
            <p><b>Тип:</b> <?=$errors['type']?></p>
            <p><b>Код ошибки:</b> <?=$errors['code']?></p>
            <?php
                if(isset($errors['more_attribute'])){
            ?>
            <p><b>Дополнительные атрибуты:</b></p>
            <div id="more_attribute">
                <?php
                    foreach($errors['more_attribute'] as $name => $value){
                        echo "<p style=\"margin-left:10px;\"><b>$name:</b> <span>$value</span></p>";
                    }
                ?>
            </div>
            <?php
                }
            ?>
            <p><b>Сообщение:</b> <?=$errors['message']?></p>
            <p><b>Файл:</b> <?=$errors['file']?></p>
            <p><b>На линии:</b> <?=$errors['line']?></p>
            <p><b>Класс:</b> <?=$errors['class']?></p>
            <p><?=$errors['debug']?></p>
        </div>
        <?php
        endforeach;
        ?>
    </div>
    <?php
    endif;
    ?>
    <div style="clear:both;"></div>
</div>
<div style="clear:both;"></div>
<script type="text/javascript">
var view = {
    init : function(view,span,src){
        var object = this;
        
        var obj_view = {
            view : '',
            span : '',
            src : '',
            $view : '',
            content : ''
        }
        // Рабочий блок
        obj_view.view = view;
        
        // span для создания фрейма
        obj_view.span = span;
        
        // Адрес запуска
        obj_view.src = src;
        
        obj_view.$view = $(obj_view.view);
        // Сохраняем контент на возврат
        obj_view.content = obj_view.$view.html();
        
        (object).get(obj_view);
        
    },
    get : function(obj_view){
        var object = this;
        obj_view.$view.find(obj_view.span).click(function(){
            // Убераем прокрутку
            $('body').css('overflow','hidden');
            
            obj_view.$view.addClass('open');
            obj_view.$view.html('<div class="box"><div class="close"><span class="addr"></span><span class="close_span">Закрыть</span><div style="clear:both;"></div></div><iframe src="'+obj_view.src+'"></iframe></div>');
            $(""+obj_view.view+" .close span").click(function(){
                obj_view.$view.html(obj_view.content);
                obj_view.$view.removeClass('open');
                (object).get(obj_view);
                (object).loader(obj_view);
                $('body').css('overflow','');
            });
            (object).loader(obj_view);
        });
    },
    loader : function(obj_view){
        var object = this;
        var iframe = obj_view.$view.find('iframe');
        /**/
        iframe.css('background','url(/media/iframe/loadingBar.gif) no-repeat center #fff');
        iframe.load(function(){
            iframe.css('background','');
        });
        
    }
}
view.init('#view','#span_view',"<?=Core::$root_url?>");
view.init('#admin','#span_admin',"<?=Core::$root_url?>/<?=Core::TREE_ID?>");

/*
var admin = {
    view : '#admin',
    span : '#span_admin',
    $view : '',
    content : '',
    init : function(){
        var object = this;
        (object).$view = $((object).view);
        (object).content = (object).$view.html();
        (object).get();
        
    },
    get : function(){
        var object = this;
        (object).$view.find((object).span).click(function(){
            (object).$view.addClass('open');
            (object).$view.html('<div class="box"><div class="close"><span class="close_span">Закрыть</span><div style="clear:both;"></div></div><iframe src="<?=Core::$root_url?>/<?=Core::TREE_ID?>"></iframe></div>');
            $(""+(object).view+" .close span").click(function(){
                (object).$view.html((object).content);
                (object).$view.removeClass('open');
                (object).get();
                (object).loader();
            });
            (object).loader();
        });
    },
    loader : function(){
        var object = this;
        var iframe = (object).$view.find('iframe');
        /**//*
        iframe.css('background','url(/media/iframe/loadingBar.gif) no-repeat center #fff');
        iframe.load(function(){
            iframe.css('background','');
        });
        
    }
}
admin.init();*/
</script>