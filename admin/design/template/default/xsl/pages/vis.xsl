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
    
    <xsl:template name="pred">
        <h5>
            <div>Нет категории. Режим предсознания.</div>
            <div class="sub text-left w-50">В режиме предсознания указывается тип данных и категория, после этого страница создается и заполняются дополнительные данные. Обратите внимание, что в режиме предсоздания не указывается URL, не забудьте указать его в дальнейшем.</div>
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
    <xsl:template name="for_meta">
        <h5>
            <div>Режим модерирования.</div>
            <div class="sub text-left w-50">Изменение типа данных повлечет глобальные изменения вывода содержимого.</div>
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
                <div class="form-group">
                    <label>Флаг</label>
                    <div class="d-flex align-items-center">
                        <xsl:if test="$parent_fix != ''">
                            <div class="">
                               <xsl:value-of select="$parent_fix"/>  
                               <input name="fields[parent][image_path]" type="hidden" value="{$parent/@var}" /> 
                            </div>
                        </xsl:if>
                        <span id="image_string"><xsl:value-of select="substring-after($field/@var,$parent_fix)"/></span>
                        <div class="padding-left">
                           <img id="image_flag" src="/media/{$field/@var}" />
                           <input name="fields[{$fields_id}][image_path]" type="hidden" id="directory_input" class="form-control" value="{substring-after($field/@var,$parent_fix)}" />
                           
                        </div>
                        <div class="padding-left">
                             <xsl:call-template name="modal_button">
                                <xsl:with-param name="id" select = "'modal_file'" />
                                <xsl:with-param name="title" select = "'Выбрать флаг'" />
                            </xsl:call-template>
                        </div>
                       
                    </div>
                    <small class="form-text text-muted">Это meta_title страницы.</small>
                </div>
                <div class="parent_box_flex row">
                    <div class="col-auto position-relative">
                        <div id="vis_image" id_fields="{$field_image/@id}">
                            <h2 class="h2 box">
                                <xsl:value-of select="$page/title" />
                            </h2>
                            <img class="image" src="/{$field_image/@var}" />
                        </div>
                        <input id="input_image_file" type="file" style="display:none;" />
                        <div class="padding-top">
                            <xsl:call-template name="modal_button">
                                <xsl:with-param name="id" select = "'modal_filesystem'" />
                                <xsl:with-param name="title" select = "'Выбрать файл'" />
                            </xsl:call-template>
                        </div>
                    </div>
                    <div class="col fix-width">
                        <div class="form-group max-width">
                            <label>Заголовок h2</label>
                            <input name="fields[{$h2_id}][h2]" type="text" class="form-control" placeholder="Заголовок описания" value="{$h2/@var}" required="required" />
                            <small class="form-text text-muted">Это заголовок для текста страницы.</small>
                        </div>
                        <div class="form-group content">
                            <label>Контент (содержимое)</label>
                            <textarea id="content_area" class="form-control" name="page[content]"><xsl:value-of select="$page/content"/></textarea>
                            <small class="form-text text-muted">Это содержимое страницы.</small>
                        </div>
                    </div>
                </div>
                <!--
                <div class="parent_box_flex row">
                    <div class="col-auto position-relative">
                        <div id="table_generator">
                            <xsl:apply-templates select="$params" />
                        </div>
                    </div>
                    <div class="col">
                        <div id="li_generator">
                            <xsl:apply-templates select="$dop_field" />
                        </div>
                    </div>
                </div>
                -->
                <div class="box_table">
                    <button type="submit" class="btn btn-primary">Применить</button>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="field[@name = 'field']">
        <ul>
            <xsl:for-each select="children/child">
                <li>
                    <div class="display_box">
                        <input name="dop_field[]" class="input_generate" type="text" value="{.}" />
                        <div class="buffer"><xsl:value-of select="."/></div>
                    </div>
                </li> 
            </xsl:for-each>
                <li>
                    <div class="display_box">
                        <input name="dop_field[]" class="input_generate" type="text" value="" />
                        <div class="buffer"></div>
                    </div>
                </li> 
        </ul>
    </xsl:template>
    <xsl:template match="field[@name = 'params']">
        <div class="box_for_table">
            <table class="table table-bordered" id_table="{position()}">
                <xsl:apply-templates select="children">
                    <xsl:with-param name="params" select = "position()" />
                </xsl:apply-templates>
            </table>
            <div class="table-bordered table_add">+</div>
        </div>
    </xsl:template>
    <xsl:template match="children[1]">
        <xsl:param name="params" />
        <tr id_row="{position()}">
            <xsl:apply-templates select="child">
                <xsl:with-param name="params" select = "$params" />
                <xsl:with-param name="row" select = "position()" />
            </xsl:apply-templates>
            <td class="table_minus">
               <div>-</div> 
            </td>
            <td class="table_plus">
               <div>+</div>  
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="children[position() != 1]">
        <xsl:param name="params" />
        <tr id_row="{position()}">
            <xsl:apply-templates select="child">
                <xsl:with-param name="params" select = "$params" />
                <xsl:with-param name="row" select = "position()" />
            </xsl:apply-templates>
            <td class="no_row" colspan="2"></td>
        </tr>
    </xsl:template>
    <xsl:template match="child">
        <xsl:param name="params" />
        <xsl:param name="row" />
        <td class="child">
            <div class="display_box">
                <input name="params[{$params}][{$row}][]" class="input_generate" type="text" value="{.}" />
                <div class="buffer"><xsl:value-of select="."/></div>
            </div>
        </td>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>