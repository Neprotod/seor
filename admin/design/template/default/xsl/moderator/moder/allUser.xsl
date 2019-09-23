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
        <div id="user_group" class="table_box">
            <div class="table_box">
                <xsl:call-template name="header"/>
                <xsl:apply-templates select="moderator/moder"/>
            </div>
            <xsl:call-template name="footer"/>
        </div>
    </xsl:template>
    
    <xsl:template match="moder">
        <xsl:variable name="status">
            <xsl:choose>
                <xsl:when test="@status = '1'">
                    <div class="card alert-success status_element">ON</div>
                </xsl:when>
                <xsl:otherwise>
                    <div class="card alert-danger status_element">OFF</div>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <a data-help="Зайти в модератора" class="table_row table_link" href="{//moderator/@url}?user={@id}">
            <div class="table_cell align-middle">
                <xsl:value-of select="position()"/>
            </div>
            <div class="table_cell align-middle">
                <xsl:value-of select="@login"/>
            </div>
            <div class="table_cell align-middle">
                <xsl:value-of select="display_name"/>
            </div>
            <div class="table_cell align-middle">
                <span><xsl:value-of select="@email"/></span>
            </div>
            <div class="table_cell align-middle">
                <span><xsl:value-of select="@type"/></span>
            </div>
            <div class="table_cell align-middle">
                <span><xsl:value-of select="title"/></span>
            </div>
            <div class="table_cell align-middle text-secondary">
                <span><xsl:value-of select="@registered"/></span>
            </div>
            <div class="table_cell align-middle text-secondary">
                <span><xsl:copy-of select="$status"/></span>
            </div>
        </a>
    </xsl:template>
    <xsl:template name="header">
        <div class="table_row bg-light">
            <a data-help="Сортировка по ID" href="?sort=id" class="table_cell align-middle th">ID</a>
            <a data-help="Сортировка по Login" href="?sort=login" class="table_cell align-middle th">Логин (login)</a>
            <a data-help="Сортировка по Имени" href="?sort=display_name" class="table_cell align-middle th">Отображаемое имя (display_name)</a>
            <a data-help="Сортировка по Почте" href="?sort=email" class="table_cell align-middle th">email</a>
            <a data-help="Сортировка по Группе" href="?sort=type" class="table_cell align-middle th">Группа(type)</a>
            <a data-help="Сортировка по Имени группы" href="?sort=title" class="table_cell align-middle th">Имя группы(title)</a>
            <a data-help="Сортировка по Дате" href="?sort=registered" class="table_cell align-middle th">Дата регистрации</a>
            <a data-help="Сортировка по Статусу" href="?sort=status" class="table_cell align-middle th">Включен ли (status)</a>
        </div>
    </xsl:template>
    <xsl:template name="footer">
         <a class="table_row plus_elem" colspan="3" href="{//moderator/@url}?user=new">
            <div data-help="Добавляет нового модератора" class="svg_elem">
                <xsl:value-of select="//technical/plus" disable-output-escaping="yes"/>
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>