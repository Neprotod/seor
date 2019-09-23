<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:param name="count_ads" select="//count/ads/@count_ads" />
    <xsl:template match="/">
        
        <xsl:if test="$count_ads != 0">
            <div class="table_box">
                <a class="d-flex align-items-center moder_box" href="{count/@url}/ads">
                    <div>
                        Вакансии ожидают модерации
                    </div>
                    <div class="border-left">
                        <xsl:value-of select="$count_ads" />
                    </div>
                </a>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>