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
    
    <xsl:param name="user" select="//ads/user" />
    <xsl:param name="detail" select="//ads/detail" />
    <xsl:param name="file" select="//ads/file" />
    
    <xsl:template match="/">
        <style>
            .ads table td{
                vertical-align:top;
                padding:10px;
            }
            .ads .btn-danger{
                margin-right:20px;
            }
        </style>
        <div class="ads">
            <form class="container" action="{ad/@action}" method="post" autocomplete="off">
                <input type="hidden" name="session_id" value="{//ads/@session_id}" />
                <input type="hidden" name="id" value="{$user/@id}" />
                <table>
                    <tr>
                        <xsl:choose>
                            <xsl:when test="$user/@legal = 1">
                                <xsl:call-template name="legal" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="nolegal" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </tr>
                </table>
                <div class="input_label">
                    <span class="string pt_sans">Причина отказа</span>
                </div>
                <div class="form-group control">
                    <textarea name="note" class="form-control p_14" rows="5" active-role="note"><xsl:value-of select="ad/note" /></textarea>
                </div>
                <div id="submit_button" class="d-flex justify-content-start">
                    <div>
                        <button name="submit" value="2" class="btn btn-danger" type="submit">Отказать</button>
                    </div>
                    <div>
                        <button name="submit" value="1" class="btn btn-success" type="submit">Подтвердить</button>
                    </div>
                </div>
            </form>
        </div>
    </xsl:template>
    
    <xsl:template name="legal">
        <td style="border-right:1px solid #666;">
            <h3>Информация о пользователе</h3>
            <div>
                <b>Имя компании: </b><span><xsl:value-of select="$user/@name" /></span>
            </div>
            <div>
                <b>Дата регистрации: </b><span><xsl:value-of select="$user/@birthday" /></span>
            </div>
        </td>
        <td>
            <h3>Присланные данные</h3>
            <div>
                <b>Номер: </b><span><xsl:value-of select="$detail/number" /></span>
            </div>
            <div>
                <b>Сканы:</b>
            </div>
            <div>
                <xsl:apply-templates select="//ads/file" />
            </div>
        </td>
    </xsl:template>
    <xsl:template name="nolegal">
        <td style="border-right:1px solid #666;">
            <h3>Информация о пользователе</h3>
            <div>
                <b>Имя: </b><span><xsl:value-of select="$user/@name" /></span>
            </div>
            <div>
                <b>День рождения: </b><span><xsl:value-of select="$user/@birthday" /></span>
            </div>
        </td>
        <td>
            <h3>Присланные данные</h3>
            <div>
                <b>ФИО: </b><span><xsl:value-of select="$user/@name" /></span>
            </div>
            <div>
                <b>День рождения: </b><span><xsl:value-of select="$user/@birthday" /></span>
            </div>
            <div>
                <b>Серия паспорта: </b><span><xsl:value-of select="$detail/serial" /></span>
            </div>
            <div>
                <b>Номер: </b><span><xsl:value-of select="$detail/number" /></span>
            </div>
            <div>
                <b>Дата регистрации: </b><span><xsl:value-of select="$detail/date" /></span>
            </div>
            <div>
                <b>Кем выдан: </b><span><xsl:value-of select="$detail/issued" /></span>
            </div>
            <div>
                <b>Сканы:</b>
            </div>
            <div>
                <xsl:apply-templates select="//ads/file" />
            </div>
        </td>
    </xsl:template>
    <xsl:template match="file">
        <div>
            <a href="/{$detail/path}/{.}" target="_blank"><xsl:value-of select="." /></a>
        </div>
    </xsl:template>
    
</xsl:stylesheet>