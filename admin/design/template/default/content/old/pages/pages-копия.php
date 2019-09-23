<div class="pages block">
    <?php
    foreach($pages as $key => $page):
    $id = Request::param($page['id']);
    ?>
    <div class="page to_frame loading">
        <div class="box">
            <div class="top_box">
                <div class="id"><?=$id?></div>
                <div class="title">
                    <span class="string"><?=Request::param($page['title'])?></span>
                </div>
                <div class="url">
                    <span class="bold">Url: </span>
                    <span class="value"><?=Request::param($page['url'])?></span>
                </div>
            </div>
            <div class="bottom_box">
                <div class="index">
                    <span class="bold">Индексация: </span>
                    <span class="value"><?=Request::param($page['robots'])?></span>
                </div>
                <div class="box_controll">
                    <div class="container">
                        <a class="status <?=(!empty($page['status']))? 'enabled' : 'disabled'?>" href="<?=Url::i()->root()?>/pages/status/<?=$id?>/<?=(empty($page['status']))? '1' : '0'?>">
                            <?=(!empty($page['status']))? 'Активен' : 'Отключен'?>
                        </a>
                    </div>
                    <div class="container">
                        <a class="drop" href="/pages/drop/<?=$id?>">
                            Удалить
                        </a>
                    </div>
                </div>
                <div class="box_information">
                    <span class="value"><?=(!empty($page['comment_status']))? "Комментарии включены" : "Комментарии отключены"?></span>
                </div>
                <div class="box_date">
                    <?=(!empty($page['modified']))? $page['modified'] : $page['date']?>
                </div>
            </div>
        </div>
    </div>
    <?php
    endforeach;
    ?>
</div>