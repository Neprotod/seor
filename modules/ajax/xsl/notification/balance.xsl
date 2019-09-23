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
    <xsl:param name="small_days">
        <xsl:choose>
            <xsl:when test="balance/@days &lt;= 1">very_low</xsl:when>
            <xsl:when test="balance/@days &lt;= 5">low</xsl:when>
        </xsl:choose>
    </xsl:param>
    <xsl:param name="small_seor">
        <xsl:if test="balance/@seor &lt;= 5">low</xsl:if>
    </xsl:param>
    <xsl:template match="/">
        <table class="table">
            <tr class="{$small_seor}">
                <td>Seor Coin</td>
                <td><xsl:value-of select="balance/@seor" /></td>
            </tr>
            <tr class="{$small_days}">
                <td>Осталось дней аккаунта</td>
                <td><xsl:value-of select="balance/@days" /></td>
            </tr>
            <tr>
                <td>Бесплатные клики</td>
                <td><xsl:value-of select="balance/@clicks" /></td>
            </tr>
            <xsl:if test="balance/@employer = 1">
                <tr>
                    <td>Бесплатные объявления</td>
                    <td><xsl:value-of select="balance/@ads" /></td>
                </tr>
            </xsl:if>
            <tr>
                <td colspan="2" class="text-center">
                    <a class="btn btn_green" href="/account/pay">Пополнить</a>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="text-center">
                    <div id="promo">Ввести промокоды</div>
                </td>
            </tr>
        </table>
        <script>
            $("#promo").one("click",function(){
                header.promo();
            });
        </script>
    </xsl:template>
    
    <xsl:template match="*">
    </xsl:template>
</xsl:stylesheet>