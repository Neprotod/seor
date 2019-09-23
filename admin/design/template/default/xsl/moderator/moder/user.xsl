<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:param name="seesion_id" select="moderator/@session_id" />
    <xsl:template match="/">
        <!-- Переменная для переключателя -->
        <div id="user_group" class="table_box moderator">
            <div class="box">
                <a class="btn btn-outline-primary" href="{//moderator/@url}">К списку</a>
            </div>
             <xsl:if test="moderator/@activation_key != ''">
                <div id="activation_key" class="card">
                    <div>Данный модератор ожидает активации.</div>
                    <div>Включение его вручную будет равносильно подтверждению.</div>
                </div>
            </xsl:if>
            <form method="post" action="{moderator/@action}" data-toggle="validator" role="form">
                <input type="hidden" name="session_id" value="{$seesion_id}" />
                <div class="group_box card">
                    <!-- Запускает форму группы -->
                    <xsl:call-template name="moder">
                        <xsl:with-param name="moder" select = "moderator/moder" />
                    </xsl:call-template>
                </div>
            </form>
        </div>
    </xsl:template>
    
    <!--
        Шаблон отображения группы
    -->
    <xsl:template name="moder">
        <xsl:param name="moder" />

        <xsl:variable name="stat">
            <xsl:choose>
                <xsl:when test="$moder/@status = 1">1</xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="id">
            <xsl:choose>
                <xsl:when test="$moder/@id != ''"><xsl:value-of select="$moder/@id"/></xsl:when>
                <xsl:otherwise>new</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="for_h4">
            <xsl:choose>
                <xsl:when test="$id = 'new'">Создание модератора</xsl:when>
                <xsl:otherwise>Описание модератора</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <h4><xsl:value-of select="$for_h4"/></h4>
        <xsl:if test="$moder/@id">
            <input type="hidden" name="moder[id]" value="{$moder/@id}" />
            <input type="hidden" name="moder[registered]" value="{$moder/@registered}" />
        </xsl:if>
        <div id="registered" class="text-secondary sub"><xsl:value-of select="$moder/@registered"/></div>
        <div class="form-row">
            <div class="form-group col-md-6 control">
                <label>Логин</label>
                <input name="moder[login]" type="text" class="form-control" value="{$moder/@login}" required="required" active-role="login" data-help="В логине могут быть только буквы и цифры." />
            </div>
            <div class="form-group col-md-6 control">
                <label>Отображаемое имя</label>
                <input name="moder[display_name]" type="text" class="form-control" value="{$moder/display_name}" required="required" active-role="display_name" />
            </div> 
        </div>
        <div class="form-row">
            <div class="form-group col-md-6 control">
                <label>Почта</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">@</span>
                    </div>
                    <input name="moder[email]" type="text" class="form-control" placeholder="Почта" value="{$moder/@email}" required="required" active-role="email" />
                </div>
            </div>
            <div class="form-group col-md-6 control">
                <label data-help="Будет сгенерирован ключ активации и отправлен на почту" class="padding-top">
                    <!-- Кнопка отправки подтверждения -->
                    <xsl:element name="input">
                        <xsl:if test="boolean($moder/@active)">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="name">moder[active]</xsl:attribute>
                        <xsl:attribute name="type">checkbox</xsl:attribute>
                    </xsl:element>
                    <span>Отправить письмо активации?</span>
                </label>
            </div> 
        </div>
        <div class="form-row">
            <div class="form-group col-md-6 control">
                <label>Пароль</label>
                <input name="moder[pass]" type="password" class="form-control" value="{$moder/@pass}" active-role="pass" />
            </div>
            <div class="form-group col-md-6 control">
                <label>Повторить пароль</label>
                <input name="moder[pass_check]" type="password" class="form-control" value="{$moder/@pass}" active-role="pass_check" />
            </div> 
        </div>

        <div class="form-group control">
            <label>Группа пользователей</label>
            <select name="moder[id_type]" class="custom-select" active-role="id_type" data-help="Пользователь обязательно должен быть в группе пользователей">
                <option value="">Выберите группу</option>
                <xsl:apply-templates select="moderator/type">
                    <xsl:with-param name="id_type" select = "$moder/@id_type" />
                </xsl:apply-templates>
            </select>
        </div>
        <div class="form-group">
            <label>Включен ли модератор</label>
            <div id="card_checker" class="status_control">
                <input data-help="Слева - выключен" type="range" class="custom-range" name="moder[status]" min="0" max="1" step="1" value="{$stat}" />
            </div>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Применить</button>
        </div>
    </xsl:template>
    
    
    <xsl:template match="type">
        <xsl:param name="id_type"/>
        
        <xsl:element name="option">
            <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
            <xsl:if test="$id_type = @id">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="title"/>
            (<xsl:value-of select="@type"/>)
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>