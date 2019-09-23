/**
 *  Обрабатывает ошибки созданные с помощью модели system:error
 */

var errors = {
    //Хранит массив найденных элементов, для дальнейшего удаления если пришла та же роль
    roles : {},
    init : function(){
        
        //Блок не должен уменьшаться при удалении одного из сообщений
        var $error_massage = $('#error_message');
        //$error_massage.css('min-width',$error_massage.innerWidth()+'px');
        
        var object = this;
        
        //Для удаления alert
        $error_massage.find("[data-dismiss]").click(function(){
            var parent = $(this).data('dismiss');
            $(this).parents('.'+parent).remove();
        });
        
        //Блоки должны браться реверсивно, так как самая главная ошибка сверху
        $($error_massage.find(".alert_box").get().reverse()).each(function(){
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
                    return;
                    //Если нет типа, значит это обычный alert, он самый неважный
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
                    
                    //$(this).addClass(type_class);
                   
                    //Сохраняем класс
                    object.roles[role].type_class = type_class;
                    
                    if(tooltip){
                        //object.roles[role].tooltip = $tooltip;
                    }
                    
                    object.commit($(this), role, type_class, type, tooltip);
                    /*
                    if(tooltip){
                        var parent = $(this).parent();
                        if(parent.hasClass('control')){
                            //Если есть родительский класс control добавляем в отдельном блоке
                            var $tooltip = $('<div class="help_block '+type+'">'+tooltip+'</div>');
                            parent.append($tooltip);
                        }
                    }
                    
                    //////////////////////////////////////////////
                     // Пишем события на убирания предупреждения с input
                    var tag = $(this).get(0).tagName;
                    var input;
                    var val;
                    
                    // Определяем это input или нет 
                    if(tag.toLowerCase() == "input"){
                        input = $(this);
                    }else{
                        input = $(this).find("input");
                    }
                    // Если есть input собираем данные и создаем события на изменение формы
                    if(input){
                        val = input.val();
                        var get = input.get(0);
                        get.testVal = val;
                        get.classChange = $(this);
                        get.newClass = type_class;
                        if(parent){
                            var help_block = parent.find(".help_block");
                            parent.get(0).help_block = help_block;
                        }
                        input.change(function(){
                            
                            var get = $(this).get(0);
                            var val = $(this).val();
                            
                            if(get.testVal == val){
                                get.classChange.addClass(get.newClass);
                                if(parent){
                                    var help_block = parent.get(0).help_block;
                                    parent.append(help_block);
                                }
                            }else{
                                if(parent){
                                    var help_block = parent.get(0).help_block;
                                    help_block.remove();
                                }
                                get.classChange.removeClass(get.newClass);
                            }
                        });
                        
                    }
                    */
                });
            }
        });
    },
    /**
     * Вставляет ошибку в поле.
     * 
     * @param  object jquery элемент
     * @param  string имя класса для ошибки
     * @param  string имя класса для ошибки в поле help_block
     * @param  string строка ошибки.
     * @return void
     */
    commit : function($elem, role, type_class, type, tooltip){
        $elem.addClass(type_class);
        
        if(tooltip){
            var parent = $elem.parents(".control");
            if(parent.hasClass('control')){              
                //Если есть родительский класс control добавляем в отдельном блоке
                var $tooltip = $('<div class="help_block '+type+'">'+tooltip+'</div>');
                var test;
                if(test = parent.find(".help_block")){
                    test.remove();
                }
                parent.append($tooltip);
                
                //this.roles[role].tooltip = $tooltip;
            }else{
                parent = '';
            }
        }
        
        //////////////////////////////////////////////
         // Пишем события на убирания предупреждения с input
        var tag = $elem.get(0).tagName;
        var input;
        var val;
                    
        // Определяем это input или нет 
        if(tag.toLowerCase() == "input" || tag.toLowerCase() == "select"|| tag.toLowerCase() == "textarea"){
            input = $elem;
        }else{
            input = $elem.find("input, select, textarea");
        }
        // Если есть input собираем данные и создаем события на изменение формы
        if(input){
            val = input.val();
            var get = input.get(0);
            get.testVal = val;
            get.classChange = $elem;
            get.newClass = type_class;

            if(parent){
                var help_block = parent.find(".help_block");
                parent.get(0).help_block = help_block;
            }
            input.on("change."+role,function(){
                var get = $elem.get(0);
                var val = $elem.val();
                
                if(get.testVal == val){
                    get.classChange.addClass(get.newClass);
                    if(parent){
                        var help_block = parent.get(0).help_block;
                        parent.append(help_block);
                    }
                }else{
                    if(parent){
                        var help_block = parent.get(0).help_block;
                        help_block.remove();
                    }
                    get.classChange.removeClass(get.newClass);
                }
            });
        }   
    },
    /**
     * Вставляет ошибку в поле.
     * 
     * @param  object jquery элемент
     * @param  string имя класса для ошибки
     * @param  string имя класса для ошибки в поле help_block
     * @param  string строка ошибки.
     * @return void
     */
    remove_event : function($elem,role){
        var tag = $elem.get(0).tagName;
        var input;
        var val;
        
        // Определяем это input или нет 
        if(tag.toLowerCase() == "input"){
            input = $elem;
        }else{
            input = $elem.find("input");
        }
        
        if(input){
            var get = input.get(0);
            get.classChange = $elem;
            get.classChange.removeClass(get.newClass);
            
            input.off("change."+role);
        }       
    }
    
}