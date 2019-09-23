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
            <xsl:when test="note/notification">
                <xsl:apply-templates select="note/notification" />
            </xsl:when>
            <xsl:otherwise>
                
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>
    <xsl:template match="notification">
        <xsl:variable name="seen">
            <xsl:if test="@seen = 0">notification_seen</xsl:if>
        </xsl:variable>
        <div class="notification_group d-flex {$seen}">
            <div class="notification_image">
                <div class="notification_logo notification_type_{@type}"></div>
            </div>
            <div class="notification_info">
                <div class="notification_title">
                    <xsl:value-of select="title" />
                </div>
                <div class="notification_content">
                    <xsl:value-of select="content" disable-output-escaping="yes" />
                </div>
                <div class="notification_time">
                    <xsl:value-of select="translate(substring(@time,0,string-length(@time) - 2),'-','/')" />
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">
    </xsl:template>
</xsl:stylesheet>