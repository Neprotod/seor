
<?php
    /*$bas = base64_decode("eyJ2ZXJzaW9uIjozLCJhY3Rpb24iOiJwYXkiLCJwdWJsaWNfa2V5IjoiaTUwNDA5MjExNDk5IiwiYW1vdW50IjoiNTAwIiwiY3VycmVuY3kiOiJVQUgiLCJkZXNjcmlwdGlvbiI6ItCX0LAg0LjQvdGE0L7RgNC80LDRhtC40L7QvdC90YvQtSDRg9GB0LvRg9Cz0LgiLCJ0eXBlIjoiYnV5Iiwic2FuZGJveCI6IjEiLCJyZXN1bHRfdXJsIjoiaHR0cHM6Ly9zZW9yLnVhL2FjY291bnQvcGF5L2NvbXBsZWF0ZSIsImxhbmd1YWdlIjoicnUifQ==");
    echo "<pre>";
    var_dump(json_decode($bas));
    exit;
    exit;*/
?>
<?=$content?>
<script>
    $(".pay_box").click(function(){
        $(this).find("input[type='submit']").get(0).click();
    });
    
    form.select_imitation();
    
    var $rate = $(".select_value","#converter");
    
    var $form_seor_count    = $("#form_seor_count input");
    var $form_seor_currency = $("#form_seor_currency input");
    
    var $form_seor_bonus = $("#form_seor_bonus .amount");
    
    var $input_currency = $("input[name='currency_type']","#converter");
    
    var $target = '';
    
    function seor_rate(){
        var rate = $rate.text().replace(/[^0-9\.]/gim,'');
                
        form.input_int($form_seor_count);
        var val = $form_seor_count.val();
        
        if(val != 0)
            var bonus = Math.floor(val / 50) * 5;
        else
            var bonus = 0;
        var cur = val * rate;
        
        cur = new String(cur).replace(/[^0-9]\./,'');
        
        cur = cur.split(".");
        
        if(cur[1]){
            cur = cur[0] +"."+ cur[1].slice(0,2);
        }
        
        $form_seor_currency.val(cur);
        
        $form_seor_bonus.text(bonus);
        
    }
    
    // Конвертируем валюты.
    $("#form_seor_count input, #form_seor_currency input").focus(function(){
        $target = $(this);
        if($(this).parent().attr("id") == "form_seor_count"){
            $(this).on("input",function(){
                seor_rate();
            });
        }else{
            $(this).on("input",function(){
                var rate = $rate.text().replace(/[^0-9\.]/gim,'');
                
                form.input_int($(this),0,/[^0-9\.]/gim);
                var val = $(this).val();
                
                cur = Math.floor(val / rate);
                
                $form_seor_count.val(cur);
            });
        }
    });
    
    $form_seor_currency.change(function(){
        seor_rate();
    });
    
    $("#form_seor_count input, #form_seor_currency input").blur(function(){
        $target.off("input");
    });
    
    $input_currency.change(function(){
        seor_rate();
        $(".caption_seor span","#form_seor_currency").text($(this).val());
    });
    
    $input_currency.get(0).events = 1;
    
    
    // Проверка формы.
    $("#form_coin").submit(function(){
        var val = $form_seor_count.val();
        if(val == '' || val == 0){
            $(this).find(".error_msg").text("Вы не укзаали количество.");
            $form_seor_count.one("input.error", function(){
                $("#form_coin").find(".error_msg").text("");
            });
            return false;
        }
    });
    
</script>