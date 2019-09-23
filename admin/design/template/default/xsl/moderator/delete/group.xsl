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
    <xsl:param name="seesion_id" select="moderator/@session_id" />
    <xsl:template match="/">
        <form method="post" action="{moderator/@action}" data-toggle="validator" role="form">
            <input type="hidden" name="session_id" value="{$seesion_id}" />
            <table id="view_group" class="table table-hover table-bordered">
                <xsl:call-template name="header"/>
                <xsl:apply-templates select="moderator/moder"/>
            </table>
            <button type="submit" class="btn btn-danger">Удалить</button>
        </form>
    </xsl:template>
    
    <xsl:template match="moder">
    
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
                    <xsl:value-of select="position()"/>
                </th>
                <td class="align-middle">
                    <xsl:value-of select="@type"/>
                </td>
                <td class="align-middle">
                   <span><xsl:value-of select="title"/></span>
                </td>
                <td class="align-middle">
                    <span><xsl:value-of select="description"/></span>
                </td>
                <td class="align-middle">
                    <label class="new_checkbox">
                        <input name="delete[{@id}]" value="{@id}"  type="checkbox" />
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
                    Тип пользователя (type)
                </th>
                <th scope="col" class="align-middle">
                    Название (title)
                </th>
                <th scope="col" class="align-middle">
                    Описание (description)
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