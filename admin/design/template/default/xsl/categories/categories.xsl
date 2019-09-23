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
    <xsl:param name="link" select="categories/@link" />
    <xsl:template match="/">
        <div id="pages">
            <div class="card pages">
                <div class="card-header">
                    <span class="name">Все категории</span>
                    <a class="btn btn-outline-secondary" href="#">Добавить</a>
                </div>
                <div class="card-body">
                    <table class="page_table">
                        <xsl:apply-templates select="categories/category[@parent_id = '']"/>
                    </table>  
                </div>
            </div>
        </div>
        
    </xsl:template>
    
    <xsl:template match="category[@parent_id = '']">
        <xsl:variable name="cat" select="../category[@parent_id = current()/@id]" />
        
        <xsl:call-template name="category" />
        
        <xsl:if test="$cat">
            <tr>
                <td colspan="6">
                    <table class="page_table sub_cat">
                        <xsl:apply-templates select="$cat"/>
                    </table> 
                </td>
            </tr>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="category">
        <xsl:variable name="cat" select="../category[@parent_id = current()/@id]" />
        
        <xsl:call-template name="category"/>
        
         <xsl:if test="$cat">
            <tr>
                <td colspan="6">
                    <table class="page_table sub_cat">
                        <xsl:apply-templates select="$cat"/>
                    </table> 
                </td>
            </tr>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="category">
        <tr>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="//technical/svg/category" disable-output-escaping="yes" />
                </div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="@url" />
                </div>
                <div class="sub">url</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="title" />
                </div>
                <div class="sub">Заголовок</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="meta_title" />
                </div>
                <div class="sub">Мета заголовок</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="@robots_name" />
                </div>
                <div class="sub">Индексация</div>
            </td>
            <td class="position-relative">
                <div class="vertical_line"></div>
                <div>
                    <xsl:value-of select="@file_name" />
                </div>
                <div class="sub">Тип</div>
            </td>
            <td class="position-relative">
                <div>
                    <a class="btn btn-outline-secondary" href="{$link}/{@id}">Изменить</a>
                </div>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>