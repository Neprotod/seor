<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes"/>
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <xsl:apply-templates select="head" />
    </xsl:template>
    
    <xsl:template match="head">
        <xsl:if test="@meta_title">
            <title><xsl:value-of select="@meta_title" /></title>
        </xsl:if>
        <xsl:if test="@description">
            <meta content="{@description}" name="description" />
        </xsl:if>
        <xsl:if test="@robots">
            <!-- https://developers.google.com/search/reference/robots_meta_tag?hl=ru описание роботов -->
            <meta content="{@robots}" name="robots" />
        </xsl:if>
        <xsl:if test="@meta_keywords">
            <meta content="{@meta_keywords}" name="keywords" />
        </xsl:if>
        <xsl:if test="@canonical">
            <link href="{@canonical}" rel="canonical" />
        </xsl:if>
        <xsl:apply-templates select="style" />
    </xsl:template>
    <xsl:template match="style">
        <xsl:value-of select="." disable-output-escaping="yes" />
    </xsl:template>
    <xsl:template match="*"></xsl:template>
    
</xsl:stylesheet>