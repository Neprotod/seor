<?=$content?>
<script>
    form.init();
    var regist = {
        $employer : '',
        $select_company : '',
        $refinement : '',
        $regist_button : '',
        
        init : function(){
           this.$employer = $("#employer"); 
           this.$refinement = $("#refinement");
           this.$select_company = this.$refinement.find("select");
           this.$regist_button = $("#regist_button");
        },
        // Открывает поля для выбора типа лица
        show : function(){
            this.$employer.animate({height: "show"}, 500); 
        },
        // Закрывает поля для выбора типа лица
        hide : function(){
            this.$employer.animate({height: "hide"}, 500); 
        },
        // Открывает дополнительные поля для выбора компании
        show_company : function(){
            this.$refinement.animate({height: "show"}, 500); 
        },
        // Закрывает дополнительные поля для выбора компании
        hide_company : function(){
            this.$refinement.animate({height: "hide"}, 500); 
        },
        // Отключает кнопку
        disabled_button : function(){
            this.$regist_button.attr("disabled","disabled");
        },
        // Включает кнопку
        active_button : function(){
            this.$regist_button.removeAttr("disabled","");
        },
        // Проверяет нужно ли активировать кнопку
        select_company : function(){
            if(this.$select_company.val() != ''){
                this.active_button();
            }else{
                this.disabled_button();
            }
        }
    }
    
    regist.init();

    $radio = $("input[name=employer]");
    
    // Начальная проверка
    $radio.filter(":checked").each(function(){
        if($(this).val() == 1){
            regist.$employer.css("display","block");
            regist.disabled_button();
        }else{
            regist.active_button();
        }
    });
    
    // Включить или отключить кнопку и запустить дополнительную форму
    $radio.change(function(){
        if($(this).val() == 1){
            regist.show();
            regist.select_company();
        }else{
            regist.hide();
            regist.active_button();
        }
    });
    
    // Начальная проверка заполненности дополнительной формы
    var $select_face = $("#select_face");
    if($select_face.val() == "1"){
        regist.$refinement.css("display","block");
        regist.select_company();
    }
    else if($select_face.val() == '0'){
        regist.hide_company();
        regist.active_button();
    }
    
    $select_face.change(function(){
       if($(this).val() == '1'){
            regist.show_company();
            regist.select_company();
        }
        else if($(this).val() == '0'){
            regist.hide_company();
            regist.active_button();
        }else{
            regist.hide_company();
            regist.disabled_button();
        }
    });
    regist.$select_company.change(function(){
        if($(this).val() == ''){
            regist.disabled_button();
        }else{
            regist.active_button();
        }
    });
</script>