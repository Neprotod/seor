<?=$content?>
<script>
    var $time = $(".time","#contact_box");
    if($time.length != 0){
        var time = $time.text();
        var regexp = /([0-9]{1,}):([0-9]{1,}):([0-9]{1,})/g;
        var match = regexp.exec(time);
        
        var date = new Date().getTime();
        var hour_second = match[1] * 3600;
        var min_second = match[2] * 60;
        var second = match[3];
        var milesecond_all = (hour_second + min_second + second) * 1000;

        date = date + milesecond_all;

        date = new Date(new Date().setTime(date)).getTime();
        
        var interval = window.setInterval(function(){
            // Защита, если дата уже стала больше
            if(date < new Date().getTime()){
                location.reload();
                clearInterval(interval);
                return;
            }
            match[3] = (match[3]*1) - 1;
            if(match[3] < 0){
                match[3] = 59;
                match[2] = (match[2]*1) - 1;
                if(match[2] < 0){
                    match[2] = 59;
                    match[1] = (match[1]*1) - 1;
                    if(match[1] < 0){
                        match[1] = 0;
                        match[2] = 0;
                        match[3] = 0;
                        clearInterval(interval);
                        location.reload();
                    }
                }
            }
            for(var i = match.length;i > 0;i--){
                if(match[i-1] < 10)
                    match[i-1] = "0" + parseInt(match[i-1]);
            }
            $time.text(match[1]+":"+match[2]+":"+match[3]);
        }, 1000);
    }
</script>