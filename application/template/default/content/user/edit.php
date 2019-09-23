
 <!--Определяем  устройство -->
<script type="text/javascript" src="/media/js/current-device.min.js"></script>

<!-- Кропер -->
<link rel="stylesheet" type="text/css" href="/media/css/imgareaselect/imgareaselect-default.css" />
<!--<script type="text/javascript" src="/media/js/imgareaselect/jquery.min.js"></script>
<script type="text/javascript" src="/media/js/imgareaselect/jquery.imgareaselect.pack.js"></script>-->
<script type="text/javascript" src="/media/js/imgareaselect/jquery.imgareaselect.js"></script>
<!--<script type="text/javascript" src="/media/js/imgareaselect/jquery.imgareaselect.mob.js"></script>-->

<div class="modal fade" id="modal_logo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование логотипа</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&#215;</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal_download">Загрузить картинку</button>
                <button type="button" class="btn btn_green modal_submit">Применить</button>
            </div>
        </div>
    </div>
</div>
<?=$content?>
<script>
// В телефонах нельзя писать пробелы
$("#phone_box").on("keyup",function(e){
    var $input = '';
    if($input = form.is_target("input",e,true)){
        form.input_int($input,'');
    }
});

$(".birthday_day, .birthday_month").on("keyup",function(){
    if($(this).attr("maxlength") == $(this).val().length){
        $(this).next(".birthday").focus();
    }
});

$("#contact_inputs_box").click(function(e){
    var $target = $(e.target);
    if($target.hasClass("minus_icon")){
        // Удаляем
        $target.parents(".form-group").remove();
    }
    else if($target.hasClass("plus_icon")){
        // Добавляем
        // Клонируем для переписи
        var $clone = $target.parents(".form-group").clone();
        
        $clone.addClass("new");
        
        $clone.find(".plus_icon").removeClass("plus_icon").addClass("minus_icon");
        var $phone_number = $clone.find(".phone_number");
        var $select_imitation  = $clone.find(".select_imitation");
        var $select_imitation_input  = $select_imitation.find("input");
        
        $select_imitation.find(".select").removeClass("select");
        
        var count = $(".form-group", "#phone_box").length + 1;
        
        $phone_number.val("");
        $phone_number.attr("name", "phone[new]["+count+"][phone]");
        $select_imitation_input.attr("name", "phone[new]["+count+"][id_country_code]");
        
        $select_imitation.find(".select_value .value").text($select_imitation.data("default"));
        $select_imitation.find("input").val('');
        // Заменяем имя, добавляя количество элементов
        /*var name = $input.attr("name");
        var reg = /[a-zA-Z\[\]]{1,}\[/;
        name = reg.exec(name);
        name +=  $(this).find(".input_phone").length + "]";
        $input.attr("name", name);*/
        
        //$input.val("");
        //$(this).find(".input_phone:last").affter($clone);
        $(this).find(".input_phone:last").after($clone);
        //$clone.find("input");
        //alert($(this).find(".input_phone").length);
    }
});


/*КРОПЕР*/
var $modal_logo = $("#modal_logo");

/*
if(!$("html").hasClass("desktop")){
    $modal_logo.modal("show");
    $modal_logo.find(".modal-body").html("<div>Редактировка логотипа, доступна только на ПК</div><div>На мобильном устройстве, вы можете только загрузить картинку разером 100 на 100 пикселей.</div>");
    return false;
}
*/

if($("html").hasClass("desktop")){
    $("#company_logo").click(function(){
        
        //$("#input_image").get(0).click();
        
        
       // $modal.modal("show");
        
        if($(this).data("logo")){
            // Логотипа нет, начать загрузку файла
            $("#input_image").get(0).click();
        }else{
            $('#image_crop').imgAreaSelect({remove : true});
            // Создаем Ajax запрос
            var get = new ajax.get();
            
            get.after(function(data,$modal_logo){
                if(data.error){
                    alert("Ошибка на сайте.");
                }else{
                    $modal_logo.modal("show");
                    $modal_logo.find(".modal-body").html(data.content);
                }
                
            },['data',$modal_logo]);
            
            get.query("/ajax/image/logo",{"default":1});
        }
        
        
    });
}else{
    $("#company_logo").click(function(){
        $modal_logo.modal("show");
        $modal_logo.find(".modal-body").html("<div><b>Редактировка логотипа, доступна только на ПК.</b></div><div class='margin-top'>На мобильном устройстве, вы можете только загрузить картинку разером 100 на 100 пикселей.</div>");
    });
}
$modal_logo.find(".modal_download").click(function(){
     $("#input_image").get(0).click();
});

$("#input_image").change(function(){
    // Создаем ajax запрос, и сохраняем tmp файла
    var ajax_file_add = new ajax.get();
    $modal_logo.find(".modal_submit").off();
    $('#image_crop').imgAreaSelect({remove : true});
    ajax_file_add.after(function(data,$modal_logo){
        if(data.error){
            alert(1);
        }else{
            $modal_logo.modal("show");
            if($("html").hasClass("desktop")){
                $modal_logo.find(".modal-body").html(data.content);
            }else{
                $modal_logo.find(".modal-body").html(data.content+"<div class='margin-top'>Нажмите \"Применить\", что бы сохранить картинку.</div>");
            }
        }
        
    },['data',$modal_logo]);
    
    var data = ajax.form_file(this.files[0]);
    
    
    ajax_file_add.query_form("/ajax/image/logo",data);
});

// Обработчик специализаций

var selected_input = form.selected_input.init("#sample");


$("#specialization_box, #language_box").click(function(e){
    selected_input.close(e.target);
});

$("#select_specialization").change(function(){
    $(this).removeClass("error_input");
    $(this).parent().removeClass("error_max");
    selected_input.add($(this),"#specialization_box","specialization");
    if($("#specialization_box > *").length > 4){
        $(this).addClass("error_input");
        $(this).parent().addClass("error_max");
        selected_input.add($(this),"#specialization_box","specialization");
    }
    $(this).val('');
});
$("#select_language").change(function(e){
    selected_input.add($(this),"#language_box","language");
    $(this).val('');
});

$specialization_box = $("#specialization_box");


function button_submit_valid(){
    if($specialization_box.length != 0){
        var leng = $specialization_box.find(".selected_input").length;
        if(leng == 0){
            $("#select_specialization").addClass("error_input");
        }else{
            $("#select_specialization").removeClass("error_input");
            return true;
        }
        return false;
    }else{
        return true;
    }
}

$("button[name='submit']").click(function(){
    button_submit_valid();
});
$("form").submit(function(){
    if(!button_submit_valid()){
        return false;
    }
});
form.select_imitation();


// Проверим, все ли телефоны правильно заполнены
$("form").submit(function(){
    var error = false;
    
    // Проверка
    if($(".error_input, .error_max").length > 0)
        error = true;
    
    $(".select_imitation input").each(function(){
        var val = $(this).val();
        
        if(val == ''){
            error = true;
            $(this).parents(".select_imitation").addClass("error_input");
        }
    });

    if(error){
        return false;
    }else{
        return true;
    }
});
</script>