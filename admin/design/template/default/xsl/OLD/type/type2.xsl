<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <xsl:key name="type"
        match="/types/type"
        use="@status" />    
        

    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <div class="types">
            <div class="box">
                <xsl:apply-templates select="key('type',1)" />
                <xsl:apply-templates select="key('type',0)" />
            </div>
        </div>
    </xsl:template>
    <!-- 
        Вывод стилей по умолчанию
    -->

    
    <!-- 
        Элемент тип
    -->
    
    <xsl:template match="type[@status = 1]">
        <!-- Определяем наименование типа-->
        <xsl:variable name="type_name">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@type = 'general'">Общий</xsl:when>
                <xsl:otherwise><xsl:value-of select="@type" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <label>
            <xsl:attribute name="class">type <xsl:value-of select="@type" /> default checked</xsl:attribute>
            <div class="action">
                
                
                <!--<input type="checkbox" style="" name="style[56]" value="1" />-->
            </div>
            
            <div class="information">
                <xsl:element name="input">
                    <xsl:attribute name="type">radio</xsl:attribute>
                    <xsl:attribute name="name">content_type</xsl:attribute>
                    <xsl:attribute name="value">1</xsl:attribute>
                    
                    <xsl:attribute name="checked">checked</xsl:attribute>
                    
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:element>
                
                <span class="name"><xsl:value-of select="@name" /></span>
                <xsl:if test="@ext != ''">
                    <span class="ext"><xsl:value-of select="@ext" /></span>
                </xsl:if>
                <xsl:if test="path != ''">
                    <span class="path"><xsl:value-of select="path" /></span>
                </xsl:if>
                <xsl:if test="title != ''">
                    <div class="split">
                        <xsl:if test="title != ''">
                            <span class="string"><xsl:value-of select="title" /></span>
                        </xsl:if>
                    </div>
                </xsl:if>
            </div>
            <xsl:if test="description != ''">
                <div class="description">
                    <div class="hr"></div>
                    <span class="string"><xsl:value-of select="description" /></span>
                </div>
            </xsl:if>
        </label>
    </xsl:template>
    
    <xsl:template match="type">
        <!-- Определяем наименование типа-->
        <xsl:variable name="type_name">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@type = 'general'">Общий</xsl:when>
                <xsl:otherwise><xsl:value-of select="@type" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <label>
            <xsl:attribute name="class">type <xsl:value-of select="@type" /></xsl:attribute>
            <div class="action">
                
                
                <!--<input type="checkbox" style="" name="style[56]" value="1" />-->
            </div>
            
            <div class="information">
                <xsl:element name="input">
                    <xsl:attribute name="type">radio</xsl:attribute>
                    <xsl:attribute name="name">content_type</xsl:attribute>
                    <xsl:attribute name="value">1</xsl:attribute>
                    
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:element>
                
                <span class="name"><xsl:value-of select="@name" /></span>
                <xsl:if test="@ext != ''">
                    <span class="ext"><xsl:value-of select="@ext" /></span>
                </xsl:if>
                <xsl:if test="path != ''">
                    <span class="path"><xsl:value-of select="path" /></span>
                </xsl:if>
                <xsl:if test="title != ''">
                    <div class="split">
                        <xsl:if test="title != ''">
                            <span class="string"><xsl:value-of select="title" /></span>
                        </xsl:if>
                    </div>
                </xsl:if>
            </div>
            <xsl:if test="description != ''">
                <div class="description">
                    <div class="hr"></div>
                    <span class="string"><xsl:value-of select="description" /></span>
                </div>
            </xsl:if>
        </label>
    </xsl:template>
</xsl:stylesheet>