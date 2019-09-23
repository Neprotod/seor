<?=$content?>
<script>

    radio_controll.init();
    
    $(".static_input").click(function(){
        if($(this).is(":checked")){
            $url.attr("disabled","disabled");
        }else{
            $url.removeAttr("disabled");
        }
    });

    var ajax_file = new ajax.get();
    // Заполняет контент
    ajax_file.after(function(data){
        var root = data.root;
        
        $("#modal_file .error").text("");
        
        ajax_file.content("#modal_file",data.content);
    },["data"]);
    
    // Заполняет события.
    ajax_file.after(function(data){
        var root = data.root;
        $("#filesystem .dir").dblclick(function(){
            var url = $(this).find(".text .string").attr("url");
            
            var current = url;
           
            current = encodeURIComponent(current);
            
            ajax_file.query("/admin/ajax/router/media?root="+root+"&current="+current,{"file":false});
        });
        $("#filesystem .card").click(function(){
            $(this).parent().find(".select").removeClass("select bg_color");
            $(this).addClass("select bg_color");
        });
        $("#filesystem .breadcrumb a").click(function(){
            current = encodeURIComponent($(this).attr("href"));
            ajax_file.query("/admin/ajax/router/media?root="+root+"&current="+current,{"file":false});
            return false;
        });
    },["data"]);
    
    // Выбор папки или файла
    $("#modal_file .to_select").click(function(){
        var url = $("#filesystem .select .text .string").attr("url");
        
        if(!url){
            $("#modal_file .error").text("Ничего не выбрано");
        }else{
            $("#directory_input").val(url);
            $("#modal_file").modal("hide");
        }
    });
    
    $(".btn[data-target='#modal_file']").click(function(){
        ajax_file.query("/admin/ajax/router/media?root=flags",{"file":false});
    });
    
</script>