<div id="navigation">
    <h3>Навигация</h3>
    <a class="link"  href="/<?=Registry::i()->host?>">К статусу</a>
    <a class="link" href="/<?=Registry::i()->class_link?>/correlate">Сверить файлы</a>
</div>
<div id="information">
    <?php
    $pathArr = explode('/',$path);
    $nav = '';
    foreach((array)$pathArr as $pathA):
    if($pathA == '.')
        $nav .= $pathA;
    else
        $nav .= '/'.$pathA;
    ?>
    <a style="float:left;" href="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('dir'=>Request::param($nav,TRUE)),'auto')?>"><?=utf8_decode($pathA)?>/</a>
    <?php
    endforeach;
    ?>
    <div style="clear:both;"></div>
    <?php
    if($back !== NULL):
    ?>
    <p class="back"><a href="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('dir'=>Request::param($back,TRUE)),'auto')?>">Назад</a></p>
    <?php
    endif;
    ?>
    <div class="form_box_top">
        <form method="post" class="information_form" id="big_zip" action="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('dir'=>Request::param($path,TRUE)),'auto')?>">
            <input class="big_zip" type="text" name="tree[big_zip][canonical]" />
            <input class="file" type="hidden" name="tree[big_zip][file]" />
            <input type="submit" value="Big zip" />
        </form>
    </div>
    <div class="form_box_top">
        <form method="post" class="information_form" id="file_upload" enctype="multipart/form-data">
            <input type="file" multiple="multiple" name="file[]" />
            <input name="upload" type="submit" value="Залить" />
        </form>
    </div>
    <div class="form_box_top">
        <form method="post" class="information_form">
            <input type="text" name="create" />
            <input name="create_file" type="submit" value="Создать файл" />
        </form>
    </div>
    <div class="form_box_top">
        <form method="post" class="information_form">
            <input type="text" name="create" />
            <input name="create_dir" type="submit" value="Создать директорию" />
        </form>
    </div>
    <?php
    if(isset($_SESSION['directory']['save'])):
    ?>
    <div id="bufer" style="">
        <form method="post">
            <p>Буфер сейчас содержит</p>
            <?php
            $key = key($_SESSION['directory']['save']);
            foreach((array)reset($_SESSION['directory']['save']) as $save):
            ?>
            <p><?=$key.'/'.utf8_decode($save)?></p>
            <?php
            endforeach;
            ?>
            <input name="clear" type="submit" value="Очистить буфер" />
            <input name="past" type="submit" value="Вставить" />
            <input name="cut" type="submit" value="Врезать" />
        </form>
    </div>
    <?php
    endif;
    ?>
    <div style="clear:both;"></div>
    <hr />
</div>
<form method="post" id="main_form">
    <div id="directory" class="table">
        <?php
        if(!empty($directories))
        foreach($directories as $directory):
        ?>
        <p class="table-row">
            <a class="table-cell name" href="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('dir'=>Request::param($path.'/'.$directory['dir'],TRUE)),'auto')?>"><?=$directory['name']?></a>
            <input class="checkbox table-cell input" name="selected[<?=$path?>][]" type="checkbox" value="<?=$directory['dir']?>" />
            
            <a class="rename table-cell">Переименовать</a>
            <?php
            if(!Core::$is_windows):
            ?>
            <span class="UNIX table-cell">UNIX</span>
            <?php
            endif;
            ?>
        </p>
        <?php
        endforeach;
        ?>
    </div>
    <h3>Файлы</h3>
    <div id="file" class="table">
        <?php
        if(!empty($files))
        foreach($files as $file):
        ?>
        <p class="table-row">
            <a class="table-cell name" href="/<?=Registry::i()->class_link?>/file<?=Url::query(array('dir'=>Request::param($path),'file'=>Request::param($file['file'])),'auto')?>"><?=$file['name']?></a>
            <span style="padding-left:10px;" class="table-cell"><?=Registry::i()->size[$file['name']]?></span>
            <input class="checkbox table-cell input" name="selected[<?=$path?>][]" type="checkbox" value="<?=$file['file']?>" />
        
            <a class="save table-cell">Скачать</a>
            <a class="rename table-cell">Переименовать</a>
            <?php
            // Для линукса
            if(!Core::$is_windows):
            ?>
            <span class="UNIX table-cell">UNIX</span>
            
            <?php
            endif;
            ?>
            <?php
            if(preg_match("/\.(zip|gz|bz2|rar|tag|jpg|jpeg|png|gif|bmp|tiff|ico|raw|psd|ecw|avi|mkv|mpg)$/i", $file['name'])):
            ?>
            <a href="<?=Core::$root_url.ltrim($path.'/'.$file['file'],'.')?>" class="table-cell save_link">Скачать как ссылку</a>
            <?php
            endif;
            ?>
        </p>
        <?php
        endforeach;
        ?>

    </div>
    <div class="form_box first">
        <input name="save" type="submit" value="Скопировать" />
    </div>
    <div class="form_box">
        <input name="unlink" type="submit" value="Удалить" />
    </div>
    <div class="form_box">
        <input name="archiv_name" type="text" value="" />
        <input name="archivate" type="submit" value="Архивировать" />
    </div>
    <div class="form_box">
        <input name="de_archivate" type="submit" value="Разархевировать" />
    </div>
    <?php
        if($path == '.'):
    ?>
    
    <div class="form_box">
        <input name="backup" type="submit" value="Backup" />
    </div>
    <?php
        endif;
    ?>
    <div class="form_box">
        <input name="UTF-8" type="submit" value="Convert to UTF-8" />
        <input name="cp1251" type="submit" value="Convert to cp1251" />
    </div>
