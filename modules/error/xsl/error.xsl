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
        <div id="error_message">
            <xsl:apply-templates select="/errors/box"/>
        </div>
    </xsl:template>
    
    <xsl:template match="box">
            <xsl:apply-templates select="alert"/>
    </xsl:template>
    
    <xsl:template match="alert">
        <xsl:variable name="type">
            <xsl:choose>
                <xsl:when test="@type != ''">alert-<xsl:value-of select="@type" /></xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <div class="alert_box">
            <xsl:attribute name="data-type">
                <xsl:value-of select="@type" />
            </xsl:attribute>
            <xsl:if test="boolean(@system)">
                <xsl:attribute name="data-system">true</xsl:attribute>
            </xsl:if>
            <xsl:if test="boolean(@role)">
                <xsl:attribute name="data-role">
                    <xsl:value-of select="@role" />
                </xsl:attribute>
                <xsl:if test="boolean(@tooltip)">
                    <xsl:attribute name="data-tooltip">
                        <xsl:value-of select="@tooltip" />
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="boolean(@select)">
                    <xsl:attribute name="data-select">
                        <xsl:value-of select="@select" />
                    </xsl:attribute>
                </xsl:if>
                <xsl:if test="boolean(@valid)">
                    <xsl:attribute name="data-valid">
                        <xsl:value-of select="@valid" />
                    </xsl:attribute>
                </xsl:if>
            </xsl:if>
            <xsl:if test="title != '' or message != ''">
                <div class="alert {$type}">
                    <button type="button" data-dismiss="alert" class="close hidden"><span>×</span></button>
                    <div>
                        <strong><xsl:value-of select="title" /></strong> <xsl:value-of disable-output-escaping="yes" select="concat(' ',message)" />
                    </div>
                </div>
            </xsl:if>
        </div>
    </xsl:template>
    
</xsl:stylesheet>