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
    <xsl:key name="language" match="//account/language" use="@id" />
    <xsl:key name="country" match="//account/country" use="@id" />
    <xsl:key name="specialization" match="//account/specialization" use="@id" />
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div id="sample" class="selected_input">
                <span class="string">English</span>
                <span class="close">×</span>
            </div>
            <div class="container d-flex align-items-center">
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="account/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="account" class="container-fluid big_container white">
            <div class="container">
                <form class="d-flex" action="{account/@site}{account/@action}" method="post" autocomplete="off">
                    <div class="flex-grow-1">
                        <div class="box_content">
                            <div class="d-flex flex-wrap">
                                <div id="main_information" class="">
                                    <!-- Поля формы -->
                                    <div id="main_form" class="box_content margin-right">
                                        <div id="name_logo_box" class="d-flex margin-bottom">
                                            <div class="padding-right">
                                                <div id="company_logo" class="edit" data-logo="{account/user/@no_logo}">
                                                    <div class="shadow"></div>
                                                    <img class="logo_image" src="{account/user/@logo}" />
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" fill="none" class="feather feather_plus">
                                                        <path style="line-height:normal;text-indent:0;text-align:start;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000;text-transform:none;block-progression:tb;isolation:auto;mix-blend-mode:normal" d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 24 13 L 24 24 L 13 24 L 13 26 L 24 26 L 24 37 L 26 37 L 26 26 L 37 26 L 37 24 L 26 24 L 26 13 L 24 13 z" />
                                                    </svg>
                                                    <input type="file" class="d-none" id="input_image" />
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ">
                                                <div class="form-group control position-relative">
                                                    <span class="input_headline">Имя фамилия<span class="star_required">*</span></span>
                                                    <xsl:choose>
                                                        <xsl:when test="account/user/@complete &gt; 0">
                                                            <input type="text" class="form-control" value="{account/user/@name}" disabled="disabled" />
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <div class="d-flex">
                                                                <input name="name[first]" type="text" class="form-control margin-right-small" value="{account/user/@first}" placeholder="Имя" active-role="name_first" required="required" />
                                                                <input name="name[last]" type="text" class="form-control" value="{account/user/@last}" placeholder="Фамилия" active-role="name_last" required="required" />
                                                            </div>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </div>
                                                <div class="form-group control position-relative">
                                                    <span class="input_headline">Специальность<span class="star_required">*</span></span>
                                                    <input name="fields[activity][0]" type="text" class="form-control" value="{account/fields/activity/field}" active-role="field_specialty" required="required" placeholder="Укажите свою профессию" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-grow-1 flex-wrap">
                                            <div>
                                                <div class="form-group control position-relative d-flex">
                                                    <span class="input_headline">Дата рождения<span class="star_required">*</span></span>
                                                    <xsl:choose>
                                                        <xsl:when test="account/user/@complete &gt; 0">
                                                            <input maxlength="2" disabled="disabled" type="text" class="form-control birthday birthday_day margin-right-small" value="{substring-after(substring-after(account/user/@birthday,'-'),'-')}" />
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <input maxlength="2" name="birthday[day]" type="text" class="form-control birthday birthday_day margin-right-small" value="{account/birthday/@day}" active-role="birthday_day" required="required" placeholder="dd" pattern="^[0-2][0-9]|[3][0-1]" />
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                    
                                                    <xsl:choose>
                                                        <xsl:when test="account/user/@complete &gt; 0">
                                                            <input maxlength="2" type="text" class="form-control birthday birthday_month margin-right-small" disabled="disabled" value="{substring-before(substring-after(account/user/@birthday,'-'),'-')}"/>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <input maxlength="2" name="birthday[month]" type="text" class="form-control birthday birthday_month margin-right-small" value="{account/birthday/@month}" active-role="birthday_month" required="required" placeholder="mm" pattern="^[0][0-9]|[1][0-2]" />
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                    
                                                    <xsl:choose>
                                                        <xsl:when test="account/user/@complete &gt; 0">
                                                            <input maxlength="4" type="text" class="form-control birthday birthday_year margin-right" value="{substring-before(account/user/@birthday,'-')}" disabled="disabled" />
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <input maxlength="4" name="birthday[year]" type="text" class="form-control birthday birthday_year margin-right" value="{account/birthday/@year}" active-role="birthday_year" required="required" placeholder="yyyy" pattern="^[1][089][0-9][0-9]|[2][0][0-9][0-9]" />
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                    
                                                    
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div id="city_fields" class="form-group control position-relative d-flex">
                                                    <span class="input_headline">Страна и город<span class="star_required">*</span></span>
                                                    <select id="country_select" class="form-control margin-right-small menu-select custom-select" name="id_country" active-role="id_country" required="required">
                                                        <option value="">Страна...</option>
                                                        <xsl:apply-templates select="account/country" />
                                                    </select>
                                                    <input id="city_select" name="fields[city][0]" type="text" class="form-control flex-grow-1" value="{account/fields/city/field}" active-role="city" required="required" placeholder="Город" />
                                                </div>
                                            </div>
                                        </div>
                                        <div id="user_language">
                                            <div class="input_label position-relative">
                                                <span class="input_headline">Я знаю языки<span class="star_required">*</span></span>
                                            </div>
                                            <div class="">
                                                <div class="form-group control">
                                                        <select id="select_language" class="form-control menu-select custom-select p_14 input-small" active-role="language">
                                                            <option value=""></option>
                                                            <xsl:apply-templates select="account/language" />
                                                        </select>
                                                        <div id="language_box" class="d-flex flex-wrap">
                                                            <xsl:apply-templates select="account/user_language" />
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="form-group control position-relative">
                                                <span class="input_headline">Опишите свои навыки:</span>
                                                <textarea name="fields[description][0]" class="form-control" id="company_textarea" rows="3" required="required"><xsl:value-of select="account/fields/description/field" /></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="information" class="flex-grow-1">
                                    <div class="box_content">
                                        <div id="user_specialization">
                                            <div class="input_label">
                                                <span class="input_headline position-static">Разделы и специализации<span class="star_required">*</span></span>
                                            </div>
                                            <div class="">
                                                <div class="form-group control">
                                                    <select id="select_specialization" class="form-control menu-select custom-select p_14 input-small" active-role="to_specialization">
                                                        <option value=""></option>
                                                        <xsl:apply-templates select="account/specialization" />
                                                    </select>
                                                    <div class="leng_max_error">Вы можете указать, не больше 4-х</div>
                                                </div>
                                                <div id="specialization_box" class="input-medium d-flex flex-wrap">
                                                    <xsl:apply-templates select="account/user_specialization" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="horizontal_ling_box">
                                            <div class="horizontal_ling"></div>
                                        </div>
                                        <div id="contact_inputs_box" class="position-relative">
                                            <div class="form-group control position-relative d-flex input_skype align-items-center no_input">
                                                <div class="mail_sing sing margin-right-small"></div>
                                                <div class="input_box align-items-center">
                                                    <xsl:value-of select="account/user/@email" />
                                                </div>
                                            </div>
                                            <div class="form-group control position-relative d-flex input_skype align-items-center">
                                                <div class="skype_sing sing margin-right-small"></div>
                                                <div class="input_box">
                                                    <input name="fields[skype][0]" type="text" class="form-control" value="{account/fields/skype/field}" active-role="skype" placeholder="" />
                                                </div>
                                            </div>
                                            <div id="phone_box" class="control">
                                                <xsl:choose>
                                                    <xsl:when test="count(account/phone) = 0">
                                                        <div class="form-group new control position-relative d-flex input_phone align-items-center">
                                                            <div class="phone_sing sing margin-right-small"></div>
                                                            <div class="input_box d-flex">
                                                                <!--<select class="country_phone form-control menu-select custom-select" required="required">
                                                                    <option value="">Страна...</option>
                                                                    <xsl:apply-templates select="account/country">
                                                                        <xsl:with-param name="type" select="'phone'" />
                                                                    </xsl:apply-templates>
                                                                </select>-->
                                                                <div class="select_imitation margin-right-small" data-type="text" data-default="Страна">
                                                                    <input name="phone[new][0][id_country_code]" type="hidden" value="" />
                                                                    <div data-value="" class="select_value"><span class="value">Страна</span></div>
                                                                    <div class="select_option_box">
                                                                        <table>
                                                                            <xsl:apply-templates select="account/country">
                                                                                <xsl:with-param name="type" select="'phone'" />
                                                                            </xsl:apply-templates>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <input name="phone[new][0][phone]" type="text" class="form-control phone_number" value="" active-role="phone" pattern="^[0-9]{{1,10}}" required="required" placeholder="0000000000" />
                                                            </div>
                                                            <div class="plus_icon"></div>
                                                        </div>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:apply-templates select="account/phone" />
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </div>
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
                            <button class="btn btn_green user_button" type="submit">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="country">
        <xsl:param name="type" select="'id'" />
        
        <xsl:param name="val">
            <xsl:choose>
                <xsl:when test="$type = 'phone'"><xsl:value-of select="@phone" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="@id" /></xsl:otherwise>
            </xsl:choose>
        </xsl:param>
        
        <xsl:choose>
            <xsl:when test="$type = 'phone'">
                <tr data-value="{@id}" data-text="+{$val}" class="select_option">
                    <td class="number_code">(+<xsl:value-of select="$val" />)</td> 
                    <td class=""><xsl:value-of select="." /></td>
                </tr>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="//account/user/@id_country = @id">
                        <option value="{$val}" selected="selected"><xsl:value-of select="." /></option>
                    </xsl:when>
                    <xsl:otherwise>
                        <option value="{$val}"><xsl:value-of select="." /></option>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <!-- Телефоны -->
    <xsl:template match="phone">
        <xsl:param name="id">
            <xsl:choose>
                <xsl:when test="normalize-space(@id) = ''">[new][<xsl:value-of select="position()" />]</xsl:when>
                <xsl:otherwise>[<xsl:value-of select="@id" />]</xsl:otherwise>
            </xsl:choose>
        </xsl:param>
        <xsl:param name="phone_role">
            <xsl:choose>
                <xsl:when test="normalize-space(@id) = ''">phone_new_<xsl:value-of select="position()" />_phone</xsl:when>
                <xsl:otherwise>phone_<xsl:value-of select="@id" />_phone</xsl:otherwise>
            </xsl:choose>
        </xsl:param>
        <xsl:param name="country_role">
            <xsl:choose>
                <xsl:when test="normalize-space(@id) = ''">phone_new_<xsl:value-of select="position()" />_id_country_code</xsl:when>
                <xsl:otherwise>phone_<xsl:value-of select="@id" />_id_country_code</xsl:otherwise>
            </xsl:choose>
        </xsl:param>
        <div class="form-group position-relative d-flex input_phone align-items-center">
            <div class="phone_sing sing margin-right-small"></div>
            <div class="input_box d-flex">
                <div class="select_imitation margin-right-small" data-type="text" data-default="Страна">
                    <input active-role="{$country_role}" name="phone{$id}[id_country_code]" type="hidden" value="{@id_country_code}" />
                    <div data-value="" class="select_value"><span class="value">+<xsl:value-of select="key('country',@id_country_code)/@phone" /></span></div>
                    <div class="select_option_box">
                        <table>
                            <xsl:apply-templates select="//account/country">
                                <xsl:with-param name="type" select="'phone'" />
                            </xsl:apply-templates>
                        </table>
                    </div>
                </div>
                <input name="phone{$id}[phone]" type="text" class="form-control phone_number" value="{@phone}" active-role="{$phone_role}" pattern="^[0-9]{{1,10}}" required="required" placeholder="0000000000" />
                <xsl:choose>
                    <xsl:when test="position() = 1">
                        <div class="plus_icon"></div>
                    </xsl:when>
                    <xsl:otherwise>
                        <div class="minus_icon"></div>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </div>
    </xsl:template>
        <xsl:template match="language">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>

    <xsl:template match="specialization">
        <option value="{@id}"><xsl:value-of select="." /></option>
    </xsl:template>
    
    <xsl:template match="user_specialization">
        <xsl:variable name="id" select="@id_specialization" />
        <div class="selected_input">
            <span class="string"><xsl:value-of select="//account/specialization[@id = $id]" /></span>
            <span class="close">×</span>
            <input name="specialization[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    
    <xsl:template match="user_language">
        <xsl:variable name="id" select="@id_language" />
        <div class="selected_input">
            <span class="string"><xsl:value-of select="//account/language[@id = $id]" /></span>
            <span class="close">×</span>
            <input name="language[{$id}]" value="{$id}" type="hidden" />
        </div>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>