/**
 *  Обрабатывает ошибки созданные с помощью модели system:error
 */

var errors = {
    //Хранит массив найденных элементов, для дальнейшего удаления если пришла та же роль
    roles : {},
    init : function(){
        
        //Блок не должен уменьшаться при удалении одного из сообщений
        var $error_massage = $('#error_massage');
        $error_massage.css('min-width',$error_massage.innerWidth()+'px');
        
        
        var object = this;
        
        //Для удаления alert
        $("[data-dismiss]").click(function(){
            var parent = $(this).data('dismiss');
            $(this).parents('.'+parent).remove();
        });
        
        //Блоки должны браться реверсивно, так как самая главная ошибка сверху
        $($(".alert_box").get().reverse()).each(function(){
            //Роль взаимосвязана с элементами у которых есть атрибут active-role
            var role = '';
            
            //Тип, используется как дополнительный класс для обозначения ошибки
            var type = $(this).data('type');

            if(role = $(this).data('role')){
                //Подсказки
                var tooltip = $(this).data('tooltip');
                //Нужно ли выделять цветом
                var select = $(this).data('select');
                
                //Отобразить ошибку другого типа, пришел error а подкрасить элемент как warning
                var valid = $(this).data('valid');
                
                //Если такая роль уже была, это означает, что она была менее важная
                if(object.roles[role]){
                    //Если нет типа, значит это обычный alert, он самый не важный
                    if(!type){
                        return;
                    }
                    object.roles[role].element.forEach(function(element){
                        element.removeClass(object.roles[role].type_class);
                    });
                    object.roles[role].tooltip.remove();
                }
                
                object.roles[role] = {};
                object.roles[role].element = new Array();
                
                $('[active-role="'+role+'"]').each(function(){
                    //Сохраняем элемент
                    object.roles[role].element.push($(this));
                    
                    var type_class = '';
                    if(!valid)
                        type_class = type;
                    else
                        type_class = valid;
                    
                    $(this).addClass(type_class);
                    
                    //Сохраняем класс
                    object.roles[role].type_class = type_class;
                    
                    if(tooltip){
                        var parent = $(this).parent();
                        if(parent.hasClass('control')){
                            //Если есть родительский класс control добавляем в отдельном блоке
                            var $tooltip = $('<div class="help_block '+type+'">'+tooltip+'</div>');
                            parent.append($tooltip);
                            
                            object.roles[role].tooltip = $tooltip;
                            
                        }else{
                            //Работает только с формами. Если блока control нет, подсказка появится
                            //сбоку если позволяет размер экрана, или сверху, если экран маленький
                            var $tooltip = $('<div class="tooltip hide">'+tooltip+'</div>');
                            
                            object.roles[role].tooltip = $tooltip;
                            
                            $('body').append($tooltip);

                            var tooltip_width = $tooltip.width();
                            var tooltip_height = $tooltip.height();
                            
                            var width = $(this).innerWidth();
                            var height = $(this).innerHeight();
                            var top = $(this).position().top;
                            var left = $(this).position().left;
                            
                            var body_width = $('body').width();
                            
                            var test = body_width - width;
                            
                            if(test > tooltip_width){
                                $tooltip.css({
                                        'position':'absolute',
                                        'z-index':'1000000000',
                                        'top':top+'px',
                                        'left':left+width+'px'
                                    }
                                );
                                $tooltip.append('<div class="arrow-left" />');
                            }else{
                                $tooltip.css({
                                        'position':'absolute',
                                        'z-index':'1000000000',
                                        'top':top-height-tooltip_height+'px',
                                        'left':left+'px'
                                    }
                                );
                                $tooltip.addClass('tooltip-top');
                                $tooltip.append('<div class="arrow-top" />');
                            }
                            
                            $(this).focus(function(){
                                $tooltip.removeClass('hide');
                            });
                            $(this).blur(function(){
                                $tooltip.addClass('hide');
                            });
                        }
                    }
                });
            }
        });
    }
}