<?=$header?>
<div class="contacts_get block">
    <a href="<?=Url::root()?>/contacts/">Вернутся</a>
    <?=$error?>
    <?=$xml?>
    <form method="post">
        <input type="hidden" name="session_id" value="<?=session_id()?>" />
        <div class="message">
            <div class="info">
                Сообщение:
            </div>
            <textarea class="col-w-4" name="message"><?=Request::param($_POST['message'])?></textarea>
        </div>
        <div class="submit col-w-4 clear">
            <button class="btn btn-success btn-lg">Обратится</button>
        </div>
    </form>
</div>