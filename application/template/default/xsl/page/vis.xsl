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
    <xsl:param name="root" select="categories/@root" />
    <xsl:param name="site" select="categories/@site" />
    <xsl:template match="/">
        <section id="vis_inside" class="container-fluid big_container white">
            <div class="container">
                <div class="row">
                    <div class="col col-auto">
                        <div id="vis_img">
                            <h1 class="h2 box"><xsl:value-of select="page/@title" /></h1>
                            <img style="width:100%; height:100%;" src="/{page/image}" alt="{page/@titl}" />
                        </div>
                    </div>
                    <div class="col visa_text">
                        <h2 class="h2"><xsl:value-of select="page/h2" disable-output-escaping="yes" /></h2>
                        <xsl:value-of select="page/content" disable-output-escaping="yes" />
                    </div>
                </div>
                
                <div class="row">
                    <div class="col">
                        <table class="table table-bordered table_vis color_grey p_14">
                            <xsl:apply-templates select="page/params" />
                        </table>
                    </div>
                    <div class="col col-3 prop_correct">
                        <ul class="visa_ul color_grey p_14">
                            <xsl:apply-templates select="page/field" />
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="field">
         <li><xsl:value-of select="." /></li>
    </xsl:template>
    <xsl:template match="params">
        <tr>
            <xsl:apply-templates select="param" />
        </tr>
    </xsl:template>
    <xsl:template match="param">
        <td><xsl:value-of select="." /></td>
    </xsl:template>
    <xsl:template match="*"></xsl:template>
</xsl:stylesheet>