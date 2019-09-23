<!-- Кропер -->
<?=$content?>
<script>
/*var sample = $("#sample").clone();
sample.removeAttr("id");*/

$("#input_salary").change(function(){
    form.input_int($(this));
});

var selected_input = form.selected_input.init("#sample");


$("#specialization_box, #language_box").click(function(e){
    selected_input.close(e.target);
});

$("#select_specialization").change(function(){
    $(this).removeClass("error_input");
    selected_input.add($(this),"#specialization_box","specialization");
    $(this).val('');
});
$("#select_language").change(function(e){
    selected_input.add($(this),"#language_box","language");
    $(this).val('');
});

$specialization_box = $("#specialization_box");


function button_submit_valid(){
    var leng = $specialization_box.find(".selected_input").length;
    if(leng == 0){
        $("#select_specialization").addClass("error_input");
    }else{
        $("#select_specialization").removeClass("error_input");
        return true;
    }
    return false;
}

$("button[name='submit']").click(function(){
    button_submit_valid();
});
$("form").submit(function(){
    if(!button_submit_valid()){
        return false;
    }
});

</script>