<div class="pages block">
    <a href="<?=Url::i()->root()?>/pages/create">Создать страницу</a>
    <?php
    foreach($pages as $key => $page):
    ?>
    <div class="page to_frame loading">
        <a class="bold" href="<?=Url::i()->root()?>/pages/page/get/<?=$page['id']?>"><?=$page['title']?></a><br />
        <span class="bold">URL: </span><?=$page['url']?>
    </div>
    <?php
    endforeach;
    ?>
</div>