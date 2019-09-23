<script  type="text/javascript" src="<?=Url::i()->root()?>/media/js/tiny_mce/plugins/smimage/smplugins.js"></script>
<script type="text/javascript" src="<?=Url::i()->root()?>/media/js/tiny_mce/tiny_mce.js"></script>

<script>
  tinyMCE.init({
        // General options
        mode : "specific_textareas",
        editor_selector : /editor/,
        theme : "advanced",
        language : "ru",
        theme_advanced_path : false,
        apply_source_formatting : false,
        plugins : "jaretypograph,smimage,smeditimage,smexplorer,safari,spellchecker,style,table,save,advimage,advlink,autolink,inlinepopups,media,contextmenu,paste,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
        relative_urls : false,
        remove_script_host : true,
        convert_urls : true,
        
        verify_html: false,
        remove_linebreaks : false,
        /*content_css :"",*/
        spellchecker_languages : "+Russian=ru,+English=en",
                
        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,paste,pastetext,pasteword,|,undo,redo,|,bold,italic,underline,strikethrough,|,sub,sup,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "tablecontrols,|,link,unlink,anchor,smimage,smeditimage,smexplorer,charmap,nonbreaking,|,styleprops,attribs,|,jaretypograph,removeformat,cleanup,spellchecker,|,visualaid,fullscreen,code",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        /*theme_advanced_resizing_max_width : 940,
        theme_advanced_resizing_min_width : 940,*/
        theme_advanced_resizing_use_cookie : false,
        
        file_browser_callback : "SMPlugins",
        plugin_smexplorer_directory : "/media",
        plugin_smimage_directory : "/media",
    
        setup : function(ed) {
            if(typeof set_meta == 'function')
            {
                ed.onKeyUp.add(set_meta);
                ed.onChange.add(set_meta);
            }
        }

    });
</script>