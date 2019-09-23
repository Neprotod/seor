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
        <div class="ads">
            <div id="container_ads">
                <xsl:apply-templates select="//ads/ads" />
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="ads">
        <xsl:variable name="length" select="'250'" />
        <xsl:variable name="doted">
            <xsl:if test="string-length(description) &gt; $length">...</xsl:if>
        </xsl:variable>
        
        <xsl:variable name="padding_top">
            <xsl:if test="position() = 1">padding-top</xsl:if>
        </xsl:variable>
        
        <div class="ads" data-id="{@id}">
            <div class="cell info_ads">
                <div class="cell_box">
                    <a class="h5 margin-bottom" href="{//ads/@url}/{@id}">
                        <xsl:value-of select="@title" />
                    </a>
                    <div class="description pt_sans"><xsl:value-of select="concat(normalize-space(substring(description,0,$length)),$doted)" /></div>
                </div>
            </div>
            <div class="ads_salary cell">
                <div class="cell_box">
                    <div class="h5 pt_sans">
                        <xsl:choose>
                            <xsl:when test="@seen = 0">
                                Не просмотрена
                            </xsl:when>
                            <xsl:when test="@seen = 2">
                                Модерируется
                            </xsl:when>
                        </xsl:choose>
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
    
</xsl:stylesheet>