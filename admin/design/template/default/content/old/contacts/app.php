<?=$header?>
<form method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>" />
    <div class="contacts_app block">
        <?=$error?>
        <a href="<?=Url::root()?>/contacts/">Вернутся</a>
        <div class="type">
            <div class="info">
                Тип вашего обращения:
            </div>
            <div class="">
                <label>У меня:</label>
                <select name="type">
                    <?php
                        $select = '';
                        if(Request::param($_POST['type']) == 'error')
                            $select = 'selected="selected"';
                    ?>
                    <option <?=$select?>  value="error">проблема</option>
                    <?php
                        $select = '';
                        if(Request::param($_POST['type']) == 'wish')
                            $select = 'selected="selected"';
                    ?>
                    <option <?=$select?> value="wish">пожелание</option>
                </select>
            </div>
        </div>
        <div class="title clear">
            <h2 class="col-w-4 table">
                <span class="cell small">Тема:</span> 
                <input active-role="title" class="cell" type="text" name="title" value="<?=Request::param($_POST['title'])?>" />
            </h2>
        </div>
        <div class="message">
            <div class="info">
                Сообщение:
            </div>
            <textarea active-role="message" class="col-w-4" name="message"><?=Request::param($_POST['message'])?></textarea>
        </div>
        <div class="submit col-w-4 clear">
            <button class="btn btn-success btn-lg">Обратится</button>
        </div>
    </div>
</form>