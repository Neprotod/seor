<?=$content?>
<script>
    $("tr[href").click(function(){
        location.replace($(this).attr("href"));
    });
</script>