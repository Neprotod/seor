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
    <xsl:param name="site" select="menu/@site" />
    <xsl:template match="/">
        <table class="table">
            <tr>
                <td>
                    <a href="/account">Профиль</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="/account/pay">Пополнить счет</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="/account/ads">Вакансии</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="/account/settings">Настройки</a>
                </td>
            </tr>
            <tr>
                <td>
                    <a href="/account/support">Тех поддержка</a>
                </td>
            </tr>
        </table>
    </xsl:template>
</xsl:stylesheet>