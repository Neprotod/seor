<script src='/media/js/tinymce/tinymce.min.js'></script>
<?=$content?>
<script>
    $(".modal").dblclick(function(e){
        var $target = $(".file");
        if(!$target.is(e.target) && $target.has(e.target).length === 0){
            return false;
        }
        $parent = $(e.target).parents(".modal");
        
        $parent.find(".btn.to_select").click();
    });
    card_header.init();
    radio_controll.init();
    
    var ajax_file = new ajax.get();
    var ajax_category = new ajax.get();
    var ajax_parent_category = new ajax.get();
    var ajax_modal_filesystem = new ajax.get("file");
    var update_fileds = new ajax.get("fileds");
    
    ajax_modal_filesystem.after(function(data){
        var root = data.root;
        
        $("#modal_filesystem .error").text("");
        
        ajax_category.content("#modal_filesystem",data.content);
    },["data"]);
    
    ajax_modal_filesystem.after(function(data){
        var root = data.root;
        $("#filesystem_full .dir").dblclick(function(){
            var url = $(this).attr("url");
            
            var current = url;
           
            current = encodeURIComponent(current);
            
            ajax_modal_filesystem.query("/admin/ajax/router/filesystem?root="+root+"&current="+current,{"file":false});
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
        $("#modal_filesystem .breadcrumb a").click(function(){
            current = encodeURIComponent($(this).attr("href"));
            ajax_modal_filesystem.query("/admin/ajax/router/filesystem?root="+root+"&current="+current,{"file":false});
            return false;
        });
    },["data"]);
    
    
    ajax_category.after(function(data){
        $("#modal_category .error").text("");
        
        ajax_category.content("#modal_category",data.content);
        
        var $page_table = $(".page_table.ajax");
        
        $page_table.find("tr[id_category]").click(function(){
            $page_table.find(".active").removeClass("active select");
            $(this).addClass("active select");
        });
    },["data"]);
    
     // Выбор папки или файла
    $("#modal_category.category .to_select").click(function(){
        var category = $("#modal_category .select");
        var id = category.attr("id_category");
        var title = category.attr("title");

        if(!category){
            $("#modal_category .error").text("Ничего не выбрано");
        }else{
            var parent_url = ajax_parent_category.query("/admin/ajax/router/category/parent_category/"+id,{},false);
            $("#url_parent").text(parent_url);
            $("#category_input").val(id);
            $("#category_name").text(title);
            $("#modal_category").modal("hide");
        }
    });
    // Работа с файлами
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
            var root = "/media/" + $("input[name='fields[parent][image_path]']").val() + "/" + url;
            
            $("#image_flag").attr("src",root);
            
            $("#directory_input").val(url);
            $("#image_string").text(url);
            $("#modal_file").modal("hide");
        }
    });
    $(".btn[data-target='#modal_file']").click(function(){
        var root = $("input[name='fields[parent][image_path]']").val();

        ajax_file.query("/admin/ajax/router/media?root=" + root,{file:true});
    });
    
    $(".btn[data-target='#modal_category']").click(function(){
        var data = {"id":$("#category_input").val()}

        ajax_category.query("/admin/ajax/router/category",data);
    });
    $(".btn[data-target='#modal_filesystem']").click(function(){
        ajax_modal_filesystem.query("/admin/ajax/router/filesystem?root=vis",{});
    });
    
    // Заполняем с title
    var $vis_image = $("#vis_image");
    var $vis_image_h2 = $("#vis_image").find(".h2");
    $("input[name='page[title]']").change(function(){
        $vis_image_h2.text($(this).val());
    });
    var $input_image_file = $("#input_image_file");
    
    $vis_image.click(function(){
        $input_image_file.click();
    });
    
    
    
    var path = "media/vis";
    $input_image_file.change(function(){
        var file = this.files[0];
        var data = ajax.for_pars(file);
        data.append('dir', path);
        
        var ajax_file_add = new ajax.get();
        ajax_file_add.after(function(data,$input_image_file,$vis_image){
            var ajax_fields_add = new ajax.get();
            
            ajax_fields_add.after(function(data,$vis_image){
                if(data.id){
                    $vis_image.attr("id_fields",data.id);
                }else{
                    alert("Не вернулся id");
                }
                
            },['data',$vis_image]);
            
            var category = $("#category");
            
            var fields = {}
            fields.id = $vis_image.attr("id_fields");
            fields.id_table = category.attr("id_table");
            fields.id_type = category.attr("id_type");
            fields.name = "image";
            fields.var = data.file[0].replace(/\\/g,"/");
            fields.where = "var";

            $vis_image.find(".image").attr("src","/"+fields.var);
            ajax_fields_add.query("/admin/ajax/router/fields/add_fields",fields);
        },['data',$(this),$vis_image]);
        
        ajax_file_add.query_form("/admin/ajax/router/filesystem/add",data);
    });
    
    $("#modal_filesystem .btn.to_select").click(function(){
        var $parent = $(this).parents(".modal");
        $parent.modal("hide");
        var url = $parent.find(".select").attr("url");
        var dir = $parent.find("#filesystem_full").attr("dir");
        
        var ajax_fields_add = new ajax.get();
            
        ajax_fields_add.after(function(data,$vis_image){
            if(data.id){
                $vis_image.attr("id_fields",data.id);
            }else{
                alert("Не вернулся id");
            }
            
        },['data',$vis_image]);
            
        var category = $("#category");
            
        var fields = {}
        fields.id = $vis_image.attr("id_fields");
        fields.id_table = category.attr("id_table");
        fields.id_type = category.attr("id_type");
        fields.name = "image";
        fields.var = dir+url;
        fields.where = "var";

        $vis_image.find(".image").attr("src","/"+fields.var);
        ajax_fields_add.query("/admin/ajax/router/fields/add_fields",fields);
    });
    
    function input_generate(){
        $(this).parent().find(".buffer").text($(this).val());
    }
    
    $(".input_generate").on("input",input_generate);
    
    // Создание 
    var $table_generator = $("#table_generator");
    
    $table_generator.find(".table_add").click(function(){
        var table = $(this).parent().find("table");
        
        var tr_all = table.find("tr");
        var tr = tr_all.first();
        
        var id_row = tr_all.length + 1;
        var table_id = table.attr("id_table");
        
        var clone_tr = tr.clone();
        clone_tr.each(function(){
            var add = '<td class="no_row" colspan="2"></td>';
            var controll = $(this).find(".table_minus, .table_plus");
            if(controll.length != 0){
                controll.remove();
            }
            $(this).append(add);
            var input = $(this).find("input");
            input.val('');
            input.on("input",input_generate);
            
            $(this).attr("id_row",id_row);
            
            input.attr("name","params["+table_id+"]["+id_row+"][]")
            
        });
        table.append(clone_tr);
    });
    
    $table_generator.find(".table_plus").click(function(){
        var table = $(this).parents("table");
        var tr_all = table.find("tr");
        var td = tr_all.first().find("td.child").first();
        
        var clone = td.clone();
        clone.find("input").val('');
        clone.find(".buffer").text('');

        var table_id = table.attr("id_table");
        
        tr_all.each(function(){
            var append = $(this).find(".child").last();
            var clone_append = clone.clone();
            
            var id_row = $(this).attr("id_row");

            var input = clone_append.find("input");
            input.on("input",input_generate);
            
            input.attr("name","params["+table_id+"]["+id_row+"][]");
            
            append.after(clone_append);
        });
        
    });
    
    $table_generator.find(".table_minus").click(function(){
        var table = $(this).parents("table");
        var tr_all = table.find("tr");
        
        // Тест
        if(tr_all.first().find("td.child").length == 1){
            return false;
        }
        
        tr_all.each(function(){
           $(this).find("td.child").last().remove();;
        });
        
    });
    
    function input_empty(){
        if($(this).val() == ''){
            var parent = $(this).parents("li");
            var next = parent.next();
            if(next.length != 0){
                if(next.find("input").val() == ''){
                    parent.remove();
                }
            }
        }
    }
    
    function input_add(){
        var parent = $(this).parents("li");
        var clone = parent.clone();
        clone.find("input").val('');
        parent.after(clone);
        clone.find("input").one("input.add",input_add);
        clone.find("input").on("input.empty",input_empty);
        clone.find("input").on("input",input_generate);
    }
    
    $("#li_generator input").last().one("input.add",input_add);
    $("#li_generator input").on("input.empty",input_empty);
    
    tinymce.init({
        selector: '#content_area',
        language: 'ru',
        plugins: [
          'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
          'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
          'save table contextmenu directionality emoticons template paste textcolor'
        ],
        content_css: 'css/content.css',
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview media fullpage | forecolor backcolor'
        });
</script>