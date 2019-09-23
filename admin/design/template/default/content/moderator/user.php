<?=$menu?>
<?=$content?>
<script>
    card_header.init();
    //Что показывать.
    $("input[name='status_control']:first").each(function(){
        var danger = $(".alert-danger.permission");
        var primary = $(".alert-primary.permission");
        
        if(!parseInt($(this).val())){
            danger.addClass("show");
        }else{
            primary.addClass("show");
        }
        
        $(this).change(function(){
            if(parseInt($(this).val())){
                /*primary.addClass("show");
                danger.removeClass("show");*/
                danger.slideUp("fast");
                primary.slideDown("fast");
            }else{
                /*danger.addClass("show");
                primary.removeClass("show");*/
                danger.slideDown("fast");
                primary.slideUp("fast");
            }
        });
    });
    
    //Определяем checkbox.
    $(".alert-danger.permission, .alert-primary.permission").each(function(){
        var method = $(this).find(".method");
        
        var test = $(this).hasClass("alert-danger");

        method.each(function(){
            var rule = $(this).find(".rule_checkbox");
            var perm = $(this).find(".method_checkbox");
            if(test){
                if(perm.is(":checked")){
                    rule.attr("disabled","disabled")
                }
            }else{
                if(!perm.is(":checked")){
                    rule.attr("disabled","disabled")
                }
            }
            
            
            perm.get(0).ruleElem = rule;
            perm.click(function(){
                var rule = $(this).get(0).ruleElem;
                if(rule.attr("disabled")){
                    rule.removeAttr("disabled");
                }else{
                    rule.attr("disabled","disabled");
                }
            });
        });
    });
    
    var $card_checker = $("#card_checker input");
    $card_checker.change(function(){
        var val = $(this).val();
        if(val == 1){
            $("#user_group .group_box").removeClass("border-danger");
            $("#user_group .group_box").addClass("border-success");
        }else{
            $("#user_group .group_box").removeClass("border-success");
            $("#user_group .group_box").addClass("border-danger");
        }
    });
    $card_checker.change();
    
    var $pass = $("input[name='moder[pass]']");
    var $pass_check = $("input[name='moder[pass_check]']");
    var $pass_controll = true;
    $pass.change(function(){
        var val = $(this).val();
        var pattern = /^[a-zA-Z0-9]{5,}$/;
        var flag = false;
        if(val){
            if(pattern.test(val)){
                flag = true;
            }
            if(flag){
                $pass_controll = true;
                $(this).removeClass("is-invalid");
                $(this).addClass("is-valid");
            }else{
                $pass_controll = false;
                $(this).removeClass("is-valid");
                errors.commit($(this),"error","is-invalid","danger","Минимум 5 символов, латинские буквы или(и) цифры.");
            }
        }else{
            $pass_controll = true;

            $(this).removeClass("is-valid");
            $(this).removeClass("is-invalid");
        }
        
    });
    $pass_check.change(function(){
        var val = $(this).val();
        var pattern = /^[a-zA-Z0-9]{5,}$/;
        
        var flag = false;
        if(val){
            if(pattern.test(val)){
                if($pass.val() === val)
                    flag = true;
            }
            if(flag){
                $pass_controll = true;
                $(this).removeClass("is-invalid");
                $(this).addClass("is-valid");
            }else{
                $pass_controll = false;
                $(this).removeClass("is-valid");
                errors.commit($(this),"error","is-invalid","danger","Пароль не совпадает.");
            }
        }else{
            $pass_controll = true;
            $(this).removeClass("is-valid");
            $(this).removeClass("is-invalid");
        }
    });
    
    $("input[name='moder[display_name]']").change(function(){
        var val = $(this).val();
        var pattern = /^[а-яА-Яa-zA-Z0-9 _-]{3,}$/;
        
        if(pattern.test(val)){
            $(this).removeClass("is-invalid");
            $(this).addClass("is-valid");
        }else{
             $(this).removeClass("is-valid");
            errors.commit($(this),"error","is-invalid","danger","Имя должно быть не меньше 3 символов.");
        }
    });
    $("input[name='moder[login]']").change(function(){
        var val = $(this).val();
        var pattern = /^[a-zA-Z0-9]{3,}$/;
        
        if(pattern.test(val)){
            $(this).removeClass("is-invalid");
            $(this).addClass("is-valid");
        }else{
             $(this).removeClass("is-valid");
            errors.commit($(this),"error","is-invalid","danger","В логине могут быть только буквы и цифры.");
        }
    });
    $("input[name='moder[email]']").change(function(){
        var val = $(this).val();
        var pattern = /^.{0,}@.{0,}\..{0,}$/;
        
        if(pattern.test(val)){
            $(this).removeClass("is-invalid");
            $(this).addClass("is-valid");
        }else{
             $(this).removeClass("is-valid");
            errors.commit($(this),"error","is-invalid","danger","Не правильный формат почты.");
        }
    });
    
    $("form").submit(function(){
        if($(".is-invalid").length > 0){
            return false;
        }
    });
    
    
    $(".new_checkbox").each(function(){
        var input = $(this).find("input");
        if(input.is(':checked'))
            $(this).addClass("active");
        input.click(function(){
            $(this).parent(".new_checkbox").toggleClass("active");
        });
    });
    
    $("select").change(function(){
        if($(this).val() == ''){
            $(this).addClass("is-invalid");
            $(this).removeClass("is-valid");
        }else{
            $(this).removeClass("is-invalid");
            $(this).addClass("is-valid");
        }
    });
</script>