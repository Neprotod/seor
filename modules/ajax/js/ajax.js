var ajax = {
    session_id : "",
    method : {},
    objects : {},
    cache : false,
    
    init : function(session_id){
        this.session_id = session_id;
    },
    /**
     * Парсит GET запрос
     */
    _get : function(session_id){
        var $_GET = {};
            // Парсим get запрос.
            document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
            function decode(s) {
                return decodeURIComponent(s.split("+").join(" "));
            }

            $_GET[decode(arguments[1])] = decode(arguments[2]);
        });
        
        return $_GET;
    },
    /**
     * Парсирует файл для отправки на сервер.
     *
     * @param mixed принимает либо input jquery с файлом, либо сам файл.
     */
    form_file : function(file,name = 'file',data = ''){
        if(data == '')
            var data = new FormData();
        if(file instanceof jQuery)
            file = file.get(0).files[0];
        
        try{
            if(file.files[0])
                file = file.files[0];
            
            if(file[0])
                file = file[0];
        }catch(e){}

        data.append(name,file);
        /*$.each( file, function( key, value ){
            data.append( key, value );
        });*/
        //data.append('my_file_upload', 1);
        
        return data;
    
    },
    
    get : function(object_name = false){
        if(object_name){
            ajax.objects[object_name] = this;
        }
        this.session_id = ajax.session_id;
        this.method = {};
        
        this.bar = '';
        
        
        
        this.load_bar = function(path){
            this.bar = $(path);
        };
        
        this.download_progres = function(proc,type = null){
            if(this.bar){
                if(type == "show"){
                    this.bar.addClass("visibility");
                    this.bar.parent().fadeIn(100);
                }else if(type == "hide"){
                    var object = this;
                    this.bar.parent().fadeOut(600,function(){
                       object.bar.css({"width" : 0+"%"});
                    });
                }
                
                this.bar.css({"width" : proc+"%"});
            }
        };
        
        var object = this;
        
        this.query = function(url,data = {}, async = true, type = "json"){
            if(this.session_id){
                data['session_id'] = this.session_id;
            }
            
            var result = '';
            
            object = this;
            
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: type,
                async : async,
                cache : false,
                xhr: function(){
                    var xhr = $.ajaxSettings.xhr();
                    // Для загрузки файлов
                    xhr.upload.addEventListener('progress', function(evt){
                       if (evt.lengthComputable) {
                            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
                            
                            object.percentComplete = percentComplete;
                            
                            object.download_progres(percentComplete);
                            
                        }
                    });
                    xhr.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                           // var percentComplete = evt.loaded / evt.total;
                            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
                            
                            object.percentComplete = percentComplete;
                            
                            object.download_progres(percentComplete);
                            
                        }
                    });
                    
                    xhr.addEventListener("loadstart",function(){
                         object.download_progres(0,"show");
                    });
                    xhr.addEventListener("load",function(){
                        object.download_progres(100,"hide");
                    });
                    return xhr;
                },
                success: function(data){
                    try{
                        if(data.exception){
                            alert(data.exception);
                        }
                    }catch(e){}
                    
                    if(async){
                        object.before(data);
                    }else{
                        result = data;
                    }
                },
                error: function(jq, textStatus, error){
                    alert(error);
                }
            });
            if(!async)
                return result;
        },
        /*Отправка файла*/
        this.query_form = function(url,data = new FormData(), async = true){
            if(this.session_id){
                data['session_id'] = this.session_id;
                data.append("session_id",this.session_id);
            }
            
            var result = '';
            
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: "json",
                async : async,
                cache : false,
                // отключаем обработку передаваемых данных, пусть передаются как есть
                processData : false,
                // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
                contentType : false,
                xhr: function(){
                    var xhr = $.ajaxSettings.xhr();
                    // Для загрузки файлов
                    xhr.upload.addEventListener('progress', function(evt){
                       if (evt.lengthComputable) {
                            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
                            
                            object.percentComplete = percentComplete;
                            
                            object.download_progres(percentComplete);
                            
                        }
                    });
                    xhr.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
                            
                            object.percentComplete = percentComplete;
                            
                            object.download_progres(percentComplete);
                            
                        }
                    });
                    
                    xhr.addEventListener("loadstart",function(){
                         object.download_progres(0,"show");
                    });
                    xhr.addEventListener("load",function(){
                        object.download_progres(100,"hide");
                    });
                    return xhr;
                },
                success: function(data){
                    try{
                    if(data.exception){
                        alert(data.exception);
                    }
                    }catch(e){}
                    
                    if(async){
                        object.before(data);
                    }else{
                        result = data;
                    }
                    
                },
                error: function(jq, textStatus, error){
                    alert(error);
                }
            });
            if(!async)
                return result;
        }
        
        this.after = function(method,param = false){
            this.method[method] = param;
        }
        
        this.before = function(data){
            var no_pars = ["data"];
            var method = '';
            for(method in this.method){
                if(this.method[method]){
                    var params = [];
                    var method_str = 'var call = ' + method + ';';
                    method_str += "call(";
                    var i = 0;
                    for(param in this.method[method]){
                        var param_string = this.method[method][param];
                        if($.inArray(param_string, no_pars) != '-1'){
                            method_str += this.method[method][param] + ",";  
                        }else{
                            params[i] = param_string;
                            method_str += "params["+i+"],";
                            i++;
                        }
                     }
                     
                     var index = method_str.lastIndexOf(",");
                     method_str = method_str.substr(0,index);
                     method_str += ");";
                     eval(method_str);
                     
                }else{
                    var method_str = 'var call = ' + method + ';';
                    method_str += "call()";
                    eval(method_str);
                }
            }
        }
        
        this.clean = function(){
            this.method = {};
        }
        this.content = function(id,content){
            $(id + " .modal-body").html(content);
        }
    }
}