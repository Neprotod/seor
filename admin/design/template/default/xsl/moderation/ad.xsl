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

    <xsl:param name="user" select="//ad/user" />
    <xsl:param name="ad" select="//ad/ads" />
    
    
    <xsl:template match="/">
        <section id="ad" class="container-fluid big_container white p_14">
            <form class="container" action="{ad/@action}" method="post" autocomplete="off">
                <input type="hidden" name="session_id" value="{//ad/@session_id}" />
                <div class="ads_line">
                    <span class="title">Модерировать Вакансию</span><span class="id">№<xsl:value-of select="$ad/@id" /></span>
                </div>
                <xsl:if test="normalize-space(ad/note) != ''">
                    <div id="ads_error" class="alert alert-warning">
                        <xsl:value-of select="ad/note" disable-output-escaping="yes" />
                    </div>
                </xsl:if>
                <div id="ad_fields" class="row no_padding margin-top-small">
                    <div class="col primary">
                        <div class="input_label">
                            <span class="string pt_sans">Заголовок</span>
                        </div>
                        <div class="">
                            <div class="form-group control">
                                <input name="title" type="text" class="form-control p_14" value="{ad/ads/@title}" active-role="name" required="required" />
                            </div>
                        </div>
                        <div class="input_label">
                            <span class="string pt_sans">Описание</span>
                        </div>
                        <div class="">
                            <div class="form-group control">
                                <textarea name="description" class="form-control p_14" rows="6" required="required" active-role="description"><xsl:value-of select="ad/ads/description" /></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_label">
                            <span class="string pt_sans">Пометки модератора</span>
                        </div>
                        <div class="form-group control">
                            <textarea name="note" class="form-control p_14" rows="5" active-role="note"><xsl:value-of select="ad/note" /></textarea>
                        </div>
                        <xsl:if test="normalize-space($ad/@time) != ''">
                            <div>У вакансии есть время</div>
                            <div class="form-check">
                                <label class="form-check-label d-flex align-items-center">
                                    <input name="time" class="form-check-input" type="checkbox" value="1" />
                                    <span>Назначить текущее</span>
                                </label>
                            </div>
                        </xsl:if>
                    </div>
                </div>
                <div id="submit_button" class="d-flex justify-content-end">
                    <div>
                        <button name="submit" value="2" class="btn btn-danger" type="submit">Отклонить</button>
                    </div>
                    <div>
                        <button name="submit" value="1" class="btn btn-success" type="submit">Подтвердить</button>
                    </div>
                </div>
            </form>
        </section>
    </xsl:template>
    
    <xsl:template match="currency_name">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>
    <xsl:template match="language">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>
    <xsl:template match="country">
        <xsl:choose>
            <xsl:when test="$ad/@id_country = @id">
                <option value="{@id}" selected="selected"><xsl:value-of select="." /></option>
            </xsl:when>
            <xsl:when test="normalize-space($ad/@id_country) = '' and $user/@id_country = @id">
                <option value="{@id}" selected="selected"><xsl:value-of select="." /></option>
            </xsl:when>
            <xsl:otherwise>
                <option value="{@id}"><xsl:value-of select="." /></option>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="specialization">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>
    
    <xsl:template match="ads_specialization">
        <xsl:variable name="id" select="@id_specialization" />
        <div class="selected_input">
            <span class="string"><xsl:value-of select="//ad/specialization[@id = $id]" /></span>
            <span type="button" class="close">×</span>
            <input name="specialization[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    
    <xsl:template match="ads_language">
        <xsl:variable name="id" select="@id_language" />
        <div class="selected_input">
            <span class="string"><xsl:value-of select="//ad/language[@id = $id]" /></span>
            <span type="button" class="close">×</span>
            <input name="language[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>