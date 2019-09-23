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
    <xsl:param name="site" select="//settings/@site" />
    <xsl:param name="url" select="//settings/@url" />
    <xsl:param name="user" select="//settings/user" />
    <xsl:param name="pass" select="//settings/pass" />

    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="settings/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="settings" class="container-fluid big_container white">
            <form class="container" action="{settings/@site}{settings/@action}" method="post">
                <h1 class="h4">Настройки</h1>
                <div class="row">
                    <div class="col">
                        <h2 class="h5 small">Смена почты</h2>
                        <div class="form-group control">
                            <div class="old_email title">
                                <div>Текущая почта:</div>
                                <div class="mail"><xsl:value-of select="$user/@email" /></div>
                            </div>
                            <input name="new_email" type="email" class="form-control input-small" placeholder="Новый email" value="{$user/@new_email}" active-role="new_email" autocomplete="off" />
                        </div>
                        <button name="submit" value="1" class="btn btn_green" type="submit">Сменить</button>
                    </div>
                    <div class="col line">
                        <div class="line_vertical"></div>
                    </div>
                    <div class="col">
                        <h2 class="h5 small">Смена пароля</h2>
                        <div class="form-group control">
                            <div class="title">
                                <span>Старый пароль:</span>
                            </div>
                            <input id="password" name="password" type="text" class="form-control input-small" placeholder="" value="{$pass/@password}" active-role="password" autocomplete="off" />
                        </div>
                        <div class="form-group control">
                            <div class="title">
                                <span>Новый пароль:</span>
                            </div>
                            <input id="new_password" name="new_password" type="text" class="form-control input-small" placeholder="" value="{$pass/@new_password}" active-role="new_password" autocomplete="off" />
                        </div>
                        <div class="form-group control">
                            <div class="title">
                                <span>Подтверждение пароля:</span>
                            </div>
                            <input id="repeat_password" name="repeat_password" type="text" class="form-control input-small" placeholder="" value="{$pass/@repeat_password}" active-role="repeat_password" autocomplete="off" />
                        </div>
                        <button name="submit" value="2" class="btn btn_green" type="submit">Сменить</button>
                    </div>
                    <div class="col line">
                        <div class="line_vertical"></div>
                    </div>
                    <div class="col">
                        <h2 class="h5 small">Удаление Аккаунта</h2>
                        <div id="drop_accaunt" class="btn btn-danger">Удалить аккаунт</div>
                    </div>
                </div>
            </form>
            
        </section>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>