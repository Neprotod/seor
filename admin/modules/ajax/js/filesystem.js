$("#filesystem_full #file_button").click(function(){
    var input = $("input[for="+$(this).attr("id")+"]");
    input.get(0).click();
    input.one("change", function(){
        var file = this.files[0];
        
        var ajax_file_add = new ajax.get();
        ajax_file_add.after(function(data){
            $('#modal_error .modal-body').html("<div class='text-success'>"+data.content+"</div>");
            $('#modal_error').modal("show");
            var url = $("#filesystem_full").attr("root");
            ajax.objects.file.query(url,{});
        },['data']);
        
        // Форма
        var data = ajax.for_pars(file);
        data.append('dir', $("#filesystem_full").attr("path"));

        ajax_file_add.query_form("/admin/ajax/router/filesystem/add",data);
    });
});
$("#filesystem_full #drop_file_button").click(function(){
    var url = $("#filesystem_full .select").attr("url");
    if(!url){
        $('#modal_error .modal-body').html("<div class='text-danger'>Ничего не выбрано</div>");
        $('#modal_error').modal("show");
    }else{
        if(confirm("Вы точно хотите удалить "+url+"?")){
            var drop_file = new ajax.get();
            
            drop_file.after(function(data){
                $('#modal_error .modal-body').html("<div class='text-success'>"+data+"</div>");
                $('#modal_error').modal("show");
                var url = $("#filesystem_full").attr("root");
                ajax.objects.file.query(url,{});
            },['data']);
            
            // Форма
            var data = {};
            
            data.dir = $("#filesystem_full").attr("path");
            data.drop = url;

            drop_file.query("/admin/ajax/router/filesystem/drop",data);
        }
    }
    
});
$("#filesystem_full #rename_file_button").click(function(){
    var url = $("#filesystem_full .select").attr("url");
    if(!url){
        $('#modal_error .modal-body').html("<div class='text-danger'>Ничего не выбрано</div>");
        $('#modal_error').modal("show");
    }else{
        var $modal_rename = $('#modal_name');
        var $input = $modal_rename.find("input");
        $input.val(url);
        $modal_rename.modal("show");
        var $button = $modal_rename.find("button.to_select");
        
        $button.off("click.rename");
        $button.off("click.create");
        
        $button.on("click.rename",function(){
            var old_name = url;
            var new_name = $input.val();
            var ajax_rename = new ajax.get();
            
            $modal_rename.modal("hide");
            
            ajax_rename.after(function(data){
                $('#modal_error .modal-body').html("<div class='text-success'>"+data+"</div>");
                $('#modal_error').modal("show");
                var url = $("#filesystem_full").attr("root");
                ajax.objects.file.query(url,{});
            },['data']);
            
            // Форма
            var data = {};
            
            data.old_name = old_name;
            data.new_name = new_name;
            data.dir = $("#filesystem_full").attr("path");

            ajax_rename.query("/admin/ajax/router/filesystem/rename",data);
        });
    }
});

$("#filesystem_full #dir_file_button").click(function(){
    var $modal_rename = $('#modal_create');
    var $input = $modal_rename.find("input");
    
    $modal_rename.one('hidden.bs.modal', function(){
        $input.val('');
    });

    $modal_rename.modal("show");
    
    var $button = $modal_rename.find("button.to_select");
    
    $button.off("click.rename");
    $button.off("click.create");
    
    $('#modal_error .modal-body')
    
    var $error = $('#modal_error');
    $button.on("click.create",function(){
        if(!$input.val()){
           $error.find('.modal-body').html("<div class='text-danger'>Пустое поле недопустимо</div>");
           $error.modal("show");
        }else{
            var ajax_create = new ajax.get();
            
            ajax_create.after(function(data,$error,$modal_rename,$input){
                if(data.error){
                    $error.find('.modal-body').html("<div class='text-danger'>"+data.error+"</div>");
                    $error.modal("show");
                    return false;
                }else{
                    $error.find('.modal-body').html("<div class='text-success'>"+data.content+"</div>");
                    $error.modal("show");
                    $modal_rename.modal("hide");
                    $input.val('');
                    var url = $("#filesystem_full").attr("root");
                    ajax.objects.file.query(url,{});
                }
            },['data',$error,$modal_rename,$input]);
            
            var data = {};
            
            data.new_dir = $input.val();
            
            data.dir = $("#filesystem_full").attr("path");
            
            ajax_create.query("/admin/ajax/router/filesystem/create_dir",data);
        }
        
    });
});

$("#filesystem_full tr").click(function(){
    $(this).parents("#filesystem_full").find(".select").removeClass("select bg_color");
    $(this).addClass("select bg_color");
});