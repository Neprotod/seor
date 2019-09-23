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
    <xsl:template match="/">
        <section id="register" class="container-fluid big_container grey">
            <div class="container">
                <xsl:value-of select="user/error" disable-output-escaping="yes" />
                <form action="{user/@site}{user/@action}" method="post" class="was-validated">
                    <div id="register_img">
                        <div id="seor_logo_registr"></div>
                    </div>
                    <div id="register_input">
                        <div class="form-group control">
                            <input name="email" type="email" class="form-control" placeholder="Enter email" value="{user/@email}" active-role="email" required="required" autocomplete="off" />
                            <div class="invalid-feedback">Неправильно задан адрес почты</div>
                        </div>
                        <div class="form-group control">
                            <input name="password" type="password" class="form-control" placeholder="Password" value="{user/@password}" active-role="password" required="required" autocomplete="off" />
                        </div>
                        
                        <div class="form-group control radio_emploer row">
                            <div class="col box-left">
                                <label class="disable radio_controll">
                                    Соискатель
                                    <xsl:element name="input">
                                        <xsl:attribute name="class"></xsl:attribute>
                                        <xsl:attribute name="type">radio</xsl:attribute>
                                        <xsl:attribute name="name">employer</xsl:attribute>
                                        <xsl:attribute name="value">0</xsl:attribute>
                                        <xsl:if test="user/@employer = 0">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>
                                </label>
                            </div>
                            <div class="col box-right">
                                <label class="disable radio_controll">
                                    Работодатель
                                    <xsl:element name="input">
                                        <xsl:attribute name="class"></xsl:attribute>
                                        <xsl:attribute name="type">radio</xsl:attribute>
                                        <xsl:attribute name="name">employer</xsl:attribute>
                                        <xsl:attribute name="value">1</xsl:attribute>
                                        <xsl:if test="user/@employer = 1">
                                            <xsl:attribute name="checked">checked</xsl:attribute>
                                        </xsl:if>
                                    </xsl:element>
                                </label>
                            </div>
                        </div>
                        <div id="employer">
                            <!--Выбор физическое или юридическое лицо-->
                            <div class="form-group">
                                <select name="face" id="select_face" class="form-control menu-select">
                                    <option value="">Выберите тип</option>
                                    <option value="1">Юридическое лицо</option>
                                    <option value="0">Физическое лицо</option>
                                </select>
                                <div class="invalid-feedback">Вы должны выбрать.</div>
                            </div>
                            <div id="refinement" class="form-group">
                                <select name="company" class="form-control menu-select">
                                    <option value="">Тип компании</option>
                                    <xsl:apply-templates select="user/type[@employer = 1 and @legal = 1]" />
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button id="regist_button" disabled="disabled" class="btn btn-success btn_orange last string" type="submit">
                                Зарегистрироваться
                            </button>
                        </div>
                    </div>
                </form>
                <div id="register_politic">
                    <div class="small">
                        Нажимая на кнопку, я даю согласие на обработку персональных данных, соглашаюсь с <a href="#">политикой конфиденциальности</a> и <a href="#">правилами сайта</a>.
                    </div>
                    <div class="big">
                       <a href="{user/@site}/account"> У меня уже есть аккаунт</a>
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="user/type">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>
    
    <xsl:template match="*">
    </xsl:template>
</xsl:stylesheet>