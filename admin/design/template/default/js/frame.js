var frame = {
    $main : '#main',
    $find : '',
    body : '',
    background : 'background_frame',
    href : '',
    iframe : '',
    
    /*
     *@param string путь как "#block a"
     */
    init : function(path){
        var object = this;
        
        (object).$main = $((object).$main);
        
        (object).$find = (object).$main.find(path);

        (object).$find.click(function(){
            var href = $(this).attr('href');
            if(href){
                //Проверяем и дополняем ссылку
                if(href.search(/\?/) == -1){
                    href += "?ajax";
                }else{
                    if(href.search(/\?ajax/) == -1)
                        if(href.search(/&ajax/) == -1)
                            href += "&ajax";
                }
                
                
                //Сохраняем ссылу на всякий случай
                (object).href = href;
                
                
                //Сохраняем body если его нет
                if(!(object).body)
                    (object).body = $('body');
                
                //Убераем прокрутку
                (object).body.css("overflow",'hidden');
                
                (object).iframe = $('<div class="'+(object).background+' animated"><div class="iframe"><iframe src="'+href+'" /></div></div>');
                
                (object).body.append(object.iframe);
                
                //Функция для расширения
                (object).parent($(this));
                
                //Если нажимаем на фон
                (object).iframe.click(function(){
                    (object).drop();
                });
                
                //Если нажимаем крестик
                (object).iframe.find('iframe').load(function(){
                    //Для расширения
                    (object).frame_load($(this));
                    $(this).contents().find("#drop").click(function(){
                        (object).drop();
                    });
                });
            }
            return false;
        });

    },
    drop : function(){
        this.body.css("overflow",'');
        this.iframe.remove();
    },
    parent : function($this){
        
    },
    frame_load : function($this){
        
    }
};

var iframe = function(path){
    this.$main = $("#main");
    this.$find = '';
    this.body = '';
    this.background = 'background_frame';
    
    var object = this;
    
    (object).$find = (object).$main.find(path);
    (object).$find.click(function(){
        var href = $(this).attr('href');
        if(href){
            //Проверяем и дополняем ссылку
            if(href.search(/\?/) == -1){
                href += "?ajax";
            }else{
                if(href.search(/\?ajax/) == -1)
                    if(href.search(/&ajax/) == -1)
                        href += "&ajax";
            }
            object.body = $('body');
            object.iframe = $('<div class="'+(object).background+' animated"><div class="iframe"><iframe src="'+href+'" /></div></div>');
            object.body.append(object.iframe);
            object.body.css("overflow",'hidden');
            object.iframe.click(function(){
                (object).drop();
            });
            object.iframe.find('iframe').load(function(){
                $(this).contents().find("#drop").click(function(){
                    (object).drop();
                });
            });
        }
        return false;
    });
        
    this.drop = function(path){
        this.body.css("overflow",'');
        this.iframe.remove();
    }
}