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


$_get = ajax._get();

function remove_ads(){
    if($(".ads").length == 0){
        $("#container_ads").append('<div id="no_ads"><div class="h4">Нет вакансий</div></div>');
    }
}
function re_count_finance(data){
    if(data["seor"]){
        $("#count_pay .seor_coin").text(data["seor"]);
    }
    if(data["ads"]){
        $("#count_day .day").text(data["ads"]);
    }
}

$(".ads .active_deactive[data-approved='1']","#container_ads").click(function(){
    var $ad = $(this).parents(".ads");
    var status = '';
    if(!$(this).hasClass("active")){
        var status = 0;
        $(this).removeClass("active");
        $(this).addClass("disable");
        $(this).text("Деактивировать");
        var $count_element = $("#active_count");
        var $count_new = $("#disabled_count");
        
    }else{
        var status = 1;
        $(this).removeClass("disable");
        $(this).addClass("active");
        $(this).text("Активировать");
        var $count_element = $("#disabled_count");
        var $count_new = $("#active_count");
    }
    
    var old_count = ($count_element.text() * 1) - 1;
    if(old_count < 0){
        old_count = 0;
    }
    $count_element.text(old_count);
    var new_count = ($count_new.text() * 1) + 1;
    
    $count_new.text(new_count);
    
    var request = new ajax.get();
        
    request.after(function(data,$ad,$_get,remove_ads){
        if(data.error){
            alert(data.error);
        }else{
            if($_get["mark"] == "active" || $_get["mark"] == "disable"){
                $ad.remove();
            }
            remove_ads();
        }
    },['data',$ad, $_get,remove_ads]);

    request.query("/ajax/announces/active_deactive",{"id" : $ad.data("id"),"status" : status});
});

var $pop = $("#technical_modal");
$pop.on('hidden.bs.modal', function (e) {
    $(this).find(".modal_submit").off();
})
// Событие на удаление
$(".ads .drop_add","#container_ads").click(function(){
    var $ad = $(this).parents(".ads");
    
    
    var request = new ajax.get();
    
    request.after(function(data,$ad,$pop,remove_ads){
        $pop.find(".modal-title").html(data.title);
        if(data.content){
            $pop.find(".modal-body").html(data.content);
            $pop.find(".modal_submit").on("click",function(){
                var drop = new ajax.get();
                drop.after(function(data,$ad,$pop,remove_ads){
                    if(data.error){
                        $pop.find(".modal-body").html(data.error);
                    }
                    // Закрываем окно, и удаляем вакансию из списка.
                    $ad.remove();
                    remove_ads();
                    $pop.modal("hide");
                    location.reload();
                    
                },['data',$ad,$pop,remove_ads]);
                drop.query("/ajax/announces/drop",{"id" : data.id,"drop": 1});
            });
        }else if(data.error){
            $pop.find(".modal-body").html(data.error);
        }
    },['data',$ad,$pop,remove_ads]);

    request.query("/ajax/announces/drop",{"id" : $ad.data("id")});
    
    // Запрос на контент
    $pop.modal("show");
});

var $to_app = $('.to_app');
//$to_app.tooltip();
// Событие на поднятие
$to_app.click(function(){
    //$to_app.tooltip("hide");
    var $ad = $(this).parents(".ads");
    
    var request = new ajax.get();
    
    request.after(function(data,$ad,$pop,remove_ads){
        $pop.find(".modal-title").html(data.title);
        if(data.content){
            $pop.find(".modal-body").html(data.content);
            $pop.find(".modal_submit").on("click",function(){
                var drop = new ajax.get();
                drop.after(function(data,$ad,$pop,remove_ads){
                    if(data.error){
                        $pop.find(".modal-body").html(data.error);
                    }else{
                        $pop.find(".modal-body").html(data.content);
                        $pop.modal("hide");
                        location.reload();
                    }
                    
                },['data',$ad,$pop,remove_ads]);
                drop.query("/ajax/announces/up",{"id" : data.id,"up": 1});
            });
        }else if(data.error){
            $pop.find(".modal-body").html(data.error);
        }
    },['data',$ad,$pop,remove_ads]);

    request.query("/ajax/announces/up",{"id" : $ad.data("id")});
    $pop.modal("show");
});
var $public_btn = $('.public_btn');


$public_btn.click(function(){
    var $ad = $(this).parents(".ads");
    
    var request = new ajax.get();
    
    request.after(function(data,$ad,$pop,remove_ads,re_count_finance){
        $pop.find(".modal-title").html(data.title);
        if(data.content){
            $pop.find(".modal-body").html(data.content);
            $pop.find(".modal_submit").on("click",function(){
                var drop = new ajax.get();
                drop.after(function(data,$ad,$pop,remove_ads,re_count_finance){
                    if(data.error){
                        $pop.find(".modal-body").html(data.error);
                    }else{
                        $pop.find(".modal-body").html(data.content);
                        
                        // Делаем пересчет.
                        var $count_element = $("#draft_count");
                        var $count_new = $("#moder_count");
                        
                        var old_count = ($count_element.text() * 1) - 1;
                        if(old_count < 0){
                            old_count = 0;
                        }
                        $count_element.text(old_count);
                        var new_count = ($count_new.text() * 1) + 1;
                        
                        $count_new.text(new_count);
                        
                        re_count_finance(data);
                        
                        // Закрываем окно, и удаляем вакансию из списка.
                        $ad.remove();
                        remove_ads();
                        $pop.modal("hide");
                    }
                    // Закрываем окно, и удаляем вакансию из списка.
                    
                    
                },['data',$ad,$pop,remove_ads,re_count_finance]);
                drop.query("/ajax/announces/publish",{"id" : data.id,"publish": 1});
            });
        }else if(data.error){
            $pop.find(".modal-body").html(data.error);
        }
    },['data',$ad,$pop,remove_ads,re_count_finance]);

    request.query("/ajax/announces/publish",{"id" : $ad.data("id")});
    $pop.modal("show");
});

</script>