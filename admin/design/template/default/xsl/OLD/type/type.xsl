<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output 
        method="html" 
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
                <xsl:apply-templates select="key('type',0)">
                    <xsl:sort select="@no_exist" data-type="number" order="descending" />
                </xsl:apply-templates>
            </div>
        </div>
        <script type="text/javascript">
            $(".type input").each(function(){
                $(this).css('display','none');
                if($(this).attr("checked")){
                    $(this).parents('.type').addClass('checked');
                }
            });
            
            $(".type").click(function(){
                $(".type").each(function(){
                    if($(this).find('input').attr("checked")){
                        $(this).addClass('checked');
                    }else{
                        $(this).removeClass('checked');
                    }
                    
                });
            });
        </script>
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
        <xsl:variable name="exist">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@no_exist = 1"> no_exist</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <div class="default">
            <h4>Подключенный</h4>
            <label>
                <xsl:attribute name="class">type <xsl:value-of select="concat(@type,$exist)" /> checked</xsl:attribute>
                <div class="action">
                    <xsl:element name="input">
                        <xsl:attribute name="type">radio</xsl:attribute>
                        <xsl:attribute name="name">content_type</xsl:attribute>
                        <xsl:attribute name="value"><xsl:value-of select="@id" /></xsl:attribute>
                        
                        <xsl:attribute name="checked">checked</xsl:attribute>
                        
                        <xsl:attribute name="style">display:none;</xsl:attribute>
                    </xsl:element>
                </div>
                <xsl:call-template name="content" />
            </label>
        </div>
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
        
        <xsl:variable name="exist">
            <!-- Так значение добавится в переменную вовремя -->
            <xsl:choose>
                <xsl:when test="@no_exist = 1"> no_exist</xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <label>
            <xsl:attribute name="class">type <xsl:value-of select="concat(@type,$exist)" /></xsl:attribute>
            <div class="action">
                <xsl:element name="input">
                    <xsl:attribute name="type">radio</xsl:attribute>
                    <xsl:attribute name="name">content_type</xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="@id" /></xsl:attribute>
                    
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:element>
            </div>
            
            <xsl:call-template name="content" />
        </label>
    </xsl:template>
    
    <xsl:template name="content">
        <div class="information">
            <span class="name"><xsl:value-of select="@name" /></span>
            <xsl:if test="@ext != ''">
                <span class="ext"><xsl:value-of select="concat(' ',@ext)" /></span>
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
        <xsl:if test="@no_exist = 1">
            <div class="exist">
                <span class="string">Данный тип не найден</span>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>