// Скрытие элементов
var card_header = {
    header : [],
    for_close : [],
    init : function(){
        
        var object = this;
        
        this.header = $(".card-hide  > .card-header");
        
        this.header.click(function(){
            $(this).parent().find(".for_close").slideToggle("fast");
        });
        
        // Скрывает или разворачивает все элементы (для кнопки)
        object.for_close = this.header.parent().find(".for_close");
        
        $("#for_close").click(function(){
            var data = $(this).data("curtail");
            var for_data = $(this).text();
            $(this).data("curtail",for_data)
            if(object.for_close.is(':visible')){
                $(this).text(data);
                object.for_close.slideUp("fast");
            }else{
                $(this).text(data);
                object.for_close.slideDown("fast");
            }
            //card_header.click();
        });
    }
}
var radio_controll = {
    init : function(){
        $(".radio_controll").each(function(){
            var input_controll = $(this).find(".input_controll");
            $(this).find(":checked").each(function(){
                var to_value = $(this).val();
                input_controll.parent().find("[to_value='"+to_value+"']").addClass("active");
                
            });
            input_controll.click(function(){
                input_controll.removeClass("active");
                $(this).addClass("active");
                
                var value = $(this).attr("to_value");
                
                $(this).parents(".radio_controll").find("input[value='"+value+"']").click();
            });
        });
    }
}




