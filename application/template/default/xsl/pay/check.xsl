<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <xsl:param name="dop" select="pay/dop" />
    
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
                <div class="alert alert-{$dop/@type}">
                    <h4><xsl:value-of select="$dop/@title" /></h4>
                    <div>
                        <xsl:value-of select="$dop/message" />
                    </div>
                    <div>
                        В случай проблем обращайтесь в <a href="">тех поддержку</a>.
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="*">
       
    </xsl:template>
</xsl:stylesheet>