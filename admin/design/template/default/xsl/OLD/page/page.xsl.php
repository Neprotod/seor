<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output method="html" 
        encoding="UTF-8"
        omit-xml-declaration="yes"
        cdata-section-elements="li"
        indent="yes" />
    
    <xsl:template match="/">
        <xsl:apply-templates select="/styles" />
    </xsl:template>
    
</xsl:stylesheet>