
/*
 * Объект для загрузки контента
 * Для динамической загрузки у элемента должен быть класс .loading
 * если у элемента дополнительно класс width, будет загружатся в строку
 *
 * Должен быть подключен css стиль animate с классом openIn
 */
var loadind = {
    
    //@var bool
    launch : false,
    //@var int время попадающее в таймер
    time : 0,
    //@var int шаг выполнения таймера
    time_stap : 80,
    
    //@var int что бы включать анимацию с нужного момента
    scroll : $(window).scrollTop(),
    
    //@var int что бы не включать анимацию елементам которые не помещаются
    window_height : $(window).height(),
    //@var int заполняется в init для ограничения по ширине экрана.
    window_width : 0,
    
    //@var int временная высота, для дальнейших вычислений
    tmp_height : 0,
    //@var int добавочная разница в высоте
    difference : 0,
    
    //@var int общая высота всех элементов
    height : 0,
    //@var int общая ширина всех элементоа
    width : 0,
    
    //@var string основной блок контента
    main : "#main",
    //@var string внутренний блок
    block : ".block",
    
    //@var bool когда остановить
    stop : false,
    
    //@var object найденные элементы у которых должна быть анимация
    $loading : "",
    //@var array элементы для обнуления
    loading_reset : new Array(),
    
    //@var array содержит все таймеры для обнуления
    timeout : new Array(),
    
    /*
     * Останавливает таймеры и вывод все блоки
     * !!! работает как callbacks функции on
     *
     * @param object в свойство date записывается this объект
     */
    stop_animation : function(event){
        if((event.data.scroll != $(this).scrollTop())){
            //Удаляем все таймеры
            if(event.data.timeout){
                event.data.timeout.forEach(function(element){
                    clearTimeout(element);
                });
                event.data.timeout = '';
                
                event.data.loading_reset.forEach(function(element){
                    element.css('visibility','');
                    element.addClass('animated openIn');
                });
            }
            
            $(window).off("scroll",event.data.stop_animation);
        }
    },
    /*
     * Загружает класс
     *
     */
    init : function(){
        var object = (this);
        
        //Измеряем основной блок
        object.window_width = $(object.main).width();
        
        //Измеряем дополнительный блок и сохраняем реальную ширину
        /*var block_width = $(object.main+' '+object.block).width();
        object.window_width = (object.window_width + (block_width - object.window_width));*/
        
        var old_loading = '';
        
        if(object.$loading){
            old_loading = object.$loading;
        }
         
        //Находим элементы для отрисовки
        object.$loading = $(object.main+ " .loading");

        if(object.$loading.length > 30){
            alert('Ошибка сценария, чрезмерная загрузка ' + object.$loading.length + ' элементов');
            return false;
        }
        if(object.time != 0){
            alert('Повторный запрос');
            return false;
        }
        
        object.$loading.each(function(index){
            
            //Устаналивает другое время для setTimeout
            var time_stap = false;
            //Нужно ли считать высоту элемента
            var to_height = true;
            
            //Если есть класс width пытаемся соеденить их по времени выполнения
            if($(this).hasClass('width')){
                var width = $(this).outerWidth(true);
                var height = $(this).outerHeight(true);    
                if(object.width == 0){
                    object.width = width;
                    object.tmp_height = height;
                }else{
                    //Вычисялем разницу в высоте на линии элементов
                    if(object.tmp_height < height){
                        object.difference = height - object.tmp_height; 
                    }
                    //Если ширина позволяет время выполнения не меняется
                    if((object.width += width) < object.window_width){
                        time_stap = 0;
                        to_height = false;
                    }else{
                        object.width = width;
                        object.tmp_height = height;
                    }
                }
            }else{
                var height = $(this).outerHeight(true);
                object.width = 0;
            }
            
            //Вычилсяем общую высоту.
            if(to_height){
                object.height +=  height + object.difference;

                object.difference = 0;
            }

            var scroll = object.height-object.scroll;
            alert(scroll);
            //Если мы на допустимом скроле включаем анимацию
            if(object.stop === false){
                if(scroll > (object.window_height + 200)){
                    object.stop = true;
                }
                //Сохраняем для обнуления
                object.loading_reset[object.loading_reset.length] = $(this);
                //Время выполнения
                object.time += (time_stap !== false)? time_stap :object.time_stap;
                
                //Пока анимации нет, элемент скрыт
                $(this).css('visibility','hidden');
                var animated = $(this);
                object.timeout[object.timeout.length] = setTimeout(function(){
                    animated.css('visibility','');
                    animated.addClass('animated openIn');
                },object.time);
            }
        });
        
        //Если происходит скролл убираем таймеры и загружаем сразу все
        $(window).on("scroll",object,object.stop_animation);
        
    }
}