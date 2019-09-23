<?=$content?>
<script>
var search = {
    $fake_input : '',
    $spread_box : '',
    $target : '',
    $_GET : {},
    $content : '',
    reset_key : ['page'],
    expiration : [],
    timer_id : '',
    init : function(){
        var object = this;
        
        this.$_GET = ajax._get();
        
        this.$content = $("#container_ads");
        
        this.$fake_input =  $(".fake_input", "#management_tool");
        this.$spread_box =  this.$fake_input.find(".spread_box");
        
        this.fake_input_count();
        $(document).on("click.fake_input",function(e){
            var target = object.$fake_input;

            if (object.is_target(target, e)){
                if(target.is(e.target)){
                    var $fake_input = $(e.target);
                }else{
                    var $fake_input = $(e.target).parents(".fake_input");
                }
                
                // Если открывается другой объект и есть еще один открытый
                if(object.$target && $(object.$target).get(0) != $fake_input.get(0)){
                    object.fake_input_close(target);
                }
                
                var $spread_box = $fake_input.find(".spread_box");
                if(!object.is_target($spread_box, e)){
                    if($spread_box.hasClass("d-none")){
                        object.fake_input_open($fake_input);
                        $spread_box.removeClass("d-none");
                        // Для того, что бы убрать ситуацию двух открытых окон
                        object.$target = $fake_input;
                    }else{
                        object.fake_input_close(target);
                    }
                }
            }else{
                object.fake_input_close(target);
            }
            
            if(object.is_target($(".close","#fiter_inside"), e)){
                var $filter_item = $(e.target).parents(".filter_item");
                var type = $filter_item.data("type");
                var id = $filter_item.data("id");
                var find = $("div[data-name='"+type+"'] .spread_info *[data-id='"+id+"']", "#management_tool");
                find.data("active",0);
                object.fake_input_count();
                object.request();
                
            }
        });
        
        // Для внутренних элементов
        this.$fake_input.find(".spread_box").click(function(e){
            // Обрабатываем клик по элементу
            var $elem = $(e.target);
            
            var $btn = $(this).find("button");
            
            if($elem.hasClass("elem")){
                var $spread_info = $elem.parents(".spread_box").find(".spread_info");
                var id = $elem.data("id");
                var element_info = $spread_info.find("*[data-id='"+id+"']");
                
                if(!id){
                    element_info.data("active",'1');
                    object.fake_input_close($(this).parents(".fake_input"));
                    return false;
                }
                
                if($elem.hasClass("active")){
                   element_info.data("active",'0');
                }else{
                    element_info.data("active",'1');
                }
                $elem.toggleClass("active");
            }else{
                if(object.is_target($btn,e)){
                    object.fake_input_close($(this).parents(".fake_input"));
                }
            }
        });
        
        // Изменение формы
        $('input','#filter_box').change(function(){
            var $filter_input = $(this).parents(".filter_input");
            
            
            // Определяем input, это цена минимальная или максимальная
            var key = $(this).data("name");
            var value = $(this).val();
            
            var val = '';
            
            if($(this).data("type") == 'price'){
                value = value.replace(/[^0-9]/gim,'');
                
                if(value == 0){
                    value = '';
                }
                
                $(this).val(value);
                
                var $select = $filter_input.find("select");
                
                if(key == 'price_from'){
                    // Надо стравнить с to
                    var $to = $filter_input.find("input[data-name='price_to']");
                    var val = parseInt($to.val());
                    
                    if(val != '' && val < value){
                        object.set_string('price_to',value);
                        $to.val(value);
                    }
                }else{
                    // Надо стравнить с to
                    var $from = $filter_input.find("input[data-name='price_from']");
                    var val = parseInt($from.val());

                    if((val != '' && value != '') && val > value){
                        //object.set_string('price_from',val);
                        value = val;
                        $(this).val(val);
                    }
                }
                object.set_string(key,value);
                
                // определяем валюту
                if(!object.empty(value) || !object.empty(val)){
                    object.set_string('currency', $select.val());
                }else{
                    if(object.$_GET['currency']){
                        delete object.$_GET['currency'];
                    }
                }
            }
            object.request();
        });
        
        $("#currency").change(function(){
            var $filter_input = $(this).parents(".filter_input");
            var price = $filter_input.find("*[data-type='price']");
            var value = $(this).val();
            price.each(function(){
                if(!object.empty($(this).val())){
                    object.set_string('currency', value);
                    object.request();
                    return false;
                }
            });
        });
        
        $("#button_search").click(function(){
            var search = $("#search_input").val();
            object.set_string('search', search);
            object.request();
        });
        
        
        $("#search_input").keypress(function (e) {
            if (e.which == 13) {
                $("#button_search").click();
                return false;
            }
        });
        
        $("#filter_clear").click(function(){
            // Находим все элементы для обнуления.
            var find = $(".spread_info *[data-id='']", "#management_tool");
            find.data("active",'1');
            var input_select = $("input, select", "#management_tool");
            var reset = [];
            input_select.each(function(){
                reset.push($(this).data("name"));
                if($(this).is("input")){
                    $(this).val('');
                }else{
                    var val = $(this).find("option:first").val();
                    $(this).val(val);
                }
            });
            object.fake_input_count();
            object.request({},reset);
        });
        
        // Сохраняем данные в таймер
        this.timer_setup();
        this.timer();
    },
    
    is_target : function (target, e){
        if(!target.is(e.target) && target.has(e.target).length === 0){
            return false;
        }
        return true;
    },
    
    fake_input_count : function ($fake = ''){
        var object = this;
        if($fake == '')
            $fake = this.$fake_input;
        
        $fake.each(function(){
            var $fake_input = $(this);
            var $info = $(this).find(".spread_info *");
            var count = 0;
            var name = $($info.get(0)).text();
            var query = '';
            var key = $fake_input.data("name");
            
            $info.each(function(){
                var id = $(this).data("id");
                var active = $(this).data("active");
                
                if(!id && active == 1){
                    count = 0;
                    $info.data("active",0);
                    
                    return false;
                }
                if(active == 1){
                    query += id + ',';
                    name = $(this).text();
                    count++;
                }
            });
            
            var index_trim = query.lastIndexOf(",");
            if(index_trim > 0)
                query = query.slice(0,index_trim);
            
            object.set_string(key, query);
            
            if(count == 0){
                $(this).addClass("empty");
            }else{
                $(this).removeClass("empty");
            }
            $(this).find(".filter_count span").text(count);
            
            if($(this).attr("id") != 'input_specialization')
                $(this).find(".input_name").text(name);
            
        });
    },
    fake_input_close : function($fake_input = ''){
        var object = this;
        
        object.$target = '';
        
        var $spread_box = this.$spread_box;
        
        $spread_box.each(function(){
            if(!$(this).hasClass("d-none")){
                object.fake_input_count($fake_input);
                $spread_box.addClass("d-none");
                // Выполнить запрос
                object.request();
            }
        });
    },
    
    fake_input_open : function($fake_input){
        // Создаем таблицу.
        var $info = $fake_input.find(".spread_info *");
        var width = document.body.clientWidth;
        var sreen_add = {
            "480" : 2,
            "620" : 3,
            "1150" : 4,
        }
        var coll = 4;
        // Корректировка колонок для других элементов
        if($fake_input.attr('id') != 'input_specialization'){
            sreen_add = {
            "1150" : 2,
            }
            coll = 2;
            if($fake_input.attr('id') == 'input_country'){
                sreen_add = {
                "1150" : 1,
                }
                coll = 1;
            }
        }

        
        for(test in sreen_add){
            if(width <= test){
               coll = sreen_add[test];
               break;
            }
        }
        
        var row = Math.ceil($info.length / coll);

        var i = 0;
        var string = '';
        string += '<table>';
        string += '<tr>';
        
        $info.each(function(){
            var id = $(this).data("id");
            var active = $(this).data("active");

            if(i == 0){
                string += '<td>';
            }
            
            var classes = '';
            if(active == 1){
                classes = ' active';
            }
            string += '<div class="elem'+classes+'" data-id="'+id+'">';
            string += $(this).text();
            string += '</div>';
            
            i++;
            if(i >= row){
                string += '</td>';
                i = 0
            }
        });
        string += '</tr>';
        string += '</table>';
        $fake_input.find(".content_info").html(string);
    },
    
    set_string : function(key,value){
        if(this.$_GET[key]){
            if(this.empty(value))
                delete this.$_GET[key];
            else
                this.$_GET[key] = value;
        }else{
            if(!this.empty(value)){
                this.$_GET[key] = value;
            }
        }
        
    },
    
    get_string : function(to_get = {},to_reset = []){
        var get = this.$_GET;
        var reset = this.reset_key;
        
        if(to_reset.length != 0)
            to_reset.forEach(function(element){
                reset.push(element);
            });
        
        var i = 0;
        var query = '';
        
        if(reset.length > 0){
            reset.forEach(function(element){
                if(get[element])
                    delete get[element];
            });
        }

        if(!$.isEmptyObject(to_get)){
            for(key in to_get)
                get[key] = to_get[key];
        }
        
        if(!$.isEmptyObject(get)){
            for(key in get){
                if(i == 0){
                    query = "?"+key+"="+encodeURIComponent(get[key]);
                }else{
                    query += "&"+key+"="+encodeURIComponent(get[key]); 
                }
                i = 1;
            }
        }
        return query;
    },
    
    request : function(to_get = {}, to_reset = []){
        var request = this.get_string(to_get, to_reset);
        if(request == ''){
            history.pushState({}, '',window.location.pathname);
        }else{
            history.pushState({}, '',request);
        }
        this.ajax();
    },
    
    empty : function(value, ziro = false){
        if((typeof(value) == 'number' && isNaN(value)) || value == '' || typeof(value) == 'undefined' || value == null){
            return true;
        }
        return false;
    },
    
    ajax : function(){
        var request = new ajax.get();
        
        request.after(function(data,$content,object){
            $content.html(data.content);
            object.timer_setup();
            object.timer();
        },['data',this.$content,this]);
        
        request.load_bar("#ads .progress-bar");
        
        request.query("/ajax/announces"+window.location.search);
    },
    timer_setup : function(){
        var object = this;
        var exp = $(".expiration");

        exp.each(function(){
            var $time = $(this).find(".time");
            var time_string = $time.text();
            var date_time = $(this).data("time");
            var t = date_time.split(/[- :]/);

            // Apply each element to the Date function
            var date = new Date(t[0], t[1], t[2], t[3], t[4], t[5]).getTime();
            
            var regexp = /([0-9]{1,}):([0-9]{1,}):([0-9]{1,})/g;
            var match = regexp.exec(time_string);
            
            object.expiration.push({"$elem":$time,"date_end" : date, "match" : match});
        });
    },
    expiration_rearray : function(){
        var object = this;
        var tmp = [];
        object.expiration.forEach(function(element){
            if(element){
                tmp.push(element);
            }
        });
        object.expiration = tmp;
    },
    timer : function(){
        var object = this;
        
        if(object.timer_id){
            clearInterval(object.timer_id);
        }

        if(object.expiration.length != 0){
            object.timer_id = window.setInterval(function(){
                if(object.expiration.length == 0){
                    clearInterval(object.timer_id);
                    return false;
                }
                for(key in object.expiration){
                    var elel = object.expiration[key];
                    var $time = elel.$elem;
                    var data_end = elel.date_end;
                    if(data_end < new Date().getTime()){
                        $time.parents(".expiration").remove();
                        object.expiration[key] = null;
                        object.expiration_rearray();
                        return false;
                    }
                    
                    elel.match[3] = (elel.match[3]*1) - 1;
                    if(elel.match[3] < 0){
                        elel.match[3] = 59;
                        elel.match[2] = (elel.match[2]*1) - 1;
                        if(elel.match[2] < 0){
                            elel.match[2] = 59;
                            elel.match[1] = (elel.match[1]*1) - 1;
                            if(elel.match[1] < 0){
                                elel.match[1] = 0;
                                elel.match[2] = 0;
                                elel.match[3] = 0;
                                $time.parents(".expiration").remove();
                                object.expiration[key] = null;
                                object.expiration_rearray();
                                return false;
                            }
                        }
                    }
                    for(var i = elel.match.length;i > 0;i--){
                        if(elel.match[i-1] < 10)
                            elel.match[i-1] = "0" + parseInt(elel.match[i-1]);
                    }
                    $time.text(elel.match[1]+":"+elel.match[2]+":"+elel.match[3]);
                }
            }, 1000);
        }
    }
}


search.init();
</script>