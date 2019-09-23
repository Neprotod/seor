<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" />
    <xsl:template name="meta">
        <input type="hidden" name="page[id]" value="{$page/@id}" />
        <input type="hidden" name="page[id_admin_user]" value="{$page/@id_admin_user}" />
        <xsl:if test="normalize-space($page/@date)">
            <div class="date_box row">
                <span class="string col-1">Создан:</span>
                <span class="date col-auto"><xsl:value-of select="$page/@date"/></span>
            </div>
        </xsl:if>
        <xsl:if test="normalize-space($page/@modified)">
            <div class="date_box row">
                <span class="string col-1">Изменен:</span>
                <span class="date col-auto"><xsl:value-of select="$page/@modified"/></span>
            </div>
        </xsl:if>
        <div class="form-group max-width">
            <label>Статус</label>
            <div class="col status_control">
                <input type="range" class="custom-range" name="page[status]" min="0" max="1" step="1" value="{$page/@status}" data-help="Влево - выключена" />
            </div>
            <small class="form-text text-muted">Включена ли категория.</small>
        </div>
        <div class="form-group max-width">
            <label>Тип данных</label>
            <select name="page[content_type]" class="custom-select" data-help="Тип данных указывается обязательно." required="required">
                <option value="">Выберите тип данных</option>
                <xsl:apply-templates select="$page/content_type">
                    <xsl:with-param name="content_type" select = "$page/@content_type" />
                </xsl:apply-templates>
            </select>
            <small class="form-text text-muted">Тип данных указывается обязательно.</small>
        </div>
        <div class="form-group max-width">
            <label>Категория:</label>
            <span id="category_name" class="text-secondary"><xsl:value-of select="$page/category/title" /></span>
            <input id="category_input" name="page[id_category]" type="hidden" class="form-control" value="{$page/category/@id}" />
            <xsl:call-template name="modal_button">
                <xsl:with-param name="id" select = "'modal_category'" />
                <xsl:with-param name="title" select = "'Выбрать'" />
            </xsl:call-template>
            <small class="form-text text-muted">Категория влияет на конечный URL, смена категории изменит URL. Предыдущий URL будет так же рабочим.</small>
        </div>
        <div class="form-group">
            <label>Url</label>
            <div class="d-flex align-items-center">
                <input type="hidden" name="page[id_url]" value="{$page/@id_url}" />
                <span class="padding-right">
                    <span id="url_parent"><xsl:value-of select="$page/category/@url" /></span>
                    <span>/</span>
                    </span>
                <span class="">
                    <xsl:element name="input">
                        <xsl:attribute name="name">page[url_name]</xsl:attribute>
                        <xsl:attribute name="type">text</xsl:attribute>
                        <xsl:attribute name="class">form-control url_input</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="$page/@url_name" /></xsl:attribute>
                        <xsl:if test="@static = 1">
                            <xsl:attribute name="disabled">disabled</xsl:attribute>
                        </xsl:if>
                    </xsl:element>
                </span>
            </div>
            <small class="form-text text-muted">Это URL страницы.</small>
        </div>
        <div class="form-group max-width">
            <label>Заголовок</label>
            <input name="page[title]" type="text" class="form-control" placeholder="Заголовок" value="{$page/title}" required="required" />
            <small class="form-text text-muted">Это title страницы.</small>
        </div>
        <div class="form-group max-width">
            <label>Мета заголовок</label>
            <input name="page[meta_title]" type="text" class="form-control" placeholder="Мета заголовок" value="{$page/meta_title}" />
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
            <textarea name="page[description]" class="form-control" rows="3"><xsl:value-of select="$page/description" /></textarea>
            <small class="form-text text-muted">Это description страницы.</small>
        </div>
        <button type="submit" class="btn btn-primary">Применить</button>
    </xsl:template>
    
    <!--
        Шаблон модального окна
    -->
    <xsl:template match="content_type">
        <xsl:param name="content_type"/>
        
        <xsl:element name="option">
            <xsl:attribute name="value"><xsl:value-of select="@id"/></xsl:attribute>
            <xsl:if test="$content_type = @id">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="@title"/>
            (<xsl:value-of select="@name"/>)
        </xsl:element>
    </xsl:template>
    
    <xsl:template name="modal">
        <xsl:param name="id" select="'modal_file'" />
        <xsl:param name="class" select="''" />
        <xsl:param name="title" select="'Файловая система'" />
        
        <!-- Modal -->
        <div class="modal fade {$class}" id="{$id}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><xsl:value-of select="$title" /></h5>
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
    <xsl:template name="modal_error">
        <div class="modal fade" id="modal_error" tabindex="-100" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Сообщение</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&#215;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
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
    
    <xsl:template name="modal_name">
        <xsl:param name="id" select="'modal_name'" />
        <xsl:param name="title" select="'Переименование'" />
        <xsl:param name="input" select="'Новое имя:'" />
        <xsl:param name="button" select="'Переименовать'" />
        
        <div class="modal fade" id="{$id}" tabindex="-100" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><xsl:value-of select="$title" /></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&#215;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label"><xsl:value-of select="$input" /></label>
                            <input type="text" class="form-control" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary to_select"><xsl:value-of select="$button" /></button>
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>