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
    <xsl:param name="dir" select="filemanager/@path_utf" />
    <xsl:template match="/">
        <div id="filesystem">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="">В начало</a>
                    </li>
                    <xsl:apply-templates select="filemanager/breadcrumb" />
                </ol>
            </nav>
            <div class="row">
                <xsl:apply-templates select="filemanager/dir" />
            </div>
            <div class="row">
                <xsl:apply-templates select="filemanager/file" />
            </div>
        </div>
    </xsl:template>

    <xsl:template match="breadcrumb">
        <xsl:choose>
            <xsl:when test="@url">
                <li class="breadcrumb-item">
                    <a href="{@url}"><xsl:value-of select="@name" /></a>
                </li>
            </xsl:when>
            <xsl:otherwise>
                <li class="breadcrumb-item active">
                    <xsl:value-of select="@name" />
                </li>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template match="dir">
        <div class="card border-dark d-flex flex-wrap dir">
            <div class="card-body text-dark">
                <div class="icon">
                    <xsl:value-of select="//technical/svg/category" disable-output-escaping="yes" />
                </div>
            </div>
            <div class="card-header">
                <div class="text">
                    <span url="{@dir}" class="string"><xsl:value-of select="@name" disable-output-escaping="yes" /></span>
                </div>
            </div>
        </div>
    </xsl:template>
    <xsl:template match="file">
        <div class="card border-dark d-flex flex-wrap file">
            <div class="card-body text-dark">
                <div class="icon">
                    <img class="small_icon" src="/{$dir}{@file}" />
                </div>
            </div>
            <div class="card-header">
                <div class="text">
                    <span url="{@file}" class="string d-inline-block text-truncate"><xsl:value-of select="@name" disable-output-escaping="yes" /></span>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template name="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Library</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data</li>
            </ol>
        </nav>
    </xsl:template>
    <xsl:template match="*"></xsl:template>
    
</xsl:stylesheet>