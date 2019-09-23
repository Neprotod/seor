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
        <xsl:apply-templates select="styles/*" />
    </xsl:template>

    <xsl:template match="style_path">
        <xsl:apply-templates select="*" />
    </xsl:template>
    <xsl:template match="style_path/css">
        <link rel="stylesheet" type="text/css" href="{@link}" />
    </xsl:template>
    <xsl:template match="style_path/js">
        <script src="{@link}"></script>
    </xsl:template>
    
    <xsl:template match="style_string">
        <xsl:apply-templates select="*" />
    </xsl:template>
    
    <xsl:template match="style_string/css">
        <style type="text/css">
            <xsl:value-of select="." disable-output-escaping="yes" />
        </style>
    </xsl:template>
    <xsl:template match="style_string/js">
        <script>
            <xsl:value-of select="." disable-output-escaping="yes" />
        </script>
    </xsl:template>
    
    <xsl:template match="*"></xsl:template>
    
</xsl:stylesheet>