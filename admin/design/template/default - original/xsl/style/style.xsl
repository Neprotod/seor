<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="html" 
        omit-xml-declaration="yes" 
        indent="yes" />
    
    <xsl:key name="sort"
        match="/styles/box/style"
        use="@default" />
        
    <xsl:key name="type"
        match="/styles/box/style"
        use="concat(@type,@default,@status)" />
        
    <xsl:key name="styleDefault"
        match="/styles/box/style"
        use="concat(@style_type,@default)" />
        
    <xsl:key name="style"
        match="/styles/box/style"
        use="concat(@style_type,@default,@status)" />
        
    <xsl:key name="styleType"
        match="/styles/box/style"
        use="concat(@type,@style_type,@default,@status)" />    
        

    <!--
        Шаблон корневого элемента
    -->
    <xsl:template match="/">
        <xsl:call-template name="enabled" />
        <xsl:call-template name="disabled" />
        <script type="text/javascript">
            var style = $(".styles .style");
            
            style.each(function(){
                    if($(this).find('.action input:checkbox:checked').length > 0)
                        $(this).addClass('active');
            });
            
            style.click(function(){
                if($(this).find('.action input:checkbox:checked').length > 0){
                    $(this).addClass('active');
                }else{
                    $(this).removeClass('active');
                }
            });
        </script>
    </xsl:template>
    <!-- 
        Вывод стилей по умолчанию
    -->

    
    <!-- 
        Вывод подключенных стилей
    -->
    <xsl:template name="enabled" priority="1">
        <xsl:param name="default" select="key('sort',1)" />
        <xsl:param name="enable" select="key('sort',0)[@status = 1]" />
        
        <div class="interactive">
            <div class="styles">
                <xsl:if test="count($default) &gt; 0">
                    <h3>Используемые стили</h3>
                    <div class="box default">
                        <h4>По умолчанию</h4>
                        <xsl:call-template name="sortStyleType">
                            <xsl:with-param name="elements" select="$default" />
                            <xsl:with-param name="style" select="$default[generate-id(.) = generate-id(key('styleDefault',concat(@style_type,@default)))]" />
                        </xsl:call-template>
                    </div>
                </xsl:if>
                <xsl:if test="count($enable) &gt; 0">
                    <div class="box enabled">
                        <h4>Подключенные</h4>
                        <xsl:call-template name="sortStyleType">
                            <xsl:with-param name="elements" select="$enable" />
                        </xsl:call-template>
                    </div>
                </xsl:if>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template name="disabled" priority="2">
        <xsl:param name="elements" select="key('sort',0)[@status = 0]" />
        <xsl:if test="count($elements) &gt; 0">
            <div class="not_used">
                <div class="styles">
                    <h4>Не используемые стили</h4>
                    <xsl:call-template name="sortType">
                        <xsl:with-param name="elements" select="$elements" />
                    </xsl:call-template>
                </div>
            </div>
        </xsl:if>
    </xsl:template>
    
    
    <!-- 
        Сортируем по типу
    -->
    <xsl:template name="sortType">
        <xsl:param name="elements" />
        <xsl:param name="type" select="$elements[generate-id(.) = generate-id(key('type',concat(@type,@default,@status)))]" />
        

        
        <xsl:for-each select="$type">
            <xsl:sort select="$type" />
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
                
                <xsl:variable name="toSort" select="$elements[@type = current()/@type]" />
                <xsl:call-template name="sortStyleType">
                        <xsl:with-param name="elements" select="$toSort" />
                        <xsl:with-param name="style" select="$toSort[generate-id(.) = generate-id(key('styleType',concat(@type,@style_type,@default,@status)))]" />
                </xsl:call-template>
            </div>
        </xsl:for-each>
    </xsl:template>
    
    <!-- 
        Сортируем по типу стиля
    -->
    <xsl:template name="sortStyleType">
        <xsl:param name="elements" />
        <xsl:param name="style" select="$elements[generate-id(.) = generate-id(key('style',concat(@style_type,@default,@status)))]" />

        <xsl:if test="boolean($style)">
            <xsl:for-each select="$style">
                <div>
                    <xsl:attribute name="class"><xsl:value-of select="@style_type" /></xsl:attribute>
                    
                    <h5><xsl:value-of select="@style_type" /></h5>
                    <xsl:variable name="sort" select="@style_type" />
                    <xsl:apply-templates select="$elements[@style_type = $sort]">
                        <xsl:sort select="@status" data-type="number" order="descending" />
                        <xsl:sort select="@type" data-type="text" order="ascending" />
                    </xsl:apply-templates>
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
                <xsl:if test="path != ''">
                    <span class="path"><xsl:value-of select="@path" /></span>
                </xsl:if>
                <xsl:if test="title != ''">
                    <div class="split">
                        <xsl:if test="title != ''">
                            <span class="string"><xsl:value-of select="title" /></span>
                        </xsl:if>
                    </div>
                </xsl:if>
                <xsl:if test="description != ''">
                    <div class="description">
                        <div class="hr"></div>
                        <span class="string"><xsl:value-of select="description" /></span>
                    </div>
                </xsl:if>
            </div>
        </label>
    </xsl:template>
</xsl:stylesheet>