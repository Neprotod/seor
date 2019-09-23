$(document).ready(function(){
    $(".break").each(function(){
        var $par = $(this).parent();

        alert($par.width());
    });    
});