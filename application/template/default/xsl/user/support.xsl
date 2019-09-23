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
    <xsl:param name="site" select="account/@site" />
    <xsl:param name="url" select="account/@url" />
    <xsl:param name="count_appeal" select="count(account/appeal)" />
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <a href="{$site}{$url}?type=arch" class="margin-right">Ахрив</a>
                <a href="{$site}{$url}">Обращения</a>
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="account/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="support" class="container-fluid big_container white view">
            <div class="container">
                <div class="card">
                    <h5 class="card-header d-flex align-items-center">
                        <div>Обращения</div>
                        <a href="{$site}{$url}/create" class="btn btn_green user_button ml-auto">Создать обращение</a>
                    </h5>
                    <div class="card-body">

                        <xsl:choose>
                            <xsl:when test="account/appeal">
                                <xsl:apply-templates select="account/appeal" />
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
        <xsl:variable name="answer">
            <xsl:if test="@new_user &gt; 0"> answer</xsl:if>
        </xsl:variable>
        <xsl:variable name="last">
            <xsl:if test="$count_appeal = position()"> last</xsl:if>
        </xsl:variable>
        <a href="{$site}{$url}/appeal/{@id}" class="d-flex appeal{$last}{$answer}">
            <div class="info_box w-100">
                <div class="title"><xsl:value-of select="@title" /></div>
                <div class="tiket">Обращение №<xsl:value-of select="@id" /></div>
            </div>
            <div class="time_box d-flex position-relative">
                <xsl:if test="$answer != ''">
                    <div class="text-success position-absolute">Ответы: <xsl:value-of select="@new_user" /></div>
                </xsl:if>
                <div class="string d-flex align-items-end"><xsl:value-of select="@time" /></div>
            </div>
        </a>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>