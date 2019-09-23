/*Работа меню*/
var menu = {
    $nav_footer : '',
    $nav_footer_action : '',
    open : false,
    init : function(){
        var object = this;
        
        (object).$nav_footer = $("#nav_footer");
        
        (object).$nav_footer_action = (object).$nav_footer.find("#nav_footer_action");
        
        var $nav_footer_wrap = (object).$nav_footer.find("#nav_footer_wrap");
        
        (object).first();
        
        (object).$nav_footer_action.click(function(){
            if(object.open == false){
                $nav_footer_wrap.scrollTop(0);
                (object).opened($(this));
            }else{
                (object).close($(this));
            }
        });
    },
    opened : function(element){
        if(!element)
            element = (this).$nav_footer_action;
        var element = element;
        (this).$nav_footer.addClass('active');
        element.addClass('active');
        (this).open = true;
        var object = (this);
        $(window).on("scroll",function(){
            if(object.open == true){
                object.open = false;
                (object).close(element);
            }
        });
    },
    close : function(element){
        if(!element)
            element = (this).$nav_footer_action;
        (this).$nav_footer.removeClass('active');
        element.removeClass('active');
        (this).open = false;
    },
    on : function(){
        (this).$nav_footer_action.css({'margin-right' : '',opacity : '1',transition : ''});
    },
    off : function(){
        var width = (this).$nav_footer_action.innerWidth();

        (this).$nav_footer_action.css({"margin-right" : '-'+width+'px',opacity : '0'});
    },
    first : function(){
        (this).$nav_footer_action.css('transition' , 'none');
    }
};