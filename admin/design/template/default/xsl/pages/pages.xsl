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
    <xsl:param name="get" select="pages/@get" />
    <xsl:param name="root_url" select="'/admin/pages/page'" />
    
    <xsl:template match="/">
        <div id="pages">
            <xsl:call-template name="default" />
            <xsl:call-template name="categories" />
            <xsl:call-template name="pages" />
        </div>
    </xsl:template>
    
    <xsl:template name="default">
        <xsl:call-template name="inside">
            <xsl:with-param name="title" select="'Главная'" />
            <xsl:with-param name="elem" select="pages/default" />
        </xsl:call-template>
    </xsl:template>
    
    <xsl:template name="categories">
        <xsl:value-of select="//technical/categories" disable-output-escaping="yes" /> 
    </xsl:template>
    
    <xsl:template name="pages">
        <xsl:call-template name="inside">
            <xsl:with-param name="title" select="'Страницы в категории'" />
            <xsl:with-param name="elem" select="pages/page" />
            <xsl:with-param name="add" select="'add'" />
        </xsl:call-template>
    </xsl:template>
    
    <xsl:template name="inside">
        <xsl:param name="title" />
        <xsl:param name="elem" />
        <xsl:param name="add" />

        <div class="card pages">
            <div class="card-header">
                <span><xsl:value-of select="$title" /></span>
                <xsl:if test="$add">
                    <span class="button">
                        <a class="btn btn-outline-secondary" href="{$root_url}/new{$get}">Добавить</a>
                    </span>
                </xsl:if>
            </div>
            <div class="card-body">
                <xsl:choose>
                <xsl:when test="$elem">
                    <table class="page_table">
                        <xsl:apply-templates select="$elem" />
                    </table>
                </xsl:when>
                <xsl:otherwise>
                    Пусто
                </xsl:otherwise>
            </xsl:choose>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="default | page">
        <xsl:variable name="not_url">
            <xsl:if test="not(normalize-space(@url))">not_url</xsl:if>
        </xsl:variable>
        <tr id_page="{@id}">
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="//technical/svg/page" disable-output-escaping="yes" />
                </div>
            </td>
            <td class="position-relative {$not_url}">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@url" />
                </div>
                <div class="sub">url</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@title" />
                </div>
                <div class="sub">Заголовок</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@meta_title" />
                </div>
                <div class="sub">Мета заголовок</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@robots_name" />
                </div>
                <div class="sub">Индексация</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@file_name" />
                </div>
                <div class="sub">Тип</div>
            </td>
            <td class="position-relative text-center">
                <div class="vertical_line"></div>
                <div class="string">
                    <xsl:value-of select="@status" />
                </div>
                <div class="sub">Статус</div>
            </td>
            <td class="position-relative">
                <div>
                    <a class="btn btn-outline-secondary" href="{$root_url}/{@id}{$get}">Изменить</a>
                </div>
            </td>
            <xsl:if test="@file_name != 'default'">
                <td class="position-relative">
                    <div>
                        <a class="btn btn-outline-secondary" href="/admin/pages/drop/{@id}">Удалить</a>
                    </div>
                </td>
            </xsl:if>
            
        </tr>
    </xsl:template>
    
    <xsl:template match="*"></xsl:template>
    
</xsl:stylesheet>