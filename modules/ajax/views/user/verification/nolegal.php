<div id="error_fields">
    
</div>
<div id="validation_pole">
    <div>Паспортные данные</div>
    <div class="valid_row d-flex align-items-center">
        <div class="valid_col">
            Фамилия, имя и отчество
        </div>
        <div class="valid_col-2 flex-grow-1">
            <div class="form-group control position-relative">
                <input name="name" type="text" class="form-control" value="" active-role="field_specialty" required="required" placeholder="" />
            </div>
        </div>
    </div>
    <div class="valid_row d-flex align-items-center">
        <div class="valid_col">
            Дата рождения
        </div>
        <div class="valid_col-2 flex-grow-1">
            <?=$user["birthday"]?>
        </div>
    </div>
    <div class="valid_row d-flex align-items-center">
        <div class="valid_col">
            Серия и номер паспорта
        </div>
        <div class="valid_col-2 flex-grow-1">
            <div class="form-group control position-relative d-flex">
                <input name="serial" type="text" class="form-control input_small margin-right-small" value="" active-role="field_specialty" required="required" placeholder="" />
                <input name="number" type="text" class="form-control input_middle" value="" active-role="field_specialty" required="required" placeholder="" />
            </div>
        </div>
    </div>
    <div class="valid_row d-flex align-items-center">
        <div class="valid_col">
            Дата выдачи
        </div>
        <div class="valid_col-2 flex-grow-1">
            <div class="form-group control position-relative d-flex">
                <input name="date" type="text" class="form-control input_middle" value="" active-role="field_specialty" required="required" placeholder="" />
            </div>
        </div>
    </div>
    <div class="valid_row d-flex align-items-center">
        <div class="valid_col">
            <div class="">Кем выдан</div>
            <div class="signature">(дословно по паспорту)</div>
        </div>
        <div class="valid_col-2 flex-grow-1">
            <div class="form-group control position-relative d-flex">
                <input name="issued" type="text" class="form-control" value="" active-role="field_specialty" required="required" placeholder="" />
            </div>
        </div>
    </div>
    <div class="valid_row d-flex">
        <div class="valid_col">
            <div class="alert alert-success">Необходим скан начальной страницы вашего паспорта с основными данными для подтверждения вашей личности.</div>
        </div>
        <div class="valid_col-2 flex-grow-1">
            <button id="scan_down" type="button" class="btn btn-outline-light">
                <div>Загрузить скан</div>
                <table id="file_sample">
                    <tr class="file_input">
                        <td>
                            <span class="name"></span>
                            <input name="" type="file" class="form-control d-none" value="" active-role="field_specialty" required="required" placeholder="" />
                        </td>
                        <td>
                            <div class="drop_input">×</div>
                        </td>
                    </tr>
                </table>
            </button>
            <table class="file_inputs position-relative margin-top">
                
            </table>
        </div>
    </div>
</div>

<script>
$("#scan_down").click(function(){
    $(this).find("input").get(0).click();
});


$("#scan_down input").change(function(){
    input = $(this).get(0);
    
    var parent = $(this).parents(".file_input");
    
    parent.find(".name").text(input.files[0]["name"]);
   
    var clone = parent.clone();
    
    var count = $(".file_inputs tr").length;
    
    clone.find("input").attr("name","file["+count+"]");
    
    $(".file_inputs").append(clone);
});

$(".file_inputs").click(function(e){
    var $target;
    if($target = form.is_target(".drop_input",e, true)){
        $target.parents("tr").remove();
    }
});

$("#technical_modal .modal_submit").on("click",function(){
    data = new FormData();
    $(".file_inputs input").each(function(){
        data = ajax.form_file($(this),$(this).attr("name"),data);
    });
    
    $("#validation_pole input[type='text']").each(function(){
        var name = $(this).attr("name");
        var val = $(this).val();
        data.append(name, val);
    });
    
    var ajax_file_add = new ajax.get();
    ajax_file_add.after(function(data,$error_fields){
        if(data.error){
            $error_fields.html(data.error);
            $('#technical_modal').scrollTop(0);
            return false;
        }
        location.reload();
    },['data',$("#error_fields")]);
    
    ajax_file_add.query_form("/ajax/user/user_valid_save",data,true);
});
</script>