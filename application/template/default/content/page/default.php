<?php
if(!isset(Registry::i()->user)):
?>
<style>
#logo{
    position:absolute;
    z-index:50;
    top:auto;
}
#logo a{
    display:block;
    width:110px;
    height:104px;
    background:url("/application/template/default/img/icon.png");
    background-position:-385px 0px;
    background-repeat:no-repeat;
    margin:auto;
    position:relative;
    top:5px;
}
#logo > div{
    box-sizing: padding-box;
    background:white;
    width:146px;
    padding-bottom:30px;
    border-radius:50%;
    margin-top:-1em;
    position:relative;
    box-shadow:0 0px 8px 4px rgba(0,0,0,0.2);
}
#logo .overlay{
    position:absolute;
    left:-10px;
    right:-10px;
    height:66px;
    background:inherit;
}
</style>
<?php
endif;
?>

<?=$content?>