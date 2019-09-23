<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <xsl:param name="default_currency" select="pay/@currency" />
    <xsl:param name="cost" select="pay/@cost" />
    <xsl:param name="seor" select="pay/@seor" />
    <xsl:param name="order" select="pay/order" />
    <xsl:param name="liqpay" select="pay/technical/liqpay" />
    
    <xsl:param name="user" select="pay/user" />
    
    <xsl:decimal-format name="currency" decimal-separator="." grouping-separator=" " />
    
    <!--
        Шаблон корневого элемента
    -->
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <div>
                    <a href="{pay/@site}/account/pay">Назад</a>
                </div>
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="pay/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="pay_checkout" class="container-fluid big_container white">
            <div class="container">
                <div class="top_page">
                    <h1 class="h4">Способы оплаты</h1>
                    <div class="right_box text-nowrap pay_count">
                        <span class="text_grey">К оплате:</span>
                        <span class="amout"><xsl:value-of select="format-number($cost,'## ###.###','currency')" /></span>
                        <span class="small">
                            <span class="currency_sing"><xsl:value-of select="$default_currency" /></span> 
                            <xsl:if test="$seor != 0">
                                / <xsl:value-of select="floor($seor)" /><span class="coin_sing"></span>
                            </xsl:if>
                        </span>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="center_page d-flex align-items-center flex-wrap">
                    <xsl:if test="$seor != 0">
                        <form id="buy_seor" class="pay_box flex-column" action="{pay/@site}/account/pay/order/seor" method="post">
                            <input name="order_id" type="hidden" value="{$order/@id}" />
                            <div class="big_seor_coin"></div>
                            <div class="string">Seor Coin</div>
                            <div class="account_count text-nowrap">
                                На счету: <xsl:value-of select="floor($user/@seor)" /><span class="coin_sing"></span>
                            </div>
                            <input type="submit" />
                        </form>
                    </xsl:if>
                    <form id="liqpay" class="pay_box" method="POST" accept-charset="utf-8" action="https://www.liqpay.ua/api/3/checkout">
                        <input type="hidden" name="data" value="{$liqpay/data}" />
                        <input type="hidden" name="signature" value="{$liqpay/signature}" />
                        <div class="img"></div>
                        <input type="submit" />
                    </form>
                </div>
                <form id="purpose" action="{pay/@site}{pay/@url}" method="post" class="d-flex align-items-center flex-wrap">
                    <span class="string_grey">Назначение платежа: </span>
                    <xsl:choose>
                        <xsl:when test="$order/@id_order_action = 2">
                            <span class="string"><xsl:value-of select="$order/@description" /> </span>
                            <span class="string"><xsl:value-of select="floor($order/@amount div $order/@rate)" /> </span>
                        </xsl:when>
                        <xsl:otherwise>
                            <span class="string"><xsl:value-of select="$order/@description" /></span>
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    <button type="submit" class="btn btn-secondary" name="action" value="cancel">Отменить платеж</button>
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="*">
       
    </xsl:template>
</xsl:stylesheet>