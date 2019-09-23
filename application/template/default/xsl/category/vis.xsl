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
    <xsl:param name="root" select="categories/@root" />
    <xsl:param name="site" select="categories/@site" />
    <xsl:template match="/">
        <section class="container-fluid big_container white">
            <div class="container">
                <h1 class="text-left h2 vis_h1"><xsl:value-of select="categories/@title" /></h1>
            </div>
        </section>
        <xsl:apply-templates select="categories/category" />
        
    </xsl:template>
    
    <xsl:template match="category">
        <section class="container-fluid big_container white visa_section">
            <div class="container">
                <h2 class="text-center"><xsl:value-of select="@title" />:</h2>
                <div class="visa_box">
                    <xsl:apply-templates select="page" />
                </div>
                <div class="clear"></div>
            </div>
        </section>
    </xsl:template>
    <xsl:template match="page">
        <a href="{$site}/{@url}" class="visa left">
            <img class="img" src="/media/{@var}" alt="{@title}"/>
            <span class="string"><span><xsl:value-of select="@title" /></span></span>
        </a>
    </xsl:template>
    <xsl:template match="*"></xsl:template>
</xsl:stylesheet>