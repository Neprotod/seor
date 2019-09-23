<!-- Модальное окно -->
<div class="modal fade" id="technical_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&#215;</span>
                </button>
            </div>
            <div class="modal-body">
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn_green modal_submit">Применить</button>
            </div>
        </div>
    </div>
</div>
<?=$content?>
<script>
var $pop = $("#technical_modal");

$("#drop_accaunt").click(function(){
    var request = new ajax.get();
    
    request.after(function(data,$pop){
        $pop.find(".modal-title").html(data.title);
        if(data.content){
            $pop.find(".modal-body").html(data.content);
            $pop.find(".modal_submit").on("click",function(){
                var drop = new ajax.get();
                drop.after(function(data){
                    if(data.error){
                        $pop.find(".modal-body").html(data.error);
                    }else{
                        $pop.find(".modal-body").html(data.content);
                    }
                    location.reload();
                },['data',$pop]);
                drop.query("/ajax/user/drop",{"drop": 1});
            });
        }else if(data.error){
            $pop.find(".modal-body").html(data.error);
        }
    },['data',$pop]);

    request.query("/ajax/user/drop",{});
    $pop.modal("show");
});
</script>