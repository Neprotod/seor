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
    
    <xsl:param name="back" select="account/@back" />
    
    <xsl:template match="/">
        <section id="management_tool" class="container-fluid big_container grey">
            <div class="container d-flex align-items-center">
                <a href="{$back}" class="margin-right">К обращениям</a>
                <div class="w-100 d-flex justify-content-center">
                    <xsl:value-of select="account/technical/error" disable-output-escaping="yes" />
                </div>
            </div>
        </section>
        <section id="support" class="container-fluid big_container white view">
            <form class="container appeal" action="{account/@site}{account/@action}" method="post" autocomplete="off">
                <div class="card">
                    <h5 class="card-header align-items-center">
                        <div class="string">Создание обращения</div>
                        <div class="small">Обращение может рассматриватся несколько часов.</div>
                    </h5>
                    <div class="card-body">
                        <div class="margin">
                            <h6>Тема обращения</h6>
                            <div class="form-group control position-relative">
                                <input name="title" type="text" class="form-control" value="" active-role="field_specialty" required="required" />
                            </div>
                        </div>
                        <div class="flex-column margin">
                            <h6>Пожалуйста, укажите детали вашего обращения</h6>
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
    
    <xsl:template match="*">
        
    </xsl:template>
</xsl:stylesheet>