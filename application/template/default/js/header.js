var header = {
    focus : '',
    target : '',
    modal : "#modal_window",
    init : function (){
        var object = this;
        
        // Определяем модальное окно
        object.modal = $(object.modal);
        
        // Выключить уже проверенные уведомления
        $(".popup_content","#notification_menu").mouseover(function(e){
            if($(e.target).hasClass("notification_seen")){
                $(e.target).removeClass("notification_seen");
            }
        });
        // обрабатываем notification
        $("#notification").click(function(){
            object.target = $(this);
            $(this).removeClass("active");
            var $pop = $("#notification_menu, #notification .arrow");
            
            if($pop.hasClass("fadeIn")){
                object.close_popup();
                return false;
            }
            
            var count = $pop.data("count");
            var $notification_count = $pop.find(".notification_count");
            if(count){
                $pop.data("count",0);
                $notification_count.html("("+count+")");
            }else{
                $notification_count.html("");
            }
 
            object.popup($pop);
            
            // Создаем Ajax запрос
            var get = new ajax.get();
            
            get.after(function(data,$pop){
                $pop.find(".popup_content").html(data);
            },["data",$pop]);
            
            // Указываем где load bar
            get.load_bar($pop.find(".progress-bar"));
  
            get.query("/ajax/notification");
        });
        
        $("#pay").click(function(){
            var $pop = $("#pay_menu, #pay .arrow");
            
            if($pop.hasClass("fadeIn")){
                object.close_popup();
                return false;
            }
            
            object.popup($pop);
            
            $pop.on("click",function(e){
                if($(e.target).attr("id") == "promo"){
                    // Открываем модальное окно
                }
            });
            
            // Создаем Ajax запрос
            var get = new ajax.get();
            
            get.after(function(data,$pop){
                $pop.find(".popup_content").html(data);
            },["data",$pop]);
            
            // Указываем где load bar
            get.load_bar($pop.find(".progress-bar"));
  
            get.query("/ajax/balance");
        });
        
        $("#client").click(function(){
            var $pop = $("#client_menu, #client .arrow");
            if($pop.hasClass("fadeIn")){
                object.close_popup();
                return false;
            }
            object.popup($pop);
        });
    },
    popup : function($pop){
        $pop = $($pop);
        $pop.toggleClass("fadeIn");
        this.focus = $pop;
        var object = this;
        $(document).on("mouseup.popup",function(e){
            if(object.focus)
                if(!object.focus.is(e.target) && (object.focus.has(e.target).length == 0)){
                    var $parent = $(e.target);
                    if(!$parent.hasClass("popup_activator"))
                        $parent = $(e.target).parents(".popup_activator");
                    
                    if($parent.length != 0){
                        
                        if($parent.prev().get(0) != object.focus.get(0)){
                            object.close_popup();
                        }
                    }else{
                        object.close_popup();
                    }
                }
        });
    },
    close_popup : function(){
        var object = this;
        object.focus.toggleClass("fadeIn");
        $(document).off("mouseup.popup");
        object.focus.off();
        object.focus = '';
    },
    promo : function(){
        // Создаем Ajax запрос
        var get = new ajax.get();
        
        this.modal.addClass("promo_modal");
        
        get.after(function(data,$modal){
            $modal.find(".modal-title").html(data.title);
            $modal.find(".modal-body").html(data.content);
        },["data",this.modal]);
        
        // Указываем где load bar
        get.load_bar(this.modal.find(".progress-bar"));

        get.query("/ajax/promo");
            
        this.modal.modal('show');
    },
    /**
     * Метод проверяет закончена ли анимация, если закончена, убирает классы, которые отвечали за анимацию
     * 
     * @param mixed  можно передать селектор или уже готовый jQuery объект
     * @param string классы которые нужны для анимации
     */
    testAnim : function($element, x, after = '', before = '', type = "animated") {
        $element = $($element);
        
        if(before != '')
              before($element);
        if(type == "animated"){
            $element.removeClass(x + ' animated').addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $(this).removeClass(x + ' animated');
                if(after != '')
                    after($(this));
            });
        }else{
            $element.removeClass(x).addClass(x).one('transitionend webkitTransitionEnd oTransitionEnd', function(){
                $(this).removeClass(x);
                if(after != '')
                    after($(this));
            });
        }
    },
    
    mobile_menu : function() {
        if($("#menu_mobile").length != 0){
            var object = this;
            $("#logo").click(function(){
                if($(window).width() <= "843"){
                    var $backdrop = $(".backdrop");
                    var $menu_mobile = $("#menu_mobile");
                    
                    object.testAnim($backdrop, "show",'','',"transition");
                    object.testAnim($menu_mobile, "slideInLeft supr-faster",'',function($el){
                        $el.removeClass("d-none");
                    });
                    return false;
                }
                return true;
            });
            $("#menu_mobile .menu_icon, .backdrop").click(function(){
                var $backdrop = $(".backdrop");
                var $menu_mobile = $("#menu_mobile");
                
                object.testAnim($backdrop, "show fade",function($el){
                   // $el.removeClass("show");
                },'',"transition");
                object.testAnim($menu_mobile, "slideOutLeft supr-faster",function($el){
                    $el.addClass("d-none");
                });
            });
        }
    }
}