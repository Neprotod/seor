<div>
    <?php
    foreach($posts as $p):
    ?>
    <a href="<?=$p['url']?>"><?=$p['title']?></a>
    <?php
    endforeach;
    ?>
</div>