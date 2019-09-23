var help = {
    init : function(){
        var $help = $("#help_line")
        $("*[data-help]").each(function(){
            $(this).hover(
            function(){
               $help.text($(this).data("help"));
            },
            function(){
                $help.text("");
            },
            );
        });
    }
}