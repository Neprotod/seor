<script>
    function get_edit($id,lang,theme){
        
        if(!theme){
            theme = 'xcode';
        }
        // Если редактор включен
        var $source_textarea = $("#"+$id);

        $source_textarea.ace({ theme: theme, lang : lang});

        var decorator = $source_textarea.data("ace");
        
        $(".ace_editor").css('font-size','16.5px');
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