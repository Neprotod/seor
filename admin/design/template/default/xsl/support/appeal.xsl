<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:fn="https://seor.ua/functions"
    extension-element-prefixes="fn">
        <xsl:output 
        method="xml" 
        omit-xml-declaration="yes" 
        indent="yes" 
        doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
        doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"/>
    
    <xsl:param name="user_name">
        <xsl:choose>
            <xsl:when test="normalize-space(support/user/@name)">
                <xsl:value-of select="support/user/@name" />
            </xsl:when>
            <xsl:otherwise>
                Пользователь
            </xsl:otherwise>
        </xsl:choose>
    </xsl:param>
    <xsl:template match="/">
        <section id="support" class="container-fluid big_container white view">
            <form class="container read" action="{support/@site}{support/@action}" method="post" autocomplete="off">
                <input type="hidden" name="session_id" value="{support/@session_id}" />
                <div class="card">
                    <h5 class="card-header align-items-center">
                        <div class="string">
                            <span class="margin-right-small small">
                                Обращение №<xsl:value-of select="support/appeal/@id" />:
                            </span> 
                        </div>
                        <div class="string">
                            <span>
                                <xsl:value-of select="support/appeal/@title" />
                            </span>
                        </div>
                    </h5>
                    <div class="card-body">
                        <xsl:apply-templates select="support/message" />
                        <div class="flex-column margin">
                            <div class="form-group control position-relative">
                                <textarea name="message" class="form-control" id="company_textarea" rows="5" required="required"></textarea>
                            </div>
                        </div>
                        <div class="margin">
                            <button href="{account/@site}{account/@url}/create" class="btn btn_green user_button ml-auto" type="submit">Отправить сообщение</button>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </xsl:template>
    
    
    <xsl:template match="message">
        <xsl:variable name="ask">
            <xsl:choose>
                <xsl:when test="normalize-space(@id_admin_user)">
                    Ответ службы поддержки
                </xsl:when>
                <xsl:otherwise>
                    Вопрос в службу поддержки
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="name">
            <xsl:choose>
                <xsl:when test="normalize-space(@id_admin_user)">
                    Сотрудник поддержки
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$user_name" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:variable name="admin">
            <xsl:if test="normalize-space(@id_admin_user)">admin</xsl:if>
        </xsl:variable>
        
        <div class="appeal {$admin}">
            <div class="info_box d-flex">
                <div class="logo_box margin-right">
                   
                </div>
                <div class="info_box w-100">
                    <div class="d-flex w-100 margin-bottom-small">
                        <!-- От кого -->
                        <div class="user_name w-100">
                            <div>
                                <xsl:value-of select="$name" />
                            </div>
                            <div class="small">
                                <xsl:value-of select="$ask" />
                            </div>
                        </div>
                        <div class="time_box">
                            <div class="string"><xsl:value-of select="@time" /></div>
                        </div>
                    </div>
                    <div class="info_box">
                        <xsl:value-of select="." />
                    </div>
                </div>
            </div>
        </div>
    </xsl:template>
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>