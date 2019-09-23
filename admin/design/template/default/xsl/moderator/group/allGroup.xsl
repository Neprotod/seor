<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <div id="user_group" class="table_box">
            <div class="table_box">
                <xsl:call-template name="header"/>
                <xsl:apply-templates select="moderator/moder "/>
            </div>
            <xsl:call-template name="footer"/>
        </div>
    </xsl:template>
    
    <xsl:template match="moder">
        <a data-help="Зайти в группу" class="table_row table_link" href="{//moderator/@url}?group={@id}">
            <div class="table_cell align-middle">
                <xsl:value-of select="position()"/>
            </div>
            <div class="table_cell align-middle">
                <xsl:value-of select="@type"/>
            </div>
            <div class="table_cell align-middle">
                <span><xsl:value-of select="title"/></span>
            </div>
            <div class="table_cell align-middle">
                <span><xsl:value-of select="description"/></span>
            </div>
        </a>
    </xsl:template>
    <xsl:template name="header">
        <div class="table_row bg-light">
            <div class="table_cell align-middle th">ID</div>
            <div class="table_cell align-middle th">Тип пользователя (type)</div>
            <div class="table_cell align-middle th">Название (title)</div>
            <div class="table_cell align-middle th">Описание (description)</div>
        </div>
    </xsl:template>
    <xsl:template name="footer">
         <a data-help="Добавляет новую группу" class="table_row plus_elem" colspan="3" href="{//moderator/@url}?group=new">
            <div class="svg_elem">
                <xsl:value-of select="//technical/plus" disable-output-escaping="yes"/>
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>