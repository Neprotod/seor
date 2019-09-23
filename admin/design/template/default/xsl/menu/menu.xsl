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
    <xsl:template match="/">
        <xsl:apply-templates select="menus/item"/>
    </xsl:template>
    
    <xsl:template match="item">
           
        <xsl:variable name="active">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@active = 'true'"> active</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <li class="menu_item{$active}">
            <a href="{@link}">
                 <span class="img">
                    <xsl:value-of select="svg" disable-output-escaping="yes" />
                </span>
                <span class="string"><xsl:value-of select="name" /></span>
            </a>
        </li>
    </xsl:template>
    
    <xsl:template match="name">
        <h1></h1>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>