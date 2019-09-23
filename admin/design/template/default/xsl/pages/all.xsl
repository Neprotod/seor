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
    <xsl:param name="seesion_id" select="pages/@session_id" />
    <xsl:param name="page" select="pages/page" />
    <xsl:param name="link" select="pages/@action" />
    <xsl:param name="parent" select="pages/page/parent_fields/field[@name = 'image_path']" />
    <xsl:param name="field" select="pages/page/fields/field[@name = 'image_path']" />
    <xsl:param name="field_image" select="pages/page/fields/field[@name = 'image']" />
    <xsl:param name="parent" select="pages/page/parent_fields/field[@name = 'image_path']" />
    <xsl:param name="params" select="pages/page/fields/field[@name = 'params']" />
    <xsl:param name="dop_field" select="pages/page/fields/field[@name = 'field']" />
    
    <!--Подключаем отображение медия данных-->
    <xsl:include href="::media::"/>
    
    <xsl:template match="/">
        <div id="category" class="vis" id_table="{$page/@id}" id_type="{$page/@id_type}">

            <div class="box">
                <a class="btn btn-outline-primary" href="{pages/@end_url}">К списку</a>
            </div>
            
            <xsl:call-template name="modal">
                <xsl:with-param name="id" select = "'modal_category'" />
                <xsl:with-param name="title" select = "'Выбор категории'" />
                <xsl:with-param name="class" select = "'category'" />
            </xsl:call-template>
            
            <xsl:call-template name="modal">
                <xsl:with-param name="id" select = "'modal_file'" />
            </xsl:call-template>
            <xsl:call-template name="modal">
                <xsl:with-param name="id" select = "'modal_filesystem'" />
            </xsl:call-template>
            
            <xsl:call-template name="modal_name" />
            
            <xsl:call-template name="modal_name">
                <xsl:with-param name="id" select = "'modal_create'" />
                <xsl:with-param name="title" select = "'Создание директории'" />
                <xsl:with-param name="input" select = "'Имя:'" />
                <xsl:with-param name="button" select = "'Создать'" />
            </xsl:call-template>
            
            <xsl:call-template name="modal_error" />
            <form method="post" action="{pages/@action}" data-toggle="validator" role="form">
                <input type="hidden" name="session_id" value="{$seesion_id}" />
                <xsl:call-template name="for_meta" />
                <xsl:call-template name="other" />
            </form>
        </div>
    </xsl:template>
    <xsl:template name="for_meta">
        <h5>
            <div>Режим предсоздания.</div>
            <div class="sub text-left w-50">Вам нужно выбрать тип данных, указать начальные значения и схоранить, после этого, продолжить заполнения.</div>
        </h5>
        <div class="card pages card-hide">
            <div class="card-header">
                <span class="name">Мета данные</span>
            </div>
            <div class="card-body for_close">
                <xsl:call-template name="meta" />
            </div>
        </div>
    </xsl:template>
    
    <xsl:template name="other">
        <xsl:variable name="parent_fix">
            <xsl:if test="$parent"><xsl:value-of select="concat($parent/@var,'/')"/></xsl:if>
        </xsl:variable>
        
        <xsl:variable name="fields_id">
            <xsl:choose>
                <xsl:when test="$field"><xsl:value-of select="$field/@id"/></xsl:when>
                <xsl:otherwise>new</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:variable name="h2" select="pages/page/fields/field[@name = 'h2']" />
        <xsl:variable name="h2_id">
            <xsl:choose>
                <xsl:when test="$h2"><xsl:value-of select="$h2/@id"/></xsl:when>
                <xsl:otherwise>new</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <div class="card pages card-hide">
            <div class="card-header">
                <span class="name">Описание страницы</span>
            </div>
            <div class="card-body for_close">
                <div class="parent_box_flex row">
                    <div class="col fix-width">
                        <div class="form-group content">
                            <label>Контент (содержимое)</label>
                            <textarea id="content_area" class="form-control" name="page[content]"><xsl:value-of select="$page/content"/></textarea>
                            <small class="form-text text-muted">Это содержимое страницы.</small>
                        </div>
                    </div>
                </div>
                <div class="box_table">
                    <button type="submit" class="btn btn-primary">Применить</button>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>