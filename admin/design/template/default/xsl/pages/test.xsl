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
        
        <xsl:import href="D:/OpenServer5/OSPanel/domains/seor/admin/design/template/default/xsl/pages/include/Meta.xsl"/>  
        
        Шаблон корневого элемента
    -->
    <xsl:param name="seesion_id" select="pages/@session_id" />
    <xsl:param name="page" select="pages/page" />
    <xsl:param name="link" select="pages/@action" />
    <xsl:param name="parent" select="pages/page/parent_fields/field[@name = 'image_path']" />
    <xsl:param name="field" select="pages/page/fields/field[@name = 'image_path']" />
    <xsl:template match="/">
        <xsl:value-of select="1" disable-output-escaping="no" /> 
    </xsl:template>
    <xsl:include href="::media::"/>
    
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>