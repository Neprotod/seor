<?=$menu?>
<?=$content?>
<script>
    $(".action-element").click(function(){
        var parents = $(this).data("dublicate");
        var clone;
        
        parent = $(parents+':first');
        clone = parent.clone();
        //alert(clone.length);
        //alert(parent.attr("class"));
        clone.each(function(){
            var id = $(this).find(".id");
            id.text($(parents).length + 1);
            $(this).find("input").each(function(){
                $(this).attr("value","");
            });
        });
        
        
        
        parent.parent().append(clone);
    });
    
    $(".new_checkbox").each(function(){
        var input = $(this).find("input");
        if(input.is(':checked'))
            $(this).addClass("active");
        input.click(function(){
            $(this).parent(".new_checkbox").toggleClass("active");
        });
    });
</script>