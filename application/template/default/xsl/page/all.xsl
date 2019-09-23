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
        <section id="all" class="container-fluid big_container white">
            <div class="container">
                <h1 class="h2 box"><xsl:value-of select="page/@title" /></h1>
                <div class="">
                     <xsl:value-of select="page/content" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="*"></xsl:template>
</xsl:stylesheet>