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
    
    <xsl:key name="currency_name" match="//worker/currency_name" use="@id" />
    <xsl:key name="language" match="//worker/language" use="@id" />
    <xsl:key name="country" match="//worker/country" use="@id" />
    <xsl:key name="specialization" match="//worker/specialization" use="@id" />
    <xsl:key name="fields" match="/worker/fields/field" use="concat(@id_user,@name)" />
    
    <xsl:param name="language_count" select="count(//worker/user_language)" />
    <xsl:param name="specialization_count" select="count(//worker/user_specialization)" />
    
    <xsl:param name="price" select="//worker/price" />
    <xsl:param name="user" select="//worker/user" />
    <xsl:param name="worker" select="//worker/worker" />
    <xsl:param name="fields" select="//worker/fields" />
    <xsl:param name="status">
        <xsl:choose>
            <xsl:when test="$worker/@status = 0"> cancel</xsl:when>
            <xsl:when test="$worker/@status = 2"> cancel</xsl:when>
        </xsl:choose>
    </xsl:param>

    <xsl:param name="self">
        <xsl:if test="$worker/@id = $user/@id">self</xsl:if>
    </xsl:param>
    <xsl:param name="return">
        <xsl:if test="worker/@prev"><xsl:value-of select="worker/@prev" /></xsl:if>
    </xsl:param>
    
     <xsl:param name="shield">
        <xsl:if test="$worker/@complete = 2">active</xsl:if>
    </xsl:param>
    
    <xsl:decimal-format name="currency" decimal-separator="," grouping-separator=" " />
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div>
                    <a href="{worker/@site}/workers{$return}">Назад</a>
                </div>
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="worker/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="announce" class="container-fluid position-relative big_container{$status}">
            <xsl:if test="$worker/@status != 1">
                    <xsl:choose>
                        <xsl:when test="$worker/@status = 0">
                            <xsl:call-template name="cancel_message">
                                <xsl:with-param name="class" select="'warning'" />
                                <xsl:with-param name="message" select="'Пользователь удален'" />
                            </xsl:call-template>
                        </xsl:when>
                    </xsl:choose>
            </xsl:if>
            <div class="container d-flex">
                <div class="w-100">
                    <div class="info_box">
                        <h1 class="h4 {$self}">
                            <xsl:value-of select="$worker/@name" />
                            <xsl:if test="$self != ''">
                                <span class="dop_text">(Ваше резюме)</span>
                            </xsl:if>
                            <div class="sing shield_sing {$shield}"></div>
                        </h1>
                        <div class="spec">
                            <xsl:value-of select="key('fields',concat($worker/@id,'activity'))" />
                        </div>
                        <div class="birthday">
                           <span><xsl:value-of select="key('country',$worker/@id_country)" /></span>
                           <span><xsl:value-of select="key('fields',concat($worker/@id,'city'))" /></span>
                           <span>/</span>
                           <span><xsl:value-of select="$worker/@all_year" /> лет</span>
                        </div>
                        <div class="description">
                            <xsl:value-of select="key('fields',concat($worker/@id,'description'))" disable-output-escaping="yes" />
                        </div>
                        <div class="language info_ads">
                            <xsl:choose>
                                <xsl:when test="count(/worker/user_language) &gt; 0">
                                    <div class="title font-weight-bold margin-top-medium">Знание языков:</div>
                                    <div class="string">
                                        <xsl:apply-templates select="/worker/user_language" />
                                    </div>
                                </xsl:when>
                            </xsl:choose>
                        </div>
                        <div class="specialization info_ads">
                            <div class="title font-weight-bold margin-top-medium">Разделы и специализации:</div>
                            <div class="string">
                                <xsl:choose>
                                    <xsl:when test="count(/worker/user_specialization) &gt; 0">
                                        <xsl:apply-templates select="/worker/user_specialization" />
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <span class="lang">Без знания языка</span>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex_container">
                    <form id="contact_box" class="{$self}" method="post" action="{worker/@site}{worker/@action}">
                        <xsl:choose>
                            <xsl:when test="$self != ''">
                                <xsl:call-template name="pay" />
                            </xsl:when>
                            <xsl:when test="$worker/@pay = 1" >
                                <xsl:call-template name="pay" />
                            </xsl:when>
                            <xsl:when test="$user/@id">
                                <xsl:call-template name="user" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="guest" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </form>
                    <div id="count_view">
                            <span class="string">Просмотры:</span>
                            <span class="count_string"><xsl:value-of select="$worker/@count_view" /></span>
                        </div>
                    </div>
                </div>
        </section>
    </xsl:template>
    
    
    <!-- РАБОТА С КОНТАКТАМИ -->
    <xsl:template name="user">
        <xsl:param name="cost">
            <xsl:choose>
                <xsl:when test="$user/@days = 0"><xsl:value-of select="floor($price[@name = 'no_user_account'])" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="floor($price[@name = 'user'])" /></xsl:otherwise>
            </xsl:choose>
        </xsl:param>
        <div id="concatc_logo">
            <div class="img"></div>
        </div>
        <xsl:if test="$worker/@status = 1">
            <button class="btn btn_green open_contacts_btn" type="submit">
                <div>Просмотреть</div>
                <div>контакты</div>
            </button>
            <div class="pay_info margin-top">
                <div>C вашего счета</div>
                <div>
                    <xsl:choose>
                        <xsl:when test="$user/@days = 0">
                            снимает <span class="pay"><b><xsl:value-of select="$cost" /></b><span class="coin_sing"></span></span> за услугу
                        </xsl:when>
                        <xsl:otherwise>
                            снимает <span class="pay"><b><xsl:value-of select="$cost" /></b><span class="coin_sing"></span></span> за услугу
                        </xsl:otherwise>
                    </xsl:choose>
                    
                </div>
                <xsl:if test="$user/@seor &lt; $cost">
                    <div class="dop_info">
                        <div class="account_is_over">Недостаточно средств</div>  
                    </div>
                </xsl:if>
                <xsl:if test="$user/@days = 0 and $user/@clicks = 0">
                    <div class="dop_info">
                        <div class="account_is_over">Период обслуживания аккаунта окончен</div>  
                        <div class="margin-top-small">
                            <a href="account/pay">продлите аккаунт</a>, что бы покупать контакты всего <div>за <xsl:value-of select="floor($price[@name = 'ads'])" /> seor coin</div>
                        </div>  
                    </div>
                </xsl:if>
            </div>
        </xsl:if>
    </xsl:template>
    
    
    <xsl:template name="pay">
        <div id="concatc_logo">
            <div class="img">
                <img src="{$worker/@logo}"></img>
            </div>
        </div>
        <div id="company_description">
            <div class="company_name h5 margin-bottom-small">
                <xsl:value-of select="$worker/@name" />
            </div>
            <div class="specialization margin-bottom">
                <xsl:value-of select="key('fields',concat($worker/@id,'activity'))" />
            </div>
        </div>
        <div class="contact_fields">
            <div class="position-relative d-flex align-items-center no_input">
                <div class="mail_sing sing margin-right-small"></div>
                <div class="input_box">
                    <xsl:value-of select="$worker/@email" />
                </div>
            </div>
            <xsl:apply-templates select="key('fields',concat($worker/@id,'skype'))" />
            <xsl:apply-templates select="/worker/phone" />
        </div>
        <xsl:if test="$worker/@expiration != ''">
            <div class="expiration_time">
                <div class="string">Контакты будут видны еще</div>
                <div class="time"><xsl:value-of select="$worker/@expiration" /></div>
            </div>
        </xsl:if>
        <xsl:choose>
            <xsl:when test="$self != ''">
                <a class="btn btn_green open_contacts_btn full" href="/account/edit">
                    <div>Редактировать</div>
                </a>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template name="guest">
        <div id="concatc_logo">
            <div class="img"></div>
        </div>
        <div class="registr_box">
            <div class="string p_12">Что бы просмотреть контакты, вы должны</div>
            <div class="string margin-top-small">
                <a href="{//worker/@site}/registr">зарегистрироваться</a>
            </div>
            <div class="string">
                или <a href="{//worker/@site}/account">войти</a>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template name="cancel_message">
        <xsl:param name="class" select="'primary'" />
        <xsl:param name="message" />
        <div class="container">
            <div class="cancel_message alert alert-{$class} text-center">
                <xsl:value-of select="$message" />
            </div>
        </div>
    </xsl:template>
    
    
    
    <xsl:template match="field[@name = 'skype']">
        <div class="position-relative d-flex align-items-center no_input">
            <div class="skype_sing sing margin-right-small"></div>
            <div class="input_box">
                <xsl:value-of select="." />
            </div>
        </div>
    </xsl:template>
    <xsl:template match="phone">
        <div class="position-relative d-flex align-items-center no_input">
            <div class="phone_sing sing margin-right-small"></div>
            <div class="input_box">
                <span>+<xsl:value-of select="key('country',@id_country_code)/@phone" /></span>
                <span><xsl:value-of select="@phone" /></span>
            </div>
        </div>
    </xsl:template>
    <xsl:template match="user_language">
        <span class="lang"><xsl:value-of select="key('language',@id_language)" /></span>
        <xsl:if test="position() != $language_count">
           <span class="dot">.</span> 
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="user_specialization">
        <span class="lang"><xsl:value-of select="key('specialization',@id_specialization)" /></span>
        <xsl:if test="position() != $specialization_count">
           <span class="dot">.</span> 
        </xsl:if>
    </xsl:template>
    
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>