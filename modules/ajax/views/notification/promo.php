<div id="promo_body" class="row">
    <div class="form-group control col no-margin">
        <input id="prono_input" autocomplete="off" type="text" class="form-control" placeholder="Введите промокод" value="" />
        <div class="small">Должно быть 16 символов <span class="promo_count"></span></div>
    </div>
    <div id="promo_content" class="col">
        <div class="lds-ellipsis fade">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
        <div class="text-content d-none">
            
        </div>
    </div>
    <script>
        var $modal = header.modal;
        
        function test_promo($elem){
            var $input = $elem;
            var $val = $input.val();
            var $promo_content = $(".text-content","#promo_content");
            
            // Убираем не нужный контент
            $promo_content.html("");
            $promo_content.addClass("d-none");
            
            $input.get(0).success = 0;
            
            var load = $(".lds-ellipsis","#promo_content");
            if($val.length == 16){
                load.addClass("show");
                // Если символов 16, значит создает ajax запрос для проверки.
                var get = new ajax.get();
                
                get.after(function(data,load,$promo_content,$input){
                    load.removeClass("show");
                    $promo_content.removeClass("d-none");
                    $promo_content.html(data.content);
                    $input.get(0).success = data.success;
                },["data",load,$promo_content,$input]);

                get.query("/ajax/promo",{"promo":$val});
            }else{
                load.removeClass("show");
                if($val.length > 16){
                    promo_error($promo_content, "Введено больше 16 символов.");
                }
                
            }
        }
        
        function promo_error($promo_content, error){
            $promo_content.removeClass("d-none");
            $promo_content.html('<div class="text-danger">'+error+'</div>'); 
        }
        /*
        // Проверка промокода
        $("#prono_input").change(function(){
            test_promo($(this))
        });
        // Проверка промокода
        $("#prono_input").keyup(function(){
            test_promo($(this))
        });
        */
        // Проверка промокода
        $("#prono_input").on("input",function(){
            test_promo($(this))
        });
        // Установка промокода
        $modal.find(".modal_submit").on("click",function(){
            var $input = $("#prono_input");
            var $val = $input.val();
            var $promo_content = $(".text-content","#promo_content");
            var error = '';
            if($val.length == 16){
                if($input.get(0).success){
                    // Создаем AJAX запрос
                    var get = new ajax.get();
                    
                    get.after(function(data,$promo_content,$input){
                        if(!data.success){
                            $input.get(0).success = 0;
                            if(data.content){
                                 promo_error($promo_content, data.content);  
                            }else{
                                promo_error($promo_content, "Промокод не установлен, попробуйте позже или обратитесь в тех поддежку");  
                            }
                        }else{
                            $modal.modal("hide");
                            var $coin_count = $("#coin_count");
                            var coint = (new Number($coin_count.text()) + Math.floor(data.promo.seor));
                            $coin_count.html(coint);
                            // Реально ли нужна перезагрузка...
                            location.reload();
                        }
                        
                    },["data",$promo_content,$input]);
                    
                    // Указываем где load bar
                    get.load_bar($modal.find(".progress-bar"));

                    get.query("/ajax/promo",{"promo":$val,"insert":true});
                }else{
                    error = "Промокод не верен.";
                }
            }else{
                error = "Промокод не введен.";
            }
            
            if(error){
                promo_error($promo_content, error);
            }
        });
    </script>
</div>