<div id="croper" class="croper">
    <img id="image_crop" src="/<?=$src?>" />
</div>
<script>
var cord = {};
function cooordiant(img,selection){
    cord = selection;
}

String.prototype.hashCode = function() {
  var hash = 0, i, chr;
  if (this.length === 0) return hash;
  for (i = 0; i < this.length; i++) {
    chr   = this.charCodeAt(i);
    hash  = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
};

setTimeout(function () {
    var i = new Image();
    i.src = $('#image_crop').attr("src");
    i.onload = function(){
        <?php if(isset($coordinate)):?>
            x1 = <?= $coordinate["x1"]?>;
            x2 = <?= $coordinate["x2"]?>;
            y1 = <?= $coordinate["y1"]?>;
            y2 = <?= $coordinate["y2"]?>;
        <?php else:?>
            x1 = (this.width / 2) - 50;
            x2 = x1 + 100;
            y1 = (this.height / 2) - 50;
            y2 = y1 + 100;
        <?php endif;?>
        $('#image_crop').imgAreaSelect({
            handles: true,
            aspectRatio : '1:1',
            minHeight : "100",
            minWidth  : "100",
            persistent : true,
            fadeSpeed: 200,
            x1 : x1,
            x2 : x2,
            y1 : y1,
            y2 : y2,
            onInit : cooordiant,
            onSelectStart : cooordiant,
            onSelectEnd : cooordiant
            //onSelectEnd: someFunction
        });
    }
}, 200);

$('#modal_logo').on('hide.bs.modal', function (e) {
    $('#image_crop').imgAreaSelect({remove : true});
});


$modal_logo.find(".modal_submit").click(function(){
    var get = new ajax.get();

    get.after(function(data,$modal){
        $(".logo_image").attr("src",data.src + "?v="+new String(new Date()).hashCode());
        $("#company_logo").data("logo",0);
        $modal_logo.modal("hide");
    },["data",$modal_logo]);
    
    get.query("/ajax/image/logo", {"coordinate" : cord, "src" : $('#image_crop').attr("src")});
});
</script>