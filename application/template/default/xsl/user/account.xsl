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
    <xsl:key name="country" match="//account/country" use="@id" />
    <xsl:param name="shield">
        <xsl:if test="account/user/@complete = 2">active</xsl:if>
    </xsl:param>
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="account/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="account" class="container-fluid big_container white view">
            <div class="container">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <div class="box_content">
                            <div id="main_information_box" class="d-flex">
                                <div id="main_information" class="flex-grow-1 d-flex">
                                    <!-- Поля формы -->
                                    <div id="main_form" class="box_content margin-right flex-grow-1">
                                        <div class="d-flex margin-bottom">
                                            <div class="padding-right">
                                                <div id="company_logo" class="">
                                                    <img class="logo_image" src="{account/user/@logo}" />
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="position-relative">
                                                    <h1 id="user_name" class="h5">
                                                        <xsl:value-of select="account/user/@name" />
                                                        <div class="sing shield_sing {$shield}"></div>
                                                    </h1>
                                                </div>
                                                <div class="position-relative margin-bottom">
                                                    <div class="string break">
                                                        <xsl:value-of select="account/fields/activity/field" />
                                                    </div>
                                                </div>
                                                <xsl:if test="account/fields/site/field">
                                                    <div class="position-relative margin-bottom">
                                                        <div class="string break">
                                                            <xsl:value-of select="account/fields/site/field" />
                                                        </div>
                                                    </div>
                                                </xsl:if>
                                                <div class="position-relative view_green">
                                                    <span class="string">
                                                        <xsl:apply-templates select="account/country" /> 
                                                    </span>   
                                                    <span class="string">
                                                        <xsl:value-of select="account/fields/city/field" />
                                                    </span>  
                                                    <span class="string">
                                                        /
                                                    </span>
                                                    <span class="string">
                                                        <xsl:value-of select="account/user/@all_year" />
                                                        лет
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="position-relative">
                                                <span class="input_headline position-static margin-bottom pt_sans">О компании:</span>
                                                <div class="view_green">
                                                    <xsl:value-of select="account/fields/description/field" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="information" class="">
                                    <div class="box_content">
                                        <div id="contact_inputs_box" class="position-relative">
                                            <span class="input_headline position-static margin-bottom pt_sans">Контакты</span>
                                            <div class="d-flex input_skype align-items-center no_input">
                                                <div class="mail_sing sing margin-right-small"></div>
                                                <div class="input_box align-items-center">
                                                    <xsl:value-of select="account/user/@email" />
                                                </div>
                                            </div>
                                            <xsl:if test="account/fields/skype">
                                                <div class="align-items-center d-flex no_input">
                                                    <div class="skype_sing sing margin-right-small"></div>
                                                    <div class="input_box">
                                                        <xsl:value-of select="account/fields/skype/field" />
                                                    </div>
                                                </div>
                                            </xsl:if>
                                            <xsl:apply-templates select="account/phone" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="count_box" class="">
                        <div id="pay_count_box">
                            <div id="count_day">
                                <div class="day"> 
                                    <xsl:value-of select="account/user/@days" />
                                </div>
                                <div class="string"> 
                                    дней
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
                            <a href="{account/@site}{account/@action}/edit" class="btn btn-success btn_orange user_button">Редактировать</a>
                        </div>
                        <xsl:if test="count(account/valid) = 0">
                            <div>
                                <button id="verification" type="button" class="btn btn-outline-light pt_sans">
                                    <div>Подтверждение</div>
                                    <div>данных</div>
                                </button>
                            </div>
                        </xsl:if>
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="country">
        <xsl:if test="//account/user/@id_country = @id">
            <xsl:value-of select="." />
        </xsl:if>
    </xsl:template>
    <!-- Телефоны -->
    <xsl:template match="phone">
        <div class="position-relative d-flex align-items-center no_input">
            <div class="phone_sing sing margin-right-small"></div>
            <div class="input_box">
                <span>+<xsl:value-of select="key('country',@id_country_code)/@phone" /></span>
                <span><xsl:value-of select="@phone" /></span>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>