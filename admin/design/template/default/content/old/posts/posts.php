<div class="posts block">
    <?php
    foreach($posts as $key => $post):
    ?>
    <div class="post to_frame loading">
        <a class="bold" href="<?=Url::i()->root()?>/posts/post/get/<?=$post['id']?>"><?=$post['title']?></a><br />
        <span class="bold">Внутренний URL: </span><?=$post['url']?>
    </div>
    <?php
    endforeach;
    ?>
</div>