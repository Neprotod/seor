<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="html" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <xsl:variable name="host" select="/contacts/host" />
    <xsl:variable name="for" select="/contacts/for" />
    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <div id="appeal" class="table">
            <xsl:apply-templates select="contacts/appeal"/>
        </div>
    </xsl:template>
    
    <xsl:template match="appeal">
        <xsl:variable name="message">
            <xsl:choose>
                <xsl:when test="@new = $for">
                    <![CDATA[<div class="new_message"></div>]]>
                </xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="appeal row {@type}">
            <a href="{$host}/contacts/get/{@id}" class="theme cell">
                <xsl:value-of select="@title" />
            </a>
            <div class="cell">
                <xsl:value-of select="$message" disable-output-escaping="yes" />
            </div>
            <xsl:if test="$for = 'client'">
                <div class="cell">
                    <button name="delete_appeal" value="{@id}" class="btn btn-danger">Удалить обращение</button>
                </div>
            </xsl:if>
        </div>
    </xsl:template>
</xsl:stylesheet>