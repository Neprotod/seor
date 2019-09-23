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
    
    <xsl:param name="price" select="//ad/price" />
    <xsl:param name="user" select="//ad/user" />
    <xsl:param name="ad" select="//ad/ads" />
    
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div id="sample" class="selected_input">
                <span class="string">English</span>
                <span class="close">×</span>
            </div>
            <div class="container d-flex align-items-center">
                <div>
                    <a href="{ad/@site}/account/ads">Назад</a>
                </div>
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="ad/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="announce" class="container-fluid big_container white p_14">
            <form class="container" action="{ad/@site}{ad/@action}" method="post" autocomplete="off">
                <div class="ads_line">
                    <xsl:choose>
                        <xsl:when test="$ad/@id">
                            <span class="title">Редактировать Вакансию</span>
                        </xsl:when>
                        <xsl:otherwise>
                            <span class="title">Создать Вакансию</span>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <xsl:if test="normalize-space(ad/note) != ''">
                    <div id="ads_error" class="alert alert-warning">
                        <xsl:value-of select="ad/note" disable-output-escaping="yes" />
                    </div>
                </xsl:if>
                <div id="ad_fields" class="row no_padding margin-top-small">
                    <div class="col primary">
                        <div class="row">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Заголовок</span><span class="star_required">*</span>
                            </div>
                            <div class="col">
                                <div class="form-group control">
                                    <input name="title" type="text" class="form-control p_14" value="{ad/ads/@title}" active-role="name" required="required" />
                                </div>
                            </div>
                        </div>
                        <div class="row margin-top-small">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Оклад</span><span class="star_required">*</span>
                            </div>
                            <div class="col d-flex">
                                <div class="form-group control margin-right-small">
                                    <input id="input_salary" name="salary" type="text" class="form-control p_14 salary" value="{ad/ads/@salary}" active-role="salary" required="required" />
                                </div>
                                <div class="form-group control">
                                    <select name="currency_name" id="select_currency" class="form-control menu-select custom-select p_14" active-role="id_currency">
                                        <xsl:apply-templates select="ad/currency_name" />
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--<div class="row margin-top-small">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Активное обявление</span>
                            </div>
                            <div class="col d-flex">
                                <div class="form-group control margin-right-small">
                                    <div class="row">
                                        <xsl:choose>
                                            <xsl:when test="//ad/ads/@status != 1">
                                                <div class="col-8">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input" type="radio" name="status" value="1" />
                                                            Активное
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input" type="radio" name="status" value="0" checked="checked" />
                                                            Неактивное
                                                        </label>
                                                    </div>
                                                </div>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <div class="col-8">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input" type="radio" name="status" value="1" checked="checked" />
                                                            Активное
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input" type="radio" name="status" value="0" />
                                                            Неактивное
                                                        </label>
                                                    </div>
                                                </div>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </div>
                                </div>
                            </div>
                        </div>-->
                        <div class="row margin-top-medium">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Разделы и специализации</span><span class="star_required">*</span>
                            </div>
                            <div class="col">
                                <div class="form-group control">
                                    <select id="select_specialization" class="form-control menu-select custom-select p_14 input-small" active-role="to_specialization">
                                        <option value=""></option>
                                        <xsl:apply-templates select="ad/specialization" />
                                    </select>
                                </div>
                                <div id="specialization_box" class="input-medium d-flex flex-wrap">
                                    <xsl:apply-templates select="ad/ads_specialization" />
                                </div>
                            </div>
                        </div>
                        <div class="row margin-top-medium">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Знание языков</span>
                            </div>
                            <div class="col">
                                <div class="form-group control">
                                        <select id="select_language" class="form-control menu-select custom-select p_14 input-small" active-role="language">
                                            <option value=""></option>
                                            <xsl:apply-templates select="ad/language" />
                                        </select>
                                    <div id="language_box" class="input-medium d-flex flex-wrap">
                                        <xsl:apply-templates select="ad/ads_language" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row margin-top">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Страна вакансии</span><span class="star_required">*</span>
                            </div>
                            <div class="col">
                                <div class="form-group control input-small">
                                    <select class="form-control menu-select custom-select p_14" name="id_country" active-role="id_country" required="required">
                                        <option value=""></option>
                                        <xsl:apply-templates select="ad/country" />
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row margin-top-medium">
                            <div class="col-4 d-flex justify-content-end input_label">
                                <span class="string pt_sans">Описание</span><span class="star_required">*</span>
                            </div>
                            <div class="col">
                                <div class="form-group control textarea-medium">
                                    <textarea name="description" class="form-control p_14" rows="3" required="required" active-role="description"><xsl:value-of select="ad/ads/description" /></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3 description_col"></div>
                </div>
                <div class="ads_line margin-top-big">
                    <xsl:if test="normalize-space($ad/@pay) != 1 and $user/@days != 0">
                        <xsl:variable name="cost" select="floor($price[@name = 'ads_create'])" />
                        <div class="pay_info">
                            C вашего счета
                            <xsl:choose>
                                <xsl:when test="$user/@ads != 0">
                                    снимет <span class="pay"><b>1</b></span> бесплатное объявление
                                </xsl:when>
                                <xsl:otherwise>
                                    снимает <span class="pay"><b><xsl:value-of select="$cost" /></b><span class="coin_sing"></span></span> за услугу
                                </xsl:otherwise>
                            </xsl:choose>
                        </div>
                    </xsl:if>
                </div>
                <div id="submit_button" class="d-flex justify-content-end">
                    
                    <xsl:if test="normalize-space($ad/@status) = '' or $ad/@status = 3">
                        <div class="margin-right">
                            <button name="submit" value="3" type="submit" class="btn btn-outline-secondary user_button">В черновик</button>
                        </div>
                    </xsl:if>
                    <xsl:if test="$user/@days != 0">
                        <div>
                            <button name="submit" value="1" class="btn btn_orange user_button" type="submit">Опубликовать</button>
                        </div>
                    </xsl:if>
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
            <span class="close">×</span>
            <input name="specialization[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    
    <xsl:template match="ads_language">
        <xsl:variable name="id" select="@id_language" />
        <div class="selected_input">
            <span class="string"><xsl:value-of select="//ad/language[@id = $id]" /></span>
            <span class="close">×</span>
            <input name="language[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>