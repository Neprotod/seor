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
    <xsl:template match="/">
        <xsl:variable name="type">
            <xsl:choose>
                <xsl:when test="change/error">
                    alert-danger
                </xsl:when>
                <xsl:otherwise>
                    alert-success
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <section class="container-fluid big_container white">
            <div class="container">
                <div class="alert {$type} table_box m-auto">
                    <xsl:choose>
                        <xsl:when test="change/title">
                            <h4><xsl:value-of select="change/title" /></h4>
                        </xsl:when>
                        <xsl:otherwise>
                            <h4>Ничего не произошло.</h4>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:choose>
                        <xsl:when test="change/message">
                             <div>
                                <xsl:value-of select="change/message" disable-output-escaping="yes" />
                            </div>
                        </xsl:when>
                        <xsl:otherwise>
                            <div>
                                Ссылка не работает.
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>
                    <div class="margin-top-small">
                        В случай проблем обращайтесь в <a href="/support">тех поддержку</a>.
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
</xsl:stylesheet>