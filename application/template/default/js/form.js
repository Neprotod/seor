var form = {
    arr_radio : [],
    imetation_select_open_focus : '',
    
    init : function(){
        this.radio();
    },
    radio : function(){
        var $radio = $("label.radio_controll input[type = radio]");
        var $label = $radio.parents(".radio_controll");
        $label.addClass("disable");
        $radio.filter(":checked").parents(".radio_controll").removeClass("disable").addClass("active");
        
        var object = this;
        $radio.each(function(){
            if(!object.arr_radio[this.name])
                object.arr_radio[this.name] = $radio.filter("[name = "+this.name+"]");
        });
        
        $radio.on("change.radio",function(){
            var $input = object.arr_radio[this.name];
            var $parent = $input.parents(".radio_controll");
            $parent.addClass("disable").removeClass("active");
            $(this).parents(".radio_controll").removeClass("disable").addClass("active");
        });
    },
    selected_input : {
        $sample : '',
        init : function($sample){
            var $sample = $($sample).clone();
            $sample.removeAttr("id");
            this.$sample = $sample;
            return this;
        },
        add : function($selection,$selected_box,name_input,remove = true) {
            var id = $($selection).val();
            if(id){
                var name = $($selection).find("option:selected").text();
                var spec = $($selected_box);
                var input = spec.find('input[value="'+id+'"]');
                
                if(input.length){
                    if(remove)
                        input.parents(".selected_input").remove();
                    return 1;
                }
                
                var clone = this.$sample.clone();
                
                clone.append('<input name="'+name_input+'['+id+']" type="hidden" value='+id+' />');
                clone.find(".string").text(name);
                spec.append(clone);
            }
        },
        close : function($obj) {
            if($($obj).hasClass("close")){
                $($obj).parents(".selected_input").remove();
            }
        }
    },
    readURL : function(input,$img) {
        if(input instanceof jQuery)
            input = input.get(0);
        
        if (input.files && input.files[0]){
            var reader = new FileReader();

            reader.onload = function(e) {
                $($img).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    },
    input_int : function ($input, default_int = 0,reg = /[^0-9]/gim) {
        $input = $($input);
        if($input.is("input")){
            var value = $input.val();
            value = value.replace(reg,'');
            if(value === 0){
                value = default_int;
            }
            $input.val(value);
        }
        return false;
    },
    /**
     * Проверят в событии на какой элемент было нажато
     *
     * @param object на какой элемент должны нажать
     * @param object событие
     */
    is_target : function (target, e, returns = false){
        var $target = $(target);
        if(!$target.is(e.target) && $target.has(e.target).length === 0){
            return false;
        }
        if(!returns)
            return true;
        
        if($target.is(e.target)){
            return $(e.target);
        }else{
            return $(e.target).parents(target);
        }
    },
    select_imitation_open : function($select){
        $select.addClass("focus");
        
        var val = $select.find("input").val();
        
        // Скролим
        this.select_imitation_scroll($select.find(".select_option[data-value='"+val+"']"));
        
        // Высота iput
        var height = $select.outerHeight();

        // высота экрана
        var heightBody = $('body').outerHeight();
        // отступ от экрана
        var defTop = $select.offset().top;
        
        var scrollTop = $('html').scrollTop();
        
        // Сколько осталось экрана сверху
        var topHeight = defTop - scrollTop;
        bottomHeight = (heightBody - height - defTop) + scrollTop;
        
        var $select_option_box = $select.find(".select_option_box");
        $select_option_box.attr("style",'');
        var to_height = 0;
        
        if(topHeight > bottomHeight){
            to_height = topHeight;

            $select_option_box.css({
                "bottom" : height+"px"
            });
        }else{
            to_height = bottomHeight;
            $select_option_box.css({
                "top" : height+"px"
            });
        }
        if(to_height > 442)
            to_height = 442;
        
        $select_option_box.css({
                "max-height" : to_height+"px"
            });
    },
    select_imitation_close : function($select){
        if(this.imetation_select_open_focus != ''){
            this.imetation_select_open_focus.removeClass("focus");
            this.imetation_select_open_focus = '';
        }
        
    },
    select_imitation_scroll : function($select){
        if($select.length != 0){
            var $parent = $select.parents(".select_imitation");
            var $select_option_box = $select.parents(".select_option_box");
            
            $parent.find(".select_option.select").removeClass("select");
            $select.addClass("select");
            var scrolls = $select.position().top;

            $select_option_box.scrollTop(scrolls);
        }
    },
    select_imitation : function(){
        var object = this;
        
        $(".select_imitation .select_option.select").each(function(){
            var $select_option_box = $(this).parents(".select_option_box");
            var scrolls = $(this).position().top;
            
            $select_option_box.scrollTop(scrolls);
        });
        
        $(document).on("click.select_imitation",function(e){
            var $select = '';
            if($select = object.is_target(".select_imitation .select_option",e,true)){
                var text = $select.text();
                var value = $select.data("value");
                
                var $parent = $select.parents(".select_imitation");
                
                $parent.removeClass("error_input");
                
                var input = $parent.find("input");
                var select_value = $parent.find(".select_value");
                
                var old_value = input.val();
                input.val(value);
                
                select_value.data("value",value);
                
                if(select_value.find(".value").length != 0)
                    select_value = select_value.find(".value");
                
                if($parent.data("type") == "val")
                    select_value.text(value);
                else if($parent.data("type") == "text")
                    select_value.text($select.data("text"));
                else
                    select_value.text(text);
                object.select_imitation_close();
                
                if(old_value != value){
                    if(input.get(0).events){
                        input.change();
                    }
                }
            }
            else if($select = object.is_target(".select_imitation",e,true)){
                if(object.imetation_select_open_focus != ''){
                    object.select_imitation_close();
                    return false;
                }
                object.imetation_select_open_focus = $select;
                
                object.select_imitation_open($select);
            }else{
                 object.select_imitation_close();
            }
        });
    }
}