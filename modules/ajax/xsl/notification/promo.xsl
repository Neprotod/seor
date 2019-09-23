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
        <xsl:choose>
            <xsl:when test="note/@error = 1">
                <div class="text-danger">
                    Промокод уже использован.
                </div>
            </xsl:when>
            <xsl:when test="note/@error = 2">
                <div class="text-danger">
                    Промокод не найден или просрочен.
                </div>
            </xsl:when>
            <xsl:otherwise>
                <div class="text-success">
                    <xsl:if test="note/@seor &gt; 0">
                        <div>+<xsl:value-of select="floor(note/@seor)" /> Seor Coint на счет</div>
                    </xsl:if>
                    <xsl:if test="note/@days &gt; 0">
                        <div>+<xsl:value-of select="note/@days" /> бесплатных дней аккаунта</div>
                    </xsl:if>
                    <xsl:if test="note/@clicks &gt; 0">
                        <div>+<xsl:value-of select="note/@clicks" /> бесплатных кликов по объявлениям</div>
                    </xsl:if>
                    <xsl:if test="note/@ads &gt; 0 and note/@employer = 1">
                        <div>+<xsl:value-of select="note/@ads" /> бесплатных объявлений</div>
                    </xsl:if>
                </div>
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
    
    <xsl:template match="*">
    </xsl:template>
</xsl:stylesheet>