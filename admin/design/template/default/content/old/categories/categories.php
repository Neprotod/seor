<div class="pages block">
    <?php
    foreach($categories as $key => $category):
    ?>
    <div class="categories to_frame loading">
        <a class="bold" href="<?=Url::i()->root()?>/categories/category/get/<?=$category['id']?>"><?=$category['title']?></a><br />
        <span class="bold">URL: </span><?=$category['url']?>
    </div>
    <?php
    endforeach;
    ?>
</div>