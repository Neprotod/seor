<div class="description">
    <div class="information-box">
        <h3>Описание</h3>
        <div class="row shift space">
            <div class="title form-group">
                <label class="name">Заголовок</label>
                <div class="controll">
                    <input active-role="title" class="col-md-12" type="text" name="title" value="<?=Request::param($param['title'])?>" />
                </div>
            </div>
            <div class="url form-group">
                <label class="name">URL</label>
                <div class="control">
                    <input active-role="url" type="text" class="col-md-8" name="url" value="<?=Request::param($param['url'])?>" />
                </div>
            </div>
        </div>
    </div>
    <div class="information-box">
        <h2>Дата</h2>
        <div class="date row shift space">
            <div class="create">
                <span class="name">Дата создания</span>
                <span class="string"><?=Request::param($param['date'])?></span>
            </div>
            <div class="modified">
                <?php
                if(!empty($param['modified'])):
                ?>
                <span class="name">Дата изменения</span>
                <span class="string"><?=$param['modified']?></span>
                <?php
                else:
                ?>
                <span class="name">Не изменялся</span>
                <?php
                endif;
                ?>
            </div>
        </div>
    </div>
    <div class="information-box">
        <h2>Статус</h2>
        <div class="status row shift space">
            <label>
                <input <?=(isset($param['status']) AND $param['status'] == 1)? 'checked="checked"' : '' ?> type="radio" name="status" value="1" /> 
                Активен
            </label>
            <label>
                <input <?=(isset($param['status']) AND $param['status'] == 0)? 'checked="checked"' : '' ?> type="radio" name="status" value="0" /> 
                Откличен
            </label>
        </div>
    </div>
    <div class="information-box">
        <h2>Мета данные</h2>
        <div class="meta row shift space">
            <div class="meta_title form-group">
                <label class="name ">Мета заголовок</label>
                <div class="contorl">
                    <input type="text" class="col-md-12" name="meta_title" value="<?=Request::param($param['meta_title'])?>" />
                </div>
            </div>
            <div class="meta_description form-group">
                <span class="name">Мета описание</span>
                <div class="string">
                    <textarea class="col-md-12" name="description"><?=Request::param($param['description'],TRUE)?></textarea>
                </div>
            </div>
            <div class="meta_keywords">
                <span class="name" style="font-weight:bold;">Сделать выпадающий список</span>
            </div>
        </div>
    </div>
    <div class="information-box">
        <h3>Индексация</h3>
        <div class="robots">
            <div class="form-group">
                <div class="index space">
                    <label>
                        <input <?=(isset($robots['index']) AND $robots['index'] == 1)? 'checked="checked"' : '' ?> id="index" type="radio" name="robots[index]" value="index" /> 
                        <span class="name">Индексировать</span>
                    </label>
                    <label>
                        <input <?=(isset($robots['noindex']) AND $robots['noindex'] == 1)? 'checked="checked"' : '' ?> type="radio" name="robots[index]" value="noindex" /> 
                        <span class="name">Не индексировать</span>
                    </label>
                </div>
                <div class="follow space">
                    <label>
                        <input <?=(isset($robots['follow']) AND $robots['follow'] == 1)? 'checked="checked"' : '' ?> type="radio" name="robots[follow]" value="follow" />
                        <span class="name">Переходить по ссылке</span>
                    </label>
                    <label>
                        <input <?=(isset($robots['nofollow']) AND $robots['nofollow'] == 1)? 'checked="checked"' : '' ?> type="radio" name="robots[follow]" value="nofollow" />
                        <span class="name">Не переходить по ссылке</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>