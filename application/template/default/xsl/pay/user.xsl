<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <xsl:param name="default_currency" select="pay/technical/currency" />
    <xsl:param name="default_rate" select="pay/technical/currency/rate" />
    <xsl:param name="user_cost">
        <xsl:choose>
            <xsl:when test="pay/user/@employer != 1">
                <xsl:value-of select="pay/price[@name = 'ads']" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="pay/price[@name = 'ads_create']" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    
    <xsl:key name="price_account" match="//pay/price[@type_name = 'account']" use="@name" />
    
    <xsl:decimal-format name="currency" decimal-separator="." grouping-separator=" " />
    
    
    
    <!--
        Шаблон корневого элемента
    -->
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="pay/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="pay_page" class="container-fluid big_container white">
            <div class="container">
                <h1 class="h4">Оплата услуг</h1>
                <div id="currency_check">
                    <xsl:apply-templates select="pay/currency">
                        <xsl:with-param name="type" select="'a'" />
                    </xsl:apply-templates>
                </div>
                <form id="container_pay" action="{pay/@site}{pay/@url}" method="post" class="d-flex justify-content-center flex-wrap">
                    <input type="hidden" name="currency" value="{$default_currency/name}" />
                    <div class="d-flex flex-wrap justify-content-center">
                        <div class="card margin-right">
                            <div class="card-header d-flex flex-column justify-content-center align-items-center card-green">
                                <div class="big_chap">Базовый</div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group justify-content-center align-items-center text-center">
                                    <li class="list-group-item border-bottom">Личный кабинет</li>
                                    <li class="list-group-item border-bottom">
                                        <div>10 бесплатных</div> 
                                        <div>вакансий</div>
                                    </li>
                                    <li class="list-group-item border-bottom">
                                        <div>Создание вакансий </div> 
                                        <div>всего <xsl:value-of select="floor($user_cost)" /><span class="coin_sing"></span></div> 
                                    </li>
                                    <li class="list-group-item pay_mouth border-bottom">1 месяц</li>
                                    <li class="list-group-item pay_count text-nowrap">
                                        <span class="amout">
                                            <xsl:value-of select="format-number(floor(key('price_account','one')) * $default_rate,'## ###.##','currency')" />
                                        </span> 
                                        <span class="small">
                                            <span class="currency_sing">
                                                <xsl:value-of select="pay/technical/currency/name" />
                                            </span> 
                                            / 
                                            <xsl:value-of select="floor(key('price_account','one'))" /> 
                                            <span class="coin_sing"></span>
                                        </span> 
                                    </li>
                                    <li class="list-group-item last-item">
                                        <button class="btn btn_green" name="account[one]" type="submit" value="1">Оформить</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card margin-right">
                            <div class="card-header d-flex flex-column justify-content-center align-items-center card-dragon-age">
                                <div class="big_chap">Корпоративный</div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group justify-content-center align-items-center text-center">
                                    <li class="list-group-item border-bottom">Личный кабинет</li>
                                    <li class="list-group-item border-bottom">
                                        <div>20 бесплатных</div> 
                                        <div class="text-nowrap">вакансий <span class="bonus">+ 5 бонусных</span></div>
                                    </li>
                                    <li class="list-group-item border-bottom">
                                        <div>Создание вакансий </div> 
                                        <div>всего <xsl:value-of select="floor($user_cost)" /><span class="coin_sing"></span></div> 
                                    </li>
                                    <li class="list-group-item pay_mouth border-bottom">3 месяц</li>
                                   
                                    <li class="list-group-item pay_count text-nowrap">
                                        <span class="amout">
                                            <xsl:value-of select="format-number(floor(key('price_account','three')) * $default_rate,'## ###.##','currency')" />
                                        </span> 
                                        <span class="small">
                                            <span class="currency_sing">
                                                <xsl:value-of select="pay/technical/currency/name" />
                                            </span> 
                                            / 
                                            <xsl:value-of select="floor(key('price_account','three'))" /> 
                                            <span class="coin_sing"></span>
                                        </span> 
                                    </li>
                                    <li class="list-group-item last-item">
                                        <button class="btn card_dragon_age" name="account[three]" type="submit" value="1">Оформить</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center">
                        <div class="card margin-right">
                            <div class="card-header d-flex flex-column justify-content-center align-items-center card-orchid">
                                <div class="big_chap">Бизнес</div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group justify-content-center align-items-center text-center">
                                    <li class="list-group-item border-bottom">Личный кабинет</li>
                                    <li class="list-group-item border-bottom">
                                        <div>30 бесплатных</div> 
                                        <div class="text-nowrap">вакансий <span class="bonus">+ 10 бонусных</span></div>
                                    </li>
                                    <li class="list-group-item border-bottom">
                                        <div>Создание вакансий </div> 
                                        <div>всего <xsl:value-of select="floor($user_cost)" /><span class="coin_sing"></span></div> 
                                    </li>
                                    <li class="list-group-item pay_mouth border-bottom">6 месяц</li>
                                    <li class="list-group-item pay_count text-nowrap">
                                        <span class="amout">
                                            <xsl:value-of select="format-number(floor(key('price_account','six')) * $default_rate,'## ###.##','currency')" />
                                        </span> 
                                        <span class="small">
                                            <span class="currency_sing">
                                                <xsl:value-of select="pay/technical/currency/name" />
                                            </span> 
                                            / 
                                            <xsl:value-of select="floor(key('price_account','six'))" /> 
                                            <span class="coin_sing"></span>
                                        </span> 
                                    </li>
                                    <li class="list-group-item last-item">
                                        <button class="btn btn_orchid" name="account[six]" type="submit" value="1">Оформить</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card margin-right">
                            <div class="card-header d-flex flex-column justify-content-center align-items-center card-yellow">
                                <div class="big_chap">Про</div>
                            </div>
                            <div class="card-body">
                                <ul class="list-group justify-content-center align-items-center text-center">
                                    <li class="list-group-item border-bottom">Личный кабинет</li>
                                    <li class="list-group-item border-bottom">
                                        <div>60 бесплатных</div> 
                                        <div class="text-nowrap">вакансий <span class="bonus">+ 20 бонусных</span></div>
                                    </li>
                                    <li class="list-group-item border-bottom">
                                        <div>Создание вакансий </div> 
                                        <div>всего <xsl:value-of select="floor($user_cost)" /><span class="coin_sing"></span></div> 
                                    </li>
                                    <li class="list-group-item pay_mouth border-bottom">12 месяц</li>
                                    <li class="list-group-item pay_count text-nowrap">
                                        <span class="amout">
                                            <xsl:value-of select="format-number(floor(key('price_account','year')) * $default_rate,'## ###.##','currency')" />
                                        </span> 
                                        <span class="small">
                                            <span class="currency_sing">
                                                <xsl:value-of select="pay/technical/currency/name" />
                                            </span> 
                                            / 
                                            <xsl:value-of select="floor(key('price_account','year'))" /> 
                                            <span class="coin_sing"></span>
                                        </span> 
                                    </li>
                                    <li class="list-group-item last-item">
                                        <button class="btn btn_yellow" name="account[year]" type="submit" value="1">Оформить</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="out_info">
                    *Стоимость покупки контактов без оплаты какого-либо из пакетов 10<span class="coin_sing"></span>
                </div>
                <div id="seor_coin">
                    <h2 class="h4">Покупка Seor Coin</h2>
                    <div class="margin-top-medium description_seor_coin">
                        Seor Coin - это внутресайтовая валюта, за которую клиенты могут преобретать контакты и оплачивать иные услуги сайта.<br/>
                        При покупке монет предусмотрены бонусы за приобретение от 50 и более монет.
                    </div>
                    
                    <form id="form_coin" action="{pay/@site}{pay/@url}" method="post" autocomplete="off" class="card margin-top-medium">
                        <div class="card-header d-flex flex-column card-green">
                            <div class="big_chap">Seor Coin</div>
                        </div>
                        <div class="card-body position-relative">
                            <div class="error_msg"></div>
                            <div class="row top-row">
                                <div class="col">
                                    <div id="form_seor_count" class="form-group position-relative">
                                        <input name="seor" type="text" class="form-control seor_input" value="" placeholder="0" />
                                        <div class="caption_seor">
                                            <span>S.Coin</span>
                                        </div>
                                    </div>
                                    <div id="form_seor_currency" class="form-group position-relative margin-top">
                                        <input name="currency" type="text" class="form-control seor_input" value="" placeholder="0" />
                                        <div class="caption_seor">
                                            <span>UAH</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 position-relative">
                                    <div id="green_frame"></div>
                                    <div id="converter" class="select_imitation margin-right-small" data-type="text" data-default="Страна">
                                        <input name="currency_type" type="hidden" value="{$default_currency/name}" />
                                        <div class="imet_box d-flex align-items-center justify-content-center">
                                            <div class="text-nowrap">
                                                1
                                                <span class="coin_sing"></span> = 
                                                <span data-value="{$default_currency/name}" class="select_value">
                                                    <xsl:value-of select="format-number($default_rate,'## ###.###','currency')" /> 
                                                    <xsl:value-of select="$default_currency/name" /></span>
                                            </div>
                                        </div>
                                        <div class="select_option_box">
                                            <table>
                                                <xsl:apply-templates select="pay/currency" />
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row bottom-row padding-bottom">
                                <div class="col position-relative">
                                    <span class="text-grey">+10% за каждые 50 seor coin </span>
                                    <div id="form_seor_bonus" class="d-flex align-items-center text-nowrap">
                                        <span>Бонус к покупке Seor Coin </span><span class="amount">0</span>
                                    </div>
                                </div>
                                <div class="col-4 position-relative d-flex align-items-center justify-content-center">
                                    <button class="btn btn_orange_solid" type="submit">Купить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="currency">
        <xsl:param name="type" select="'input'" />
        <xsl:choose>
            <xsl:when test="$type = 'input'">
                <tr data-value="{@name}" data-text="{format-number(@rate,'## ###.###','currency')} {@name}" class="select_option">
                    <td class="">1<span class="coin_sing"></span></td> 
                    <td class="">=</td>
                    <td class="">
                        <span><xsl:value-of select="format-number(@rate,'## ###.###','currency')" /></span>
                        <span> <xsl:value-of select="@name" /></span>
                    </td>
                </tr>
            </xsl:when>
            <xsl:when test="$type = 'a'">
                <xsl:variable name="class">
                    <xsl:if test="$default_currency/name = @name">active</xsl:if>
                </xsl:variable>
                <a href="{pay/@site}{pay/@action}?currency={@name}" class="{$class}"><xsl:value-of select="@name" /></a>
            </xsl:when>
            <xsl:otherwise>
                
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="*">
       
    </xsl:template>
</xsl:stylesheet>