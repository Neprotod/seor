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
    <xsl:param name="temp" select="user/@active" />
    <xsl:template match="/">
        <section id="register" class="container-fluid big_container grey">
            <div class="container">
                <div class="alert alert-info">
                    <xsl:choose>
                        <xsl:when test="$temp = 1">
                            <xsl:call-template name="active" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:call-template name="regist" />
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    <div>
                        В случай проблем обращайтесь в <a href="{user/@site}/support">тех поддержку</a>.
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template name="regist">
        <h4>В базе нет такого ключа.</h4>
        <div>
             Скорее всего вы попали сюда по ошибке но вы можете <a href="{user/@site}/registr">зарегистрироваться</a>.
        </div>
    </xsl:template>
    <xsl:template name="active">
        <h4>Пользователь уже активирован.</h4>
        <div>
            Вы можете просто <a href="{user/@site}/account">войти в систему</a>.
        </div>
    </xsl:template>
</xsl:stylesheet>