<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="html" 
        omit-xml-declaration="yes" 
        indent="yes" />
    

    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/appeal">
        <div class="info">
            <b>Обращение было создано: </b>
            <span><xsl:value-of select="@date" /></span>
        </div>
        <div id="message">
            <xsl:apply-templates select="/appeal/message"/>
        </div>
    </xsl:template>
    
    <xsl:template match="message">
        <xsl:variable name="class">
            <xsl:choose>
                <xsl:when test="@for = 'client'"></xsl:when>
                <xsl:otherwise>alert-info</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="post {@for} alert {$class}">
            <xsl:value-of select="." disable-output-escaping="yes" />
        </div>
    </xsl:template>
</xsl:stylesheet>