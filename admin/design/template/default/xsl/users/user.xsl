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
    <xsl:param name="fields" select="users/fields" />
    <xsl:param name="user" select="users/user" />
    <xsl:param name="site" select="users/@site" />
    <xsl:param name="url" select="users/@url" />
    <xsl:template match="/">
        <section id="users" class="container-fluid big_container white view">
            <div class="container">
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <table>
                        <tr>
                            <td>Имя:</td> 
                            <td>
                                <input type="text" value="{$user/@name}" name="name" />
                            </td>
                        </tr>
                        <tr class="margin-top">
                            <td>Дата рождения:</td> 
                            <td>
                                <input type="text" value="{$user/@birthday}" name="birthday" />
                            </td>
                        </tr>
                        <xsl:if test="$fields/site">
                            <tr class="margin-top">
                                <td>Сайт:</td> 
                                <td>
                                    <input type="text" value="{$fields/site}" name="fields[{$fields/site/@id}][var]" />
                                </td>
                            </tr>
                        </xsl:if>
                        <tr class="margin-top">
                            <td>Специализация:</td> 
                            <td>
                                <input type="text" value="{$fields/activity}" name="fields[{$fields/activity/@id}][var]" />
                            </td>
                        </tr>
                        <tr class="margin-top">
                            <td colspan="2">
                                <textarea style="width:100%;" name="fields[{$fields/description/@id}][text]"><xsl:value-of select="$fields/description" /></textarea>
                            </td>
                        </tr> 
                    </table>
                    <input type="submit" value="Сохранить" />
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>