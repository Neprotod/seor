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
    <xsl:param name="price" select="users/promo" />
    <xsl:param name="site" select="users/@site" />
    <xsl:param name="url" select="users/@url" />
    <xsl:template match="/">
        <section id="users" class="container-fluid big_container white view">
            <div class="container">
                Укажи цену в гривнах для каждый из валют
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off" style="margin-bottom:20px;">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <table class="table">
                        <tbody>
                            <xsl:apply-templates select="users/currency" />
                        </tbody>
                    </table>
                    <input type="submit" value="Сохранить" />
                </form>
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <table class="table">
                        <tbody>
                            <xsl:apply-templates select="users/price" />
                        </tbody>
                    </table>
                    <input type="submit" value="Сохранить" />
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="currency">
        <tr>
            <td>
                <b><xsl:value-of select="@name" /></b>
            </td>
            <td>
                <input type="text" name="currency[{@id}][rate]" class="form-control" value="{@rate}"/>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="price">
        <tr>
            <td colspan="3" class="text-center">
                <b><xsl:value-of select="@title" /></b>
            </td>
        </tr>
        <tr>
            <td>
                Цена в сеор
            </td>
            <td>
                Бесплатные клики
            </td>
            <td>
                Бесплатные вакансии
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" name="price[{@id}][amount]" class="form-control" value="{@amount}"/>
            </td>
            <td>
                <input type="text" name="price[{@id}][clicks]" class="form-control" value="{@clicks}"/>
            </td>
            <td>
                <input type="text" name="price[{@id}][adc]" class="form-control" value="{@adc}"/>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>