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
    <xsl:param name="site" select="support/@site" />
    <xsl:param name="url" select="support/@url" />
    <xsl:param name="count_appeal" select="count(support/appeal)" />
    <xsl:template match="/">
        <section id="support" class="container-fluid big_container white view">
            <div class="container">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center">
                        <div>Обращения</div>
                        <div class="btn btn_green user_button ml-auto">
                            <xsl:choose>
                                <xsl:when test="support/@acrh = 1">
                                    Обращений в ахриве
                                </xsl:when>
                                <xsl:otherwise>
                                    Всего непрочитанных обращений: 
                                </xsl:otherwise>
                            </xsl:choose>
                            
                            <b><xsl:value-of select="support/@count" /></b>
                        </div>
                    </h5>
                    <div class="card-body">

                        <xsl:choose>
                            <xsl:when test="support/appeal">
                                <xsl:apply-templates select="support/appeal" />
                            </xsl:when>
                            <xsl:otherwise>
                                <div class="no_appeal">Нет обращений</div>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="appeal">
        <xsl:variable name="last">
            <xsl:if test="$count_appeal = position()">last</xsl:if>
        </xsl:variable>
        <a href="{$site}/admin/support/fetch/get?id={@id}" class="d-flex appeal  {$last}">
            <div class="info_box w-100">
                <div class="title"><xsl:value-of select="@title" /></div>
                <div class="tiket">Обращение №<xsl:value-of select="@id" /></div>
            </div>
            <div class="time_box">
                <div class="string"><xsl:value-of select="@time" /></div>
            </div>
        </a>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>