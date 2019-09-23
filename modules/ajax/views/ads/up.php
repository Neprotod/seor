<table class="table_up">
    <tr>
        <td>
            <div>"<?=$ads_title?>"</div>
        </td>
        <td class="text-right padding-left">
            <div class="title_price">C вашего счета снимет:</div>
            <?php
                if($cost == 0):
            ?>
            <div><b>1</b> бесплатное объявление</div>
            <?php
                else:
            ?>
            <div><b><?=$cost?></b><span class="coin_sing"></span></div>
            <?php
                endif;
            ?>
        </td>
    </tr>
</table>