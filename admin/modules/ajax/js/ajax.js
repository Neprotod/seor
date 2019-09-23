var ajax = {
    session_id : "",
    method : {},
    objects : {},
    cache : false,
    
    init : function(session_id){
        this.session_id = session_id;
    },
    
    for_pars : function(file){
        var data = new FormData();
        
        data.append("file",file);
        $.each( file, function( key, value ){
            data.append( key, value );
        });
        data.append('my_file_upload', 1);
        
        return data;
    },
    
    get : function(object_name = false){
        if(object_name){
            ajax.objects[object_name] = this;
        }
        this.session_id = ajax.session_id;
        this.method = {};
        
        var object = this;
        
        this.query = function(url,data = {}, async = true){
            if(!this.session_id){
                alert("Необходимо установить сессию");
                return false;
            }
            data['session_id'] = this.session_id;
            
            var result = '';
            
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: "json",
                async : async,
                cache : false,
                success: function(data){
                    if(data.exception){
                        alert(data.exception);
                    }

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
        this.query_form = function(url,data = new FormData(), async = true){
            if(!this.session_id){
                alert("Необходимо установить сессию");
                return false;
            }
            
            data.append("session_id",this.session_id);
            
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
                success: function(data){
                    if(data.exception){
                        alert(data.exception);
                    }
                    
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