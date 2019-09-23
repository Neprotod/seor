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
        <div id="dop_menu">
            <div class="btn-group">
                <xsl:apply-templates select="dopMenu/item"/>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="item">
        <xsl:variable name="active">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@active = 'true'"> active</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <a href="{@link}" class="btn btn-secondary{$active}"><xsl:value-of select="name" /></a>
    </xsl:template>
    <xsl:template match="item[dop]">
       <xsl:variable name="active">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@active = 'true'"> active</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="btn-group" role="group">
            <button id="{@link}" type="button" class="btn btn-secondary dropdown-toggle {$active}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <xsl:value-of select="name" />
            </button>
            <div class="dropdown-menu" aria-labelledby="{@link}">
                <xsl:apply-templates select="dop"/>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="dop">
        <a class="dropdown-item" href="{@link}"><xsl:value-of select="name" /></a>
    </xsl:template>
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>