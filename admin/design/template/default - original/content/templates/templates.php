<form method="post">
    <input name="session_id" type="hidden" value="<?=session_id()?>"  />
    <div class="templates block">
        <?php
        if(isset($templates)):
        foreach($templates as $template):
        ?>
        <a class="box <?=($template['status'] == 1)? 'active' : ''?>" href="<?=URL::root(NULL)?>/template/<?=$template['id']?>">
            <div class="wrap">
                <div class="name">
                    <span class="string">Имя темы</span>
                    <?=$template['name']?>
                </div>
            </div>
            <div class="wrap">
                <div class="title">
                    <span class="string">Заголовок</span>
                    <?=$template['title']?>
                </div>
            </div>
            <div class="wrap">
                <div class="description">
                    <span class="string">Описание</span>
                    <?=(isset($template['description']))? $template['description'] : 'Нет описания'?>
                </div>
            </div>
            <div class="wrap">
                <div class="status">
                    <span class="string">Статус</span>
                    <?=($template['status'] == 1)? 'Данная тема используется' : ''?>
                </div>
            </div>
        </a>
        <?php
        endforeach;
        else:
        ?>
        <div class="not_fond">Нет тем</div>
        <?php
        endif;
        ?>
    </div>
</form>