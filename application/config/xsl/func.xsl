<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:fn="https://seor.ua/functions"
    xmlns:func="http://exslt.org/functions"
    extension-element-prefixes="func">
    
    <!-- Функция обрезает конец строки по заданному символу -->
    <func:function name="fn:substring-before-last">
        <xsl:param name="input"/>
        <xsl:param name="substr"/>
        
        <xsl:variable name="result">
            <xsl:if test="contains($input,$substr)">
                <xsl:value-of select="substring-before($input,$substr)" />
                <xsl:variable name="test" select="substring-after($input,$substr)" />
                <xsl:if test="contains($test,$substr)">
                   <xsl:value-of select="$substr" /> 
                   <xsl:value-of select="fn:substring-before-last($test,$substr)" /> 
                </xsl:if>
            </xsl:if> 
        </xsl:variable>
        
        
        <func:result>
            <xsl:value-of select="$result"/>
        </func:result>
    </func:function>
    
</xsl:stylesheet>