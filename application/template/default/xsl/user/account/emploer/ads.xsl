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

    <xsl:param name="price" select="//account/price" />
    <xsl:param name="count" select="//account/count" />
    
    <!-- Создаем переменные для подсчета -->
    <xsl:param name="count_draft">
        <xsl:choose>
            <xsl:when test="$count/draft"><xsl:value-of select="$count/draft" /></xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    <xsl:param name="count_moder">
        <xsl:choose>
            <xsl:when test="$count/moder"><xsl:value-of select="$count/moder" /></xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    <xsl:param name="count_disabled">
        <xsl:choose>
            <xsl:when test="$count/disabled"><xsl:value-of select="$count/disabled" /></xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    <xsl:param name="count_active">
        <xsl:choose>
            <xsl:when test="$count/active"><xsl:value-of select="$count/active" /></xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    <xsl:param name="count_all">
        <xsl:choose>
            <xsl:when test="$count/all"><xsl:value-of select="$count/all" /></xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    
    <xsl:decimal-format name="currency" decimal-separator="," grouping-separator=" " />
    
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="account/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="ads" class="container-fluid big_container white">
            <div class="container d-flex">
                <div class="flex-grow-1">
                    <div id="container_ads">
                        <h1 class="h4">Мои вакансии</h1>
                        <div id="ads_menu" class="d-flex justify-content-end ads_line pt_sans">
                            <xsl:element name="a">
                                <xsl:attribute name="href"><xsl:value-of select="concat(account/@url,'?mark=draft')" /></xsl:attribute>
                                <xsl:attribute name="class">margin-right-small <xsl:if test="account/@mark = 'draft'">active</xsl:if></xsl:attribute>
                                Черновик (<span id="draft_count"><xsl:value-of select="$count_draft" /></span>)
                            </xsl:element>
                            <xsl:element name="a">
                                <xsl:attribute name="href"><xsl:value-of select="concat(account/@url,'?mark=moder')" /></xsl:attribute>
                                <xsl:attribute name="class">margin-right-small <xsl:if test="account/@mark = 'moder'">active</xsl:if></xsl:attribute>
                                На модерации (<span id="moder_count"><xsl:value-of select="$count_moder" /></span>)
                            </xsl:element>
                            <xsl:element name="a">
                                <xsl:attribute name="href"><xsl:value-of select="concat(account/@url,'?mark=disable')" /></xsl:attribute>
                                <xsl:attribute name="class">margin-right-small <xsl:if test="account/@mark = 'disable'">active</xsl:if></xsl:attribute>
                                Неактивные (<span id="disabled_count"><xsl:value-of select="$count_disabled" /></span>)
                            </xsl:element>
                            <xsl:element name="a">
                                <xsl:attribute name="href"><xsl:value-of select="concat(account/@url,'?mark=active')" /></xsl:attribute>
                                <xsl:attribute name="class">margin-right-small <xsl:if test="account/@mark = 'active'">active</xsl:if></xsl:attribute>
                                Активные (<span id="active_count"><xsl:value-of select="$count_active" /></span>)
                            </xsl:element>
                            <xsl:element name="a">
                                <xsl:attribute name="href"><xsl:value-of select="concat(account/@url,'?mark=all')" /></xsl:attribute>
                                <xsl:attribute name="class"><xsl:if test="account/@mark = 'all'">active</xsl:if></xsl:attribute>
                                Все (<span id="all_count"><xsl:value-of select="$count_all" /></span>)
                            </xsl:element>
                        </div>
                        <xsl:choose>
                            <xsl:when test="count(//account/ads) != 0">
                                <xsl:apply-templates select="//account/ads" />
                            </xsl:when>
                            <xsl:otherwise>
                                <div id="no_ads">
                                    <div class="h4">Нет вакансий</div>
                                </div>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                </div>
                <div>
                    <div id="count_box" class="">
                        <div id="pay_count_box">
                            <div id="count_day">
                                <div class="day"> 
                                    <xsl:value-of select="account/user/@ads" />
                                </div>
                                <div class="string"> 
                                    вакансий
                                </div>
                            </div>
                            <div id="count_pay">
                                <div class="pay"> 
                                    <span class="seor_coin">
                                        <xsl:value-of select="floor(account/user/@seor)" />
                                    </span>
                                    <span class="coin_sing"></span>
                                </div>
                            </div>
                            <a class="link" href="/account/pay">
                                Пополнить счет
                            </a>
                        </div>
                        <div>
                            <a href="{account/@url}?ad=new" class="btn btn-success btn_orange user_button btn_ads">Создать вакансию</a>
                        </div>
                    </div> 
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="ads">
        <xsl:variable name="length" select="'250'" />
        <xsl:variable name="doted">
            <xsl:if test="string-length(description) &gt; $length">...</xsl:if>
        </xsl:variable>
        
        <xsl:variable name="currency" select="@id_currency" />
        
        <xsl:variable name="moder">
            <xsl:choose>
                <xsl:when test="@status = 3">
                    В черновике
                </xsl:when>
                <xsl:when test="@approved = 0">
                    В процессе модерации
                </xsl:when>
                <xsl:when test="@approved = 2">
                    Не прошло модерацию
                </xsl:when>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="moder_class">
            <xsl:choose>
                <xsl:when test="@status = 3"> text-secondary</xsl:when>
                <xsl:when test="@approved = 0"> text-success</xsl:when>
                <xsl:when test="@approved = 2"> text-danger</xsl:when>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="active">
            <xsl:choose>
                <xsl:when test="@status = 1">Деактивировать</xsl:when>
                <xsl:otherwise>Активировать</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="class">
            <xsl:choose>
                <xsl:when test="@status = 1">disable</xsl:when>
                <xsl:otherwise>active</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="ads" data-id="{@id}">
            <div class="d-flex ads_inform">
                <div class="flex-grow-1">
                    <div class="h5"><xsl:value-of select="@title" /></div>
                    <div><xsl:value-of select="concat(normalize-space(substring(description,0,$length)),$doted)" /></div>
                </div>
                <div class="ads_salary">
                    <div class="h5">
                        <span>
                            <xsl:value-of select="format-number(@salary,'## ###','currency')" />
                        </span>
                        <span>
                            <xsl:value-of select="//account/currency_name[@id = $currency]" />
                        </span>
                    </div>
                    <xsl:if test="@status = 3">
                        <div class="btn btn_green public_btn" href="/account/pay">Опубликовать</div>
                    </xsl:if>
                </div>
            </div>
            <div class="time pt_sans position-relative">
                <span class="article">Артикул №<xsl:value-of select="@id" /></span>
                <span class="clock"><xsl:value-of select="translate(@time,'-','/')" /></span>
            </div>
            <div class="d-flex justify-content-end align-items-center ads_bottom pt_sans no_select">
                <xsl:if test="normalize-space($moder_class) != ''">
                    <div class="moder{$moder_class}"><xsl:value-of select="$moder" /></div>
                </xsl:if>
                <xsl:if test="@status = 1">
                    <div class="to_app text_button right-border" data-toggle="tooltip" data-placement="top" title="С счета будет снято {floor($price[@name = 'ads_up'])} seor coin">Поднять</div>
                </xsl:if>
                <div data-approved="{@approved}" class="text_button active_deactive {$class}"><xsl:value-of select="$active" /></div>
                <a class="text_button left-border" href="{account/@url}?ad={@id}">Редактировать</a>
                <div class="text_button left-border drop_add">Удалить</div>
            </div>
        </div>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>