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
    <xsl:param name="seesion_id" select="categories/@session_id" />
    <xsl:param name="link" select="categories/@action" />
    <xsl:param name="parent" select="categories/category/parent_fields/field[@name = 'image_path']" />
    <xsl:param name="field" select="categories/category/fields/field[@name = 'image_path']" />
    <xsl:template match="/">
        <div id="category" class="vis">
            <div class="box">
                <a class="btn btn-outline-primary" href="{categories/@end_url}">К списку</a>
            </div>
            
            <xsl:call-template name="modal" />
            
            <form method="post" action="{categories/@action}" data-toggle="validator" role="form">
                <input type="hidden" name="session_id" value="{$seesion_id}" />
                <xsl:apply-templates select="categories/category"/>
            </form>
            
        </div>
    </xsl:template>
    
    <xsl:template match="category">
    
        <xsl:variable name="parent_fix">
            <xsl:if test="$parent"><xsl:value-of select="concat($parent/@var,'/')"/></xsl:if>
        </xsl:variable>
        <xsl:variable name="fields_id">
            <xsl:choose>
                <xsl:when test="$field"><xsl:value-of select="$field/@id"/></xsl:when>
                <xsl:otherwise>new</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <input type="hidden" name="category[id]" value="{@id}" />
        <div class="date_box row">
            <span class="string col-1">Создан:</span>
            <span class="date col-auto"><xsl:value-of select="@date"/></span>
        </div>
        <xsl:if test="normalize-space(@modified)">
            <div class="date_box row">
                <span class="string col-1">Изменен:</span>
                <span class="date col-auto"><xsl:value-of select="@modified"/></span>
            </div>
        </xsl:if>
        
        <div class="form-group max-width">
            <label>Статус</label>
            <div class="col status_control">
                <input type="range" class="custom-range" name="category[status]" min="0" max="1" step="1" value="{@status}" data-help="Влево - категория выключена" />
            </div>
            <small class="form-text text-muted">Включена ли категория.</small>
        </div>
        <div class="form-group max-width">
            <label>Заголовок</label>
            <input name="category[title]" type="text" class="form-control" placeholder="Заголовок" value="{title}" />
            <small class="form-text text-muted">Это title страницы.</small>
        </div>
        <div class="form-group max-width">
            <label>Мета заголовок</label>
            <input name="category[meta_title]" type="text" class="form-control" placeholder="Мета заголовок" value="{meta_title}" />
            <small class="form-text text-muted">Это meta_title страницы.</small>
        </div>
        <div class="form-group">
            <label>Url</label>
            <div class="d-flex align-items-center">
                <input type="hidden" name="category[id_url]" value="{@id_url}" />
                <span class="padding-right"><xsl:value-of select="@url_parent" />/</span>
                <span class="">
                    <xsl:element name="input">
                        <xsl:attribute name="name">category[url_name]</xsl:attribute>
                        <xsl:attribute name="type">text</xsl:attribute>
                        <xsl:attribute name="class">form-control url_input</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="@url_name" /></xsl:attribute>
                        <xsl:if test="@static = 1">
                            <xsl:attribute name="disabled">disabled</xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </span>
                <span class="padding-left">
                    <xsl:element name="input">
                        <xsl:attribute name="name">category[static]</xsl:attribute>
                        <xsl:attribute name="type">checkbox</xsl:attribute>
                        <xsl:attribute name="class">form-control static_input</xsl:attribute>
                        <xsl:attribute name="value">1</xsl:attribute>
                        <xsl:if test="@static = 1">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </span>
                <span class="padding-left">
                    <span class="string">Статическая категория?</span>
                    <small class="form-text text-muted">Если указать, что она статическая, это будет равносильно папке.</small>
                </span>
            </div>
            <small class="form-text text-muted">Это URL страницы. У статических категорий нет URL.</small>
        </div>
        <div class="form-group">
            <label>Папка категории</label>
            <div class="d-flex align-items-center">
                <xsl:if test="$parent_fix != ''">
                    <div class="padding-right">
                       <xsl:value-of select="$parent_fix"/>  
                       <input name="fields[parent]" type="hidden" value="{$parent/@var}" /> 
                    </div>
                </xsl:if>
                <div>
                   <input name="fields[{$fields_id}][var]" type="text" id="directory_input" class="form-control" value="{substring-after($field/@var,$parent_fix)}" />  
                </div>
                <div>
                     <xsl:call-template name="modal_button" />
                </div>
               
            </div>
            <small class="form-text text-muted">Это meta_title страницы.</small>
        </div>
        <div class="form-group">
            <label>Индексация:</label>
            <div class="radio_controll row" data-help="Будет ли робот поисковика брать данные со страницы.">
                <span class="col-2">Индексировать:</span>
                <div class="col-auto">
                    <div class="input_controll" to_value="index">
                        <div class="input_center"></div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input_controll no" to_value="noindex">
                        <div class="input_center"></div>
                    </div>
                </div>
                <xsl:choose>
                    <xsl:when test="//robots/index = 'index'">
                        <input checked="checked" type="radio" class="custom-input" name="robots[index]" value="index" />
                    </xsl:when>
                    <xsl:otherwise>
                        <input type="radio" class="custom-input" name="robots[index]" value="index" />
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:choose>
                    <xsl:when test="//robots/index = 'noindex'">
                        <input checked="checked" type="radio" class="custom-input" name="robots[index]" value="noindex" />
                    </xsl:when>
                    <xsl:otherwise>
                        <input type="radio" class="custom-input" name="robots[index]" value="noindex" />
                    </xsl:otherwise>
                </xsl:choose>
            </div>
            <div class="radio_controll row" data-help="Будет ли робот поисковика переходить по ссылкам которые найдет на странице.">
                <span class="col-2">Переходить по ссылке:</span>
                <div class="col-auto">
                    <div class="input_controll" to_value="follow">
                        <div class="input_center"></div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input_controll no" to_value="nofollow">
                        <div class="input_center"></div>
                    </div>
                </div>
                <xsl:choose>
                    <xsl:when test="//robots/follow = 'follow'">
                        <input checked="checked" type="radio" class="custom-input" name="robots[follow]" value="follow" />
                    </xsl:when>
                    <xsl:otherwise>
                        <input type="radio" class="custom-input" name="robots[follow]" value="follow" />
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:choose>
                    <xsl:when test="//robots/follow = 'nofollow'">
                        <input checked="checked" type="radio" class="custom-input" name="robots[follow]" value="nofollow" />
                    </xsl:when>
                    <xsl:otherwise>
                        <input type="radio" class="custom-input" name="robots[follow]" value="nofollow" />
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </div>
        <div class="form-group max-width">
            <label>Мета описание</label>
            <textarea name="category[description]" class="form-control" rows="3"></textarea>
            <small class="form-text text-muted">Это description страницы.</small>
        </div>
        <button type="submit" class="btn btn-primary">Применить</button>
    </xsl:template>
    
    <!--
        Шаблон модального окна
    -->
    <xsl:template name="modal">
        <xsl:param name="id" select="'modal_file'" />
        <!-- Modal -->
        <div class="modal fade" id="modal_file" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Файловая система</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&#215;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                        <button type="button" class="btn btn-primary to_select">Выбрать</button>
                    </div>
                    <div class="error"></div>
                </div>
            </div>
        </div>
    </xsl:template>
    <!--
        Шаблон кнопки вызова модального окна
    -->
    <xsl:template name="modal_button">
        <xsl:param name="id" select="'modal_file'" />
        <xsl:param name="title" select="'Выбрать папку'" />
        <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#{$id}">
            <xsl:value-of select="$title" disable-output-escaping="yes" /> 
        </button>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>