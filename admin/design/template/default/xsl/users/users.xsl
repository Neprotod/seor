<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <!--
        Шаблон корневого элемента
    -->
    <xsl:param name="site" select="users/@site" />
    <xsl:param name="url" select="users/@url" />
    <xsl:template match="/">
        <section id="users" class="container-fluid big_container white view">
            <div class="container">
                <form class="user" action="{users/@site}/admin/users/inside" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <input type="text" name="email" value="" />
                    <input type="submit" value="Зайти в аккаунт" />
                </form>
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <table class="table">
                        <xsl:apply-templates select="users/user" />
                    </table>
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="user">
        <tr>
            <td>
                <span class="type">Email</span>
                <xsl:value-of select="@email" />
            </td>
            <td>
                <span class="type">Имя</span>
                <xsl:value-of select="@name" />
            </td>
            <td>
                <xsl:value-of select="@registered" />
            </td>
            <td>
                <span class="type">Тип</span>
                <xsl:choose>
                    <xsl:when test="@employer = 1">
                        Работодатель
                    </xsl:when>
                    <xsl:otherwise>
                        Соискатель
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="@status = 0">
                        <button type="submit" name="activation" value="{@id}" class="btn btn-secondary">Активировать</button>
                    </xsl:when>
                    <xsl:otherwise>
                        <button type="submit" name="deactivation" value="{@id}" class="btn btn-secondary">Деактивировать</button>
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <button type="submit" name="drop" value="{@id}" class="btn btn-danger">Удалить</button>
            </td>
        </tr>
        <tr>
            <td>
                <a class="btn btn-success" href="/admin/users/user/{@id}">Редактировать</a>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>