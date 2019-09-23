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
        <form method="post" action="{customers/@action}" data-toggle="validator" role="form">
            <input type="hidden" name="session_id" value="{$seesion_id}" />
            <table id="view_settings" class="table table-hover table-bordered">
                <xsl:call-template name="header"/>
                <xsl:apply-templates select="customers/custom"/>
            </table>
            <button type="submit" class="btn btn-danger">Удалить</button>
        </form>
    </xsl:template>
    
    <xsl:template match="custom">
    
        <xsl:variable name="status">
            <xsl:choose>
                <xsl:when test="@status = '1'">
                    <div class="card alert-success status_element">ON</div>
                </xsl:when>
                <xsl:otherwise>
                    <div class="card alert-danger status_element">OFF</div>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <tbody>
            <tr>
                <th scope="row" class="align-middle">
                    <xsl:value-of select="@id" /> 
                </th>
                <td class="align-middle">
                    <xsl:value-of select="@name" /> 
                </td>
                <td class="align-middle">
                    <xsl:value-of select="value" /> 
                </td>
                <td class="align-middle">
                    <xsl:value-of select="title" /> 
                </td>
                <td class="align-middle">
                    <xsl:value-of select="description" /> 
                </td>
                <td class="align-middle">
                    <xsl:copy-of select="$status"/> 
                </td>
                <td class="align-middle">
                    <label class="new_checkbox">
                        <input name="delete[{@id}]"  type="checkbox" />
                    </label>
                </td>
            </tr>
        </tbody>
    </xsl:template>
    <xsl:template name="header">
        <thead>
            <tr class="bg-light">
                <th scope="col" class="align-middle">
                    ID
                </th>
                <th scope="col" class="align-middle">
                    Имя поля (name)
                </th>
                <th scope="col" class="align-middle">
                    Значение (value)
                </th>
                <th scope="col" class="align-middle">
                    Заголовок (title)
                </th>
                <th scope="col" class="align-middle">
                    Описание (description)
                </th>
                <th scope="col" class="align-middle">
                    Включен ли (status)
                </th>
                <th scope="col" class="align-middle">
                    Удалить
                </th>
            </tr>
        </thead>
    </xsl:template>
    
    <xsl:template match="*">

    </xsl:template>
    
</xsl:stylesheet>