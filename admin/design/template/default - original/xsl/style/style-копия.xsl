<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <xsl:key name="sort"
        match="/styles/box/style"
        use="@default" />
        
    <xsl:key name="type"
        match="/styles/box/style"
        use="concat(@type,@default)" />
        
    <xsl:key name="style"
        match="/styles/box/style"
        use="concat(@type,@style_type,@default)" />    
        

    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <div class="styles">
            <xsl:apply-templates select="/styles/box" />
        </div>
    </xsl:template>
    <!-- 
        Вывод стилей по умолчанию
    -->
    <xsl:template match="box[@default = 1]" priority="1">
        <div class="box default">
            <h4>По умолчанию</h4>
            <xsl:call-template name="sortStyleType">
                <xsl:with-param name="style" select="style[generate-id(.) = generate-id(key('sort',@default))]" />
            </xsl:call-template>
        </div>
    </xsl:template>
    <!-- 
        Вывод остальных стилей
    -->
    <xsl:template match="box[@default = 0]" priority="2">
        <xsl:call-template name="sortType">
            <xsl:with-param name="type" select="style[generate-id(.) = generate-id(key('type',concat(@type,@default)))]" />
        </xsl:call-template>
    </xsl:template>

    <!-- 
        Сортируем по типу
    -->
    <xsl:template name="sortType">
        <xsl:param name="type" />
        
        <xsl:for-each select="$type">
            <div>
                <xsl:attribute name="class">box <xsl:value-of select="@type" /></xsl:attribute>
                
                <!-- Определяем наименование -->
                <xsl:variable name="type_name">
                    <!-- Так значение добавится в переменную вовремя -->
                    <xsl:choose>
                        <xsl:when test="@type = 'general'">Общий</xsl:when>
                        <xsl:otherwise><xsl:value-of select="@type" /></xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                
                <h4>Стили: <span><xsl:value-of select="$type_name" /></span></h4>
                
                <xsl:variable name="sort" select="@type" />
                <xsl:call-template name="sortStyleType">
                    <xsl:with-param name="style" select="ancestor::box[1]/style[@type = $sort][generate-id(.) = generate-id(key('style',concat(@type,@style_type,@default)))]" />
                </xsl:call-template>
            </div>
        </xsl:for-each>
    </xsl:template>
    <!-- 
        Сортируем по типу стиля
    -->
    <xsl:template name="sortStyleType">
        <xsl:param name="style" />
        
        <xsl:if test="boolean($style)">
            <xsl:for-each select="$style">
                <div>
                    <xsl:attribute name="class"><xsl:value-of select="@style_type" /></xsl:attribute>
                    
                    <h5><xsl:value-of select="@style_type" /></h5>
                    
                    <!-- Стили по умолчанию должны быть в одном блоке стиля -->
                    <xsl:choose>
                        <xsl:when test="@default = 1">
                            <xsl:variable name="sort" select="@style_type" />
                            <xsl:apply-templates select="ancestor::box[1]/style[@style_type = $sort]" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:apply-templates select="key('style',concat(@type,@style_type,@default))" />
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </xsl:for-each>
            
        </xsl:if>
    </xsl:template>
    <!-- 
        Элемент стиля
    -->
    <xsl:template match="style">
        <!-- Определяем наименование типа-->
        <xsl:variable name="type_name">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@type = 'general'">Общий</xsl:when>
                <xsl:otherwise><xsl:value-of select="@type" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <label>
            <xsl:attribute name="class">style <xsl:value-of select="@type" /></xsl:attribute>
            <div class="action">
                <xsl:element name="input">
                    <xsl:attribute name="type">checkbox</xsl:attribute>
                    <xsl:attribute name="name">style[<xsl:value-of select="@id" />]</xsl:attribute>
                    <xsl:attribute name="value">1</xsl:attribute>
                    <xsl:choose>
                        <xsl:when test="@default = 1 and (@status = '')">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:when>
                        <xsl:when test="@status = 1">
                            <xsl:attribute name="checked">checked</xsl:attribute>
                        </xsl:when>
                    </xsl:choose>
                </xsl:element>
                
                <!--<input type="checkbox" style="" name="style[56]" value="1" />-->
            </div>
            
            <div class="information">
                <span class="type"><xsl:value-of select="$type_name" /></span>
                <span class="name"><xsl:value-of select="@name" /></span>
                <xsl:if test="boolean(@path)">
                    <span class="path"><xsl:value-of select="@path" /></span>
                </xsl:if>
                <xsl:if test="boolean(@title) and boolean(@description)">
                    <div class="split">
                        <xsl:if test="boolean(@title)">
                            <span class="string"><xsl:value-of select="@title" /></span>
                        </xsl:if>
                        <xsl:if test="boolean(@description)">
                            <span class="string"><xsl:value-of select="@description" /></span>
                        </xsl:if>
                    </div>
                </xsl:if>
            </div>
        </label>
    </xsl:template>
</xsl:stylesheet>