</form>
<form style="display:none;" method="post" id="get" action="/<?=Registry::i()->class_link?>/get<?=Url::query(array('dir'=>Request::param($path,TRUE),'file'=>Request::param($file,TRUE),'return'=>urlencode(Url::instance())),'auto')?>">
    <input class="file" type="hidden" name="tree[get][file]" /> 
    <input type="hidden" name="tree[get][dir]" value="<?=$path?>" /> 
    <input class="start" type="submit"/> 
</form>
<form style="display:none;" method="post" id="rename" action="/<?=Registry::i()->class_link?>/directory<?=Url::query(array('dir'=>Request::param($path,TRUE)),'auto')?>">
    <div class="box">
        <input class="old" type="hidden" name="old" /> 
        <input class="new" type="text" name="new" value="<?=$path?>" /> 
        <input class="start" name="rename" type="submit" value="Переименовать" />
        <div class="close"><span class="string">закрыть<span></div>
    </div>
</form>
<!--UNIX права -->
<form style="display:none;" id="UNIX" method="post">
    <input type="hidden" name="encode_name" />
    <input type="hidden" name="dir" value="<?=$path?>" />
    <table>
        <tr>
            <td style="padding-bottom:10px;" colspan="4"><span class="file_name">Изменение прав на: </span><span class="close-button">закрыть</span><div class="load"></div></td>
        </tr>
        <tr>
            <td width="100"></td>
            <td width="60">Чтение</td>
            <td width="60">Запись</td>
            <td width="60">Исполнение</td>
        </tr>
        <tr>
            <td>Владелец</td>
            <td><input class="ur" name="permission[]" type="checkbox" value="400" /></td>
            <td><input class="uw" name="permission[]" type="checkbox" value="200" /></td>
            <td><input class="ux" name="permission[]" type="checkbox" value="100" /></td>
        </tr>
        <tr>
            <td>Группа</td>
            <td><input class="gr" name="permission[]" type="checkbox" value="40" /></td>
            <td><input class="gw" name="permission[]" type="checkbox" value="20" /></td>
            <td><input class="gx" name="permission[]" type="checkbox" value="10" /></td>
        </tr>
        <tr>
            <td>Все</td>
            <td><input class="or" name="permission[]" type="checkbox" value="4" /></td>
            <td><input class="ow" name="permission[]" type="checkbox" value="2" /></td>
            <td><input class="ox" name="permission[]" type="checkbox" value="1" /></td>
        </tr>
        <tr>
            <td style="padding-top:10px;" colspan="4">Права: <span class="permission">000</span> <button name="UNIX" type="submit">Принять</button></td>
        </tr>
    </table>
</form>
<script type="text/javascript">
$("#file p .save").click(function(){
    var get = $(this).parent().find('.input').val();
    var $get = $("#get");
    
    $get.find('.file').val(get);
    $get.submit();
});
$("div p .rename").click(function(){
    var parent = $(this).parent();
    var get = parent.find('.input').val();
    var name = parent.find('.name').text();
    var $form = $("#rename");
    $form.css('display','block');
    $form.find('.old').val(get);
    $form.find('.new').val(name);
    // Закрытие окна
    $form.find('.close').click(function(){
        $form.css('display','none');
    });
});
/////////////////////////////////////////
var filemanager = {
    result_name : 'file_result',
    opacity_name : 'file_box',
    return_filename : '',
    return_size : '',
    root : 'D:',
    
    first : '#big_zip .big_zip',
    file_name : '#big_zip .file',
    
    url : '/media/ajax/filemanager/index.php',
    url_load : '/<?=Registry::i()->host?>/loadbar',
    
    init : function(){
        if(!(this).result){
            (this).result = $('<div id="'+(this).result_name+'"></div>').appendTo("body");
        }
        if(!(this).opacity){
            (this).opacity = $('<div id="'+(this).opacity_name+'" />').appendTo("body");
        }
        // Инициализируем функцию удаляения
        if((this).drop_init != true){
            (this).drop_init = true;
            var object = (this);
            (this).opacity.click(function(){
                object.drop()}
            );
        }
    },
    
    drop: function(){
        // указываем что бы загрузить снова
        (this).drop_init = false;
        
        // удаляем оэлемент и свойство
        (this).result.detach();
        delete (this).result;
        
        (this).opacity.detach();
        delete (this).opacity;
    },
    
    // для начального старта
    start : function(){
        var object = (this);
        $(object.first).click(function() {
            // инициализируем
            object.init();
            $.ajax({
                url: object.url,
                data: {
                    root: object.root
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    object.result.html(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
            });
        });
    },
    
    getFolder : function(folder){

        var object = (this);
        $.ajax({
                url: object.url,
                data: {
                    root: object.root, 
                    session_id : object.session_id, 
                    dir: folder,
                    original: object.original,
                    resize: object.resize
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    object.result.html(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
        });
    },
    getFile : function(canonical,file,size){
        if(!canonical){
            alert('Ошибка на странице');
            return;
        }
        var object = (this);
        // Сохраняем файл
        (object).return_filename = file;
        (object).return_size = size;
        
        (object).drop();
        (object).returns(canonical,file);
    },
    returns : function(canonical,file){
        var object = (this);
        $((object).first).val(canonical);
        $((object).file_name).val(file);
    },
    loadbar : function($result){
        var object = (this);
        $.ajax({
                url: object.url_load,
                data: {
                    dir : "<?=$_GET['dir']?>",
                    file : (object).return_filename,
                    size : (object).return_size
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    $result.html(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
        });
    }
}

filemanager.start();
function load($path,string){
    $($path).click(function(){
        $("body").append('<div style="position:fixed; top:0;bottom:0;left:0;right:0; background:#000;opacity:0.5;"></div><div style="position:fixed; top:45%;left:50%; font-size:20px; color:#fff;"><div style="margin-left:-50%;">'+string+'</div></div>');
    });
}

function load_bigZip($path,string){
    $($path).click(function(){
        $("body").append('<div style="position:fixed; top:0;bottom:0;left:0;right:0; background:#000;opacity:0.5;"></div><div style="position:fixed; top:45%;left:50%; font-size:20px; color:#fff;"><div style="margin-left:-50%;">'+string+'</div><div id="loadbar">Загружаем...</div></div>');
        var $result = $("#loadbar");
        setInterval(function(){filemanager.loadbar($result)},'1000');
    });
}
// Отображение загрузок
load_bigZip('#big_zip input[type=submit]','Загружаем Big ZIP');
load('#file_upload input[type=submit]','Загружаем файл');
load('#main_form input[name=archivate]','Идет архивация');
load('#main_form input[name=de_archivate]','Идет разархивация');
load('#bufer input[name=past]','Копирование файла');

// Для backup
$("#main_form input[name=backup]").click(function(){
    $("#main_form .checkbox").attr('checked', 'checked');
});

/*Подтверждение на некотрые файлы*/
$("#file .name").click(function(){
    var $text = $(this).text();
    if($text.match(/\.(zip|gz|bz2|rar|tag|jpg|jpeg|png|gif|bmp|tiff|ico|raw|psd|ecw|avi|mkv|mpg)$/i))
        return confirm('Открытие большого файла, продолжить?');
    return true;
});
var UNIX = {
    $UNIX : '',
    $save : '',
    $parent : '',
    file_name : '',
    encode_name : '',
    dir : '<?=$path?>',
    $permission : '',
    url_chmod : '/<?=Registry::i()->host?>/permission',
    init : function(){
        var object = this;
        (object).$UNIX = $('#UNIX');
        $("#main_form .UNIX").click(function(){
            (object).$parent = $(this).parent();
            (object).encode_name = (object).$parent.find(".input").val();
            (object).$permission = (object).$parent.find(".permission");
            (object).file_name = (object).$parent.find(".name").text();
            (object).$UNIX.css('display','block');
            (object).$UNIX.find('input[name="encode_name"]').val((object).encode_name);
            // Загрузка
            (object).$UNIX.find('.load').css('display','block');
            
            (object).$permission = (object).$UNIX.find(".permission");
            (object).$save = (object).$UNIX.find('.file_name').text();
            (object).$UNIX.find('.file_name').html("Изменение прав на: "+(object).file_name);
            // Очищаем поле
            (object).$UNIX.find('input[type="checkbox"]').each(function(){
                    $(this).prop("checked",false);
            });
            // Определения начального рарешения
            (object).chmods();
            // Событие формы
            (object).$UNIX.change(function(){
                var permission = 0;
                (object).$UNIX.find('input[type="checkbox"]').each(function(){
                    if($(this).prop("checked")){
                        permission += parseInt($(this).val());
                    }
                });
                permission = permission.toString();

                var length = permission.length;
                var chmod = '';
                for(var i = 3;i > 0;i--){
                    if(length == i){
                        chmod += permission;
                        break;
                    }
                    chmod += '0';
                        
                }
                (object).$permission.html(chmod);
            });
            // Закрытие окна
            (object).$UNIX.find('.close-button').click(function(){
                (object).$UNIX.css('display','none');
            });
        });
    },
    chmods : function(){
        var object = (this);
        $.ajax({
                url: (object).url_chmod,
                data: {
                    dir : (object).dir,
                    encode_name : (object).encode_name
                },
                dataType: 'text',
                type: 'POST',
                success: function(data){
                    (object).$UNIX.find('.load').css('display','');
                    (object).$permission.html(data);
                    (object).chekbox(data);
                },
                error: function(xhr, textStatus, errorThrown){
                    alert("Error: " +textStatus);
                }
        });
    },
    chekbox : function(permission){
        object = this;
        var chmod = new Array();
        var length = permission.length;
        
        if(length)
            for(var i = 0;i < length;i++){
                chmod[i] = new Array();
                var num = parseInt(permission[i]);
                if(num != 0){
                    if(num > 4){
                        chmod[i].push(4);
                        if(num > 5){
                            if(num > 6){
                                chmod[i].push(2);
                                chmod[i].push(1);
                            }else{
                                chmod[i].push(2);
                            }
                                
                        }else{
                            chmod[i].push(1);
                        }
                    }else{
                        if(num == 4){
                            chmod[i].push(4);
                        }else{
                            if(num == 3){
                                chmod[i].push(2);
                                chmod[i].push(1);
                            }else{
                                if(num < 2){
                                    chmod[i].push(1);
                                }else{
                                    chmod[i].push(2);
                                }
                            }
                        }
                    }
                }else{
                    chmod[i].push(0);
                }
            }
            
            chmod.forEach(function(elemnt,index){
                if(typeof elemnt == 'object')
                    elemnt.forEach(function(permission){
                        if(permission == 4){
                            var flag = 'r';
                        }
                        else if(permission == 2){
                            var flag = 'w';
                        }
                        else if(permission == 1){
                            var flag = 'x';
                        }
                        // опредляем какие подключать
                        if(index == 0){
                            if(flag)
                                var use = 'u';
                        }
                        else if(index == 1){
                            if(flag)
                                var use = 'g';
                        }else{
                            if(flag)
                                var use = 'o';
                        }
                        
                        if(flag && use)
                            (object).$UNIX.find('.'+use+flag).prop("checked",true)
                    });
            });
            
    }
}
UNIX.init();
</script>