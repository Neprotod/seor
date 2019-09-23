<?=$header?>
<div class="contacts block">
    <div class="information_for_user info">
        <p>Вы можете создать обращение если у вас есть проблема или пожелание.</p>
    </div>
    <?php
    if(!empty($xml)):
    ?>
    <?=$xml?>
    <?php
    else:
    ?>
    <div class="information_for_appeal">
        <p>На данные момент нет обращений.</p>
    </div>
    <?php
    endif;
    ?>
    <div class="appeal">
        <a class="app btn btn-success" href="<?=Url::i()->root()?>/contacts/app">Создать обращение</a>
    </div>
</div>