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
    
    <xsl:param name="ads_specialization" select="//ad/ads_specialization" />
    <xsl:param name="return" select="//ad/@return" />
    <xsl:param name="site" select="//ad/@site" />
    <xsl:param name="user" select="//ad/user" />
    <xsl:param name="get" select="//ad/get" />
    
    <xsl:decimal-format name="currency" decimal-separator="," grouping-separator=" " />
    
    <xsl:template match="/">
        <div id="fiter_inside" class="d-flex flex-wrap">
            <xsl:apply-templates select="$get/specialization" />
            <xsl:apply-templates select="$get/country" />
            <xsl:apply-templates select="$get/language" />
        </div>
        <xsl:choose>
            <xsl:when test="//ad/ads">
                <div class="table_box">
                    <xsl:apply-templates select="//ad/ads" />
                </div>
            </xsl:when>
            <xsl:otherwise>
                <div id="no_ads">
                    <span class="string">Вакансии не найдены</span>
                </div>
            </xsl:otherwise>
        </xsl:choose>
        
    </xsl:template>
    
    <xsl:template match="ads">
        <xsl:param name="shield">
            <xsl:if test="@complete = 2">active</xsl:if>
        </xsl:param>
        
        <xsl:variable name="length" select="'250'" />
        <xsl:variable name="doted">
            <xsl:if test="string-length(description) &gt; $length">...</xsl:if>
        </xsl:variable>
        <xsl:variable name="id" select="@id" />
        <xsl:variable name="self">
            <xsl:if test="@id_user = $user/@id">1</xsl:if>
        </xsl:variable>
        
        <xsl:variable name="padding_top">
            <xsl:if test="position() = 1">padding-top</xsl:if>
        </xsl:variable>
        <xsl:variable name="currency" select="@id_currency" />
        <xsl:variable name="query_return">
            <xsl:if test="$return != ''">?prev=<xsl:value-of select="$return" /></xsl:if>
        </xsl:variable>
        <div class="ads" data-id="{@id}">
            <div class="cell info_ads">
                <div class="cell_box">
                    <a class="h5 margin-bottom" href="{$site}/ads/ad/{@id}{$query_return}">
                        <xsl:value-of select="@title" />
                        <xsl:if test="$self = 1">
                            <span class="self_ads">(Ваша вакансия)</span>
                        </xsl:if>
                        <div class="sing shield_sing {$shield}"></div>
                    </a>
                    <div class="description pt_sans"><xsl:value-of select="concat(normalize-space(substring(description,0,$length)),$doted)" /></div>
                    <div class="ads_specialization">
                        <xsl:apply-templates select="$ads_specialization[@id_ads = $id]">
                            <xsl:with-param name="specialization_count" select="count($ads_specialization[@id_ads = $id])" />
                        </xsl:apply-templates>
                    </div>
                </div>
            </div>
            <div class="ads_salary cell">
                <div class="cell_box">
                    <div class="h5 pt_sans">
                        <span>
                            <xsl:value-of select="format-number(@salary,'## ###','currency')" />
                        </span>
                        <span class="currency_name">
                            <xsl:value-of select="key('currency_name',@id_currency)" />
                        </span>
                    </div>
                </div>
                <div class="dop_inform">
                    <xsl:if test="@pay = 1">
                        <div class="pay_ads">Куплено</div>
                    </xsl:if>
                    <xsl:if test="@expiration != ''">
                        <div class="expiration" data-time="{@expiration_time}">
                            <div class="expiration_ads">Открыты еще</div>
                            <div class="time"><xsl:value-of select="@expiration" /></div>
                        </div>
                    </xsl:if>
                </div>
            </div>
        </div>
    </xsl:template>
    
    
    <xsl:template match="get/specialization">
        <!--<xsl:value-of select="name()" />-->
        <div class="filter_item" data-type="{name()}" data-id="{.}">
            <span><xsl:value-of select="key('specialization',.)" /></span>
            <span class="close">×</span>
        </div>
    </xsl:template>
    <xsl:template match="get/country">
        <!--<xsl:value-of select="name()" />-->
        <div class="filter_item" data-type="{name()}" data-id="{.}">
            <span><xsl:value-of select="key('country',.)" /></span>
            <span class="close">×</span>
        </div>
    </xsl:template>
    <xsl:template match="get/language">
        <!--<xsl:value-of select="name()" />-->
        <div class="filter_item" data-type="{name()}" data-id="{.}">
            <span><xsl:value-of select="key('language',.)" /></span>
            <span class="close">×</span>
        </div>
    </xsl:template>
    
    <xsl:template match="ads_specialization">
        
        <xsl:param name="specialization_count" />
        
        <span class="lang"><xsl:value-of select="key('specialization',@id_specialization)" /></span>
        <xsl:if test="position() != $specialization_count">
           <span class="dot">.</span> 
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="*">
       
    </xsl:template>
</xsl:stylesheet>