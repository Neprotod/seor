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
            <table id="view_settings" class="table table-hover table-bordered">
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
                    <xsl:value-of select="@id" />
                </th>
                <td class="align-middle">
                    <xsl:value-of select="@login"/>
                </td>
                <td class="align-middle">
                    <xsl:value-of select="display_name"/>
                </td>
                <td class="align-middle">
                    <span><xsl:value-of select="@email"/></span>
                </td>
                <td class="align-middle">
                    <span><xsl:value-of select="@type"/></span>
                </td>
                <td class="align-middle">
                   <span><xsl:value-of select="title"/></span>
                </td>
                <td class="align-middle text-secondary">
                   <span><xsl:value-of select="@registered"/></span>
                </td>
                <td class="align-middle">
                    <xsl:copy-of select="$status"/> 
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
                    <a data-help="Сортировка по ID" href="{moderator/@site_url}&amp;sort=id">ID</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Login" href="{moderator/@site_url}&amp;sort=login">Логин (login)</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Имени" href="{moderator/@site_url}&amp;sort=display_name">Отображаемое имя (display_name)</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Почте" href="{moderator/@site_url}&amp;sort=email">email</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Группе" href="{moderator/@site_url}&amp;sort=type">Группа(type)</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Имени группы" href="{moderator/@site_url}&amp;sort=title">Имя группы(title)</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Дате" href="{moderator/@site_url}&amp;sort=registered">Дата регистрации</a>
                </th>
                <th scope="col" class="align-middle">
                    <a data-help="Сортировка по Статусу" href="{moderator/@site_url}&amp;sort=status">Включен ли (status)</a>
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