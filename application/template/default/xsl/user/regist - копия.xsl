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
    <xsl:template match="/">
        <section id="register" class="container-fluid big_container grey">
            <div class="container">
                <form action="{user/@site}{user/@action}" method="post">
                    <div id="register_input">
                        <h3>Второй шаг регистрации.</h3>
                        <div class="form-group control">
                            <!--
                            <select id="select_type" class="form-control">
                                <option value=""></option>
                                <option value="0">Соискатель</option>
                                <option value="1">Работодатель</option>
                            </select>
                            -->
                            
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </xsl:template>
</xsl:stylesheet>