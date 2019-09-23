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
    <xsl:key name="currency_name" match="//ad/currency_name" use="@id" />
    <xsl:key name="language" match="//ad/language" use="@id" />
    <xsl:key name="country" match="//ad/country" use="@id" />
    <xsl:key name="specialization" match="//ad/specialization" use="@id" />
    <xsl:key name="all_ads_language" match="//ad/all_ads_language" use="@id_language" />
    <xsl:key name="all_ads_country" match="//ad/all_ads_country" use="@id_country" />
    
    <xsl:param name="get" select="//ad/get" />
    <xsl:param name="return" select="//ad/@return" />
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container">
                <div id="search_box" class="d-flex padding-bottom pt_sans no_select">
                    <div class="w-100 margin-right-small">
                        <input id="search_input" value="{$get/search}" placeholder="Поиск" autocomplete="off" data-name="search" />
                    </div>
                    <div>
                        <div id="input_specialization" class="position-relative fake_input d-flex down_list align-items-center empty" data-name="specialization">
                            <div class="filter_count">
                                <span class="string">0</span>
                            </div> 
                            <div class="input_name">Разделы и специализации</div>
                            <div class="spread_box d-none">
                                <div class="spread_info d-none">
                                    <div data-id="" data-active="0">Все</div>
                                    <xsl:apply-templates select="/ad/specialization" />
                                </div>
                                <div class="content_info"></div>
                                <button class="btn btn_green" href="/account/pay">ok</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="filter_box" class="d-flex margin-top-big no_select">
                    <div id="filter_inform" class="d-flex w-100 align-items-center" data-name="specialization">
                        <div id="input_country" class="position-relative fake_input down_list d-flex align-items-center  margin-right-small empty" data-name="country">
                            <div class="title">Страна</div>
                            <div class="filter_count">
                                <span class="string">0</span>
                            </div> 
                            <div class="input_name">
                                Все
                            </div>
                            <div class="spread_box d-none">
                                <div class="spread_info d-none">
                                    <div data-id="" data-active="0">Все</div>
                                    <xsl:apply-templates select="/ad/country" />
                                </div>
                                <div class="content_info"></div>
                                <button class="btn btn_green" href="/account/pay">ok</button>
                            </div>
                        </div>
                        <div id="input_language" class="position-relative fake_input down_list d-flex align-items-center  margin-right-small empty" data-name="language">
                            <div class="title">Знание языков</div>
                            <div class="filter_count">
                                <span class="string">0</span>
                            </div> 
                            <div class="input_name">
                                Все
                            </div>
                            <div class="spread_box d-none">
                                <div class="spread_info d-none">
                                    <div data-id="" data-active="0">Все</div>
                                    <xsl:apply-templates select="/ad/language" />
                                </div>
                                <div class="content_info"></div>
                                <button class="btn btn_green" href="/account/pay">ok</button>
                            </div>
                        </div>
                        <div class="position-relative filter_input d-flex align-items-center margin-right-small" >
                            <div class="title">Зарплата</div>
                            <input id="price_from" data-name="price_from" data-type="price" value="{$get/price_from}" placeholder="от" />
                            <div class="uno">-</div>
                            <input id="price_to" data-name="price_to" data-type="price" value="{$get/price_to}" class="margin-right-small" placeholder="до" />
                            <div class="down_list">
                                <select id="currency" data-name="currency">
                                    <xsl:apply-templates select="/ad/currency_name" />
                                </select>
                            </div>
                        </div>
                        <button type="button" id="filter_clear" class="btn btn-outline-secondary">Сбросить</button>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button id="button_search" class="btn btn_green" href="/account/pay">Найти</button>
                    </div>
                </div>
            </div>
        </section>
        <section id="ads" class="container-fluid big_container white view">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="container">
                <h1 class="h4">Вакансии</h1>
                <div id="container_ads">
                    <xsl:value-of select="//ad/content" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="specialization">
        <xsl:variable name="active">
            <xsl:choose>
                <xsl:when test="$get/specialization = @id">1</xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div data-id="{@id}" data-active="{$active}"><xsl:value-of select="." /></div>
    </xsl:template>
    
    <xsl:template match="language">
        <xsl:if test="key('all_ads_language',@id)">
            <xsl:variable name="active">
                <xsl:choose>
                    <xsl:when test="$get/language = @id">1</xsl:when>
                    <xsl:otherwise>0</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <div data-id="{@id}" data-active="{$active}"><xsl:value-of select="." /></div>
        </xsl:if>
    </xsl:template>
    <xsl:template match="country">
         <xsl:if test="key('all_ads_country',@id)">
            <xsl:variable name="active">
                <xsl:choose>
                    <xsl:when test="$get/country = @id">1</xsl:when>
                    <xsl:otherwise>0</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <div data-id="{@id}" data-active="{$active}"><xsl:value-of select="." /></div>
        </xsl:if>
    </xsl:template>
    <xsl:template match="currency_name">
        <xsl:choose>
            <xsl:when test="$get/currency = @id">
                <option selected="selected" value="{@id}"><xsl:value-of select="." /></option>
            </xsl:when>
            <xsl:otherwise>
                <option value="{@id}"><xsl:value-of select="." /></option>
            </xsl:otherwise>
        </xsl:choose>
        
    </xsl:template>
    
    <xsl:template match="*">
       
    </xsl:template>
</xsl:stylesheet>