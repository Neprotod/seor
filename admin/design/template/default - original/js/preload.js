/**
 *  Предварительная загрузка элементов
 tinymce.settings
 */

var preload = {
    init : function(){
        
    },
    tinyMCE : function(){
        if(!tinymce.settings)
            return false;
        
        /*var selector = tinymce.settings.selector;
       alert(selector);*/
       for(v in tinymce.each)
           alert(v);
    }
}