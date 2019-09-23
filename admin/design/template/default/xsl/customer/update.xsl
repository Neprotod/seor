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
    <xsl:param name="seesion_id" select="customers/@session_id" />
    <xsl:template match="/">
        <div id="form_block">
            <form method="post" action="{customers/@action}" data-toggle="validator" role="form">
                <input type="hidden" name="session_id" value="{$seesion_id}" />
                <xsl:call-template name="new_custom"/>
                <xsl:apply-templates select="customers/custom"/>
                <button type="submit" class="btn btn-primary">Применить</button>
            </form>
        </div>
    </xsl:template>
    
    <xsl:template match="custom">
        <xsl:variable name="settings" select="concat('settings[',@name,']')" />

        <div class="form-group card">
            <label><xsl:value-of select="title" /> </label>
            <input type="hidden" class="form-control" name="{$settings}[id]" value="{@id}" />
            <div class="row">
                <div class="col position_element id" style="width:20px;">
                    <xsl:value-of select="position()"/>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[name]" value="{@name}" active-role="{@id}_name" required="required" />
                    <div class="sub">Имя поля (name)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[value]" value="{value}" active-role="{@id}_value" required="required" />
                    <div class="sub">Значение (value)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[title]" value="{title}" />
                    <div class="sub">Заголовок (title)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[description]" value="{description}"/>
                    <div class="sub">Описание (description)</div>
                </div>
                <div class="col status_control">
                    <input type="range" class="custom-range" name="{$settings}[status]" min="0" max="1" step="1" value="{@status}" />
                    <div class="sub">Включен ли (status)</div>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template name="new_custom">
        <xsl:variable name="settings" select="'insert[new]'" />

        <div class="form-group card new-item">
            <label><xsl:value-of select="title" /> </label>
            <div class="row">
                <div class="col position_element id" style="width:20px;">
                    New
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[name]" value="{//technical/new/name}" active-role="insert_new_name" />
                    <div class="sub">Имя поля (name)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[value]" value="{//technical/new/value}" active-role="insert_new_value" />
                    <div class="sub">Значение (value)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[title]" value="" />
                    <div class="sub">Заголовок (title)</div>
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="{$settings}[description]" value=""/>
                    <div class="sub">Описание (description)</div>
                </div>
                <div class="col status_control">
                    <input type="range" class="custom-range" name="{$settings}[status]" min="0" max="1" step="1" value="0" />
                    <div class="sub">Включен ли (status)</div>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>