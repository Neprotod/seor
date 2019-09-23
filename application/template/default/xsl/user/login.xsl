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
        <link href="{user/@root}/css/user/login.css" rel="stylesheet" />
        <xsl:call-template name="login">
            <xsl:with-param name="title" select="user/@title" />
            <xsl:with-param name="regist" select="user/@regist" />
        </xsl:call-template>
    </xsl:template>
    <xsl:template name="login">
        <xsl:param name="title" select="'Войти'" />
        <xsl:param name="regist" select="'0'" />
        <section id="register" class="container-fluid big_container grey">
            <div class="container">
                <xsl:value-of select="user/error" disable-output-escaping="yes" />
                <form action="{user/@site}{user/@action}" method="post">
                    <div id="register_img">
                        <div id="seor_logo_registr"></div>
                    </div>
                    <!--
                    <div id="register_social">
                        <span class="string">Вход через:</span>
                        <a class="btn btn_facebook" href="#">facebook</a>
                        <a class="btn btn_twitter" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M8.192 21c-2.28 0-4.404-.668-6.192-1.815 2.144.252 4.282-.342 5.98-1.672-1.768-.034-3.26-1.202-3.772-2.806.633.12 1.256.085 1.823-.07-1.942-.39-3.282-2.14-3.24-4.01.545.302 1.17.484 1.83.505C2.822 9.93 2.313 7.555 3.37 5.738c1.992 2.444 4.97 4.053 8.326 4.22C11.106 7.434 13.024 5 15.63 5c1.163 0 2.212.49 2.95 1.276.92-.18 1.785-.517 2.565-.98-.3.944-.942 1.736-1.775 2.235.817-.097 1.596-.314 2.32-.635-.542.807-1.228 1.52-2.016 2.088C19.93 14.665 15.694 21 8.192 21z"></path></svg>twitter
                        </a>
                    </div>
                    <div id="register_and_line">
                        <div class="horizontal_line"></div>
                        <div>
                            <span>или</span>
                        </div>
                    </div>
                    -->
                    <div id="register_input">
                        <div class="form-group control">
                            <input name="email" type="email" class="form-control" placeholder="Enter email" value="{user/@email}" active-role="email" required="required" />
                        </div>
                        <div class="form-group control">
                            <input name="password" type="password" class="form-control" placeholder="Password" value="{user/@password}" active-role="password" required="required" />
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success btn_orange last string" type="submit">
                                <xsl:value-of select="$title" />
                            </button>
                        </div>
                    </div>
                </form>
                <xsl:choose>
                    <xsl:when test="$regist = 1">
                        <div id="register_politic">
                            <div class="small">
                                Нажимая на кнопку, я даю согласие на обработку персональных данных, соглашаюсь с <a href="#">политикой конфиденциальности</a> и <a href="#">правилами сайта</a>.
                            </div>
                            <div class="big">
                               <a href="{user/@site}/account"> У меня уже есть аккаунт</a>
                            </div>
                        </div>
                    </xsl:when>
                    <xsl:otherwise>
                        <div id="register_politic">
                            <div class="big">
                               <a href="{user/@site}/registr">Зарегистрироваться</a>
                            </div>
                        </div>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </section>
    </xsl:template>
</xsl:stylesheet>