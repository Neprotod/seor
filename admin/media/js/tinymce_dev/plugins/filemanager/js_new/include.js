var manager = {
    init : function(){
        //img_lightbox
        var object = this;

        $('.preview').click(function(){
            var data = $(this).data('url');

            $("#previewLightbox #img_lightbox").attr('src',data);
            return true;
        });
        
        //Запускаем функцию вставки
        $('.link').click(function(){
            var $file = $(this).parents('.file');
            var func =  $file.data('function');
            //Вызываем функцию
            (object)[func]($file);
        });
        //Переименование
        
        $('.rename-file').click(function(){
            var $rename = $('#rename');
            var $element = $(this);
            $rename.modal({
                show:true,
                backdrop:'static'
            });
            $rename.find('.alert .close').click(function(){
                $(this).parent().addClass('hide');
            });
            (object).rename($rename,$element);
        });
    },
    rename : function($rename,$element){
        $rename.find('.input-block-level').val($element.data('name'));
        var manager_dir = $('#manager_dir').val();
        var type = $element.data('type');
        var post = {
                dir:$element.data('dir'),
                type:type,
                path:$element.data('path'),
                charset:$element.data('charset')
            }
        if(type == 'file'){
            post.ext = $element.data('ext');
        }else{
            post.folder = $element.data('folder');
        }
        var $alert = $rename.find('.alert');
        
        //Событие на закрывание
        $rename.find('button[data-dismiss="modal"]').click(function(){
            $alert.addClass('hide');
        });
        
        $rename.find('.start').click(function(){
            var $btn = $(this).button('loading');
            var name = $rename.find('.input-block-level').val();
            post.name = name;;
            //if(start !== true){
            $.ajax({
                url: manager_dir+"ajax.php",
                type: "POST",
                data: post,
                dataType: "json",
                success: function(data){
                    if(data.verify == 'complete'){
                        $alert.attr('class','alert '+data.classes);
                        $alert.find('.massage').html(data.massage);
                        var $figure = $element.parents('figure');
                        
                        $element.data('path',data.path).attr('data-path',data.path);;
                        $element.data('name',data.name);
                        
                        var $link = $figure.find('.box .link,.box .folder-link');
                        if($figure.hasClass('file')){
                            $figure.data('path',data.path).attr('data-path',data.path);
                            $figure.data('name',data.name);
                            $figure.data('file',data.file);

                        }else{
                            var $all_link = $figure.find('.folder-link');
                            var default_link = $('#default_link').val();
                            var folder = "&folder="+data.folder+data.name;
                            
                            var href = default_link + folder;
                            
                            $all_link.each(function(){
                                $(this).attr('href',href);
                            });
                            
                        }
                        $link.text(data.name);
                        $rename.modal('hide');
                        window.setTimeout(function(){
                            $alert.addClass('hide');
                        },100);
                    }else{
                        $alert.attr('class','alert '+data.classes);
                        $alert.find('.massage').html(data.massage);
                    }
                    //start = false;
                    //$("#result").html(data);
                    $btn.button('reset');
                },
                error : function(){
                    $alert.attr('class','alert alert-danger');
                    $alert.html('Системная ошибка. Обратитесь к администратору.');
                    $btn.button('reset');
                }
            });
            /*}
            var start = true;*/
            //$btn.button('reset')
        });
        
    },
    apply_image : function($file){
        //Берем предыдущее окно
        var $w = this.get_win();

        var path = $file.data('path');

        var img = new Image();
        img.src = path;
        var width = img.width;
        var height = img.height;
        
        if($file.data('action') == 1){
            $w.find('.mce-img_content').val(path);
            
            $w.find('.mce-formitem').eq(2).find('.mce-textbox').each(function(){
                if($(this).hasClass('mce-first'))
                    $(this).val(width);
                else
                    $(this).val(height);
            });
        }else if($file.data('action') == 5){
            $w.find('.mce-video5_content').val(path);
        }
        //Закрываем окно
        this.close();
    },
    apply_video : function($file){
        //Берем предыдущее окно
        var $w = this.get_win();

        var path = $file.data('path');
        
        if($file.data('action') == 3){
            $w.find('.mce-video3_content').val(path);
        }
        else if($file.data('action') == 4){
            $w.find('.mce-video4_content').val(path);
        }
        //Закрываем окно
        this.close();
    },
    apply : function($file){
        //Берем предыдущее окно
        var $w = this.get_win();

        var path = this.encodeURL($file.data('path'));
        var name = $file.data('name');
    
        parent.tinymce.activeEditor.selection.setContent('<a href="'+path+'">'+name+'</a>');
        //Закрываем окно
        this.close();
    },
    close : function(){
        top.tinymce.activeEditor.windowManager.close();
    },
    get_win : function(){
        var win = parent.tinyMCE.activeEditor.windowManager.getWindows();
        win = win[0];
        var $w = $(win.getEl());
        return $w;
    },
    encodeURL : function (e) {
        for (var a = e.split("/"), t = 3; t < a.length; t++)
            a[t] = encodeURIComponent(a[t]);
        return a.join("/")
    },
}