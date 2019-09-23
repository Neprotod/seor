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
    <xsl:param name="promo" select="users/promo" />
    <xsl:param name="site" select="users/@site" />
    <xsl:param name="url" select="users/@url" />
    
    <xsl:template match="/">
        <section id="users" class="container-fluid big_container white view">
            <div class="container">
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <input type="hidden" name="create" value="1" />
                    <div class="">
                        <div class="row">
                            <div class="col">
                                <input type="text" name="days" class="form-control" placeholder="Дней" />
                            </div>
                            <div class="col">
                                <input type="text" name="seor" class="form-control" placeholder="Количество seor" />
                            </div>
                            <div class="col">
                                <input type="text" name="clicks" class="form-control" placeholder="Бесплатные клики" />
                            </div>
                            <div class="col">
                                <input type="text" name="ads" class="form-control" placeholder="Бесплатные вакансии" />
                            </div>
                            <div class="col">
                                <input type="text" name="time" class="form-control" placeholder="2019-00-00" />
                                <div>Дата окончания промокода, если не задать, будет вечным.</div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-2">Одноразвоый</div>
                                <div class="col-sm-10">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="once" value="1" />
                                    </div>
                                </div>
                        </div>
                        <input type="submit" value="Сгенерировать промокод" /> 
                    </div>
                </form>
                <form class="user" action="{users/@site}{users/@action}" method="post" autocomplete="off">
                    <input type="hidden" name="session_id" value="{users/@session_id}" />
                    <table class="table">
                            <thead>
                            <tr>
                                <th>Промокод</th>
                                <th>Дата окончания</th>
                                <th>Seor</th>
                                <th>Дни</th>
                                <th>Клики</th>
                                <th>Вакансии</th>
                                <th>Многоразовый?</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:apply-templates select="users/promo" />
                        </tbody>
                    </table>
                </form>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="promo">
        <tr>
            <td>
                <xsl:value-of select="@promo" />
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="normalize-space(@time)">
                        <xsl:value-of select="@time" />
                    </xsl:when>
                    <xsl:otherwise>
                        Вечный
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <xsl:value-of select="@seor" />
            </td>
            <td>
                <xsl:value-of select="@days" />
            </td>
            <td>
                <xsl:value-of select="@clicks" />
            </td>
            <td>
                <xsl:value-of select="@ads" />
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="@once = 1">
                        Нет
                    </xsl:when>
                    <xsl:otherwise>
                        Да
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <button type="submit" class="btn btn-danger" name="drop" value="{@id}">Удалить</button>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>