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
    <xsl:param name="root" select="index/@root" />
    <xsl:param name="site" select="index/@site" />
    <xsl:template match="/">
        <header>
            <div id="header_top" class="container-fluid white">
                <div class="container">
                    <div id="logo">
                        <div>
                            <div class="overlay"></div>
                            <a href="/"></a>
                        </div>
                    </div>
                    <nav>
                        <button id="menu_button" class="btn btn-success collapsed btn_orange" type="button" data-toggle="collapse" data-target="#collapse_mobail_menu" aria-expanded="false" aria-controls="collapseOne">
                            <div class="menu_line"></div>
                        </button>
                        <ul id="menu" class="left for_hidden">
                            <li>
                                <a href="{$site}/ads">Вакансии</a>
                            </li>
                            <li>
                                <a href="{$site}/workers">Соискатели</a>
                            </li>
                            <li>
                                <a href="{$site}/visas">Визы и страны</a>
                            </li>
                        </ul>
                        <div id="register_block" class="right for_hidden">
                            <a class="string" href="{$site}/account">Вход</a>
                            <span class="small">или</span>
                            <a class="btn btn-success last string" href="{$site}/registr">Регистрация</a>
                        </div>
                    </nav>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="collapse_mobail_box" class="container-fluid">
                <div id="collapse_mobail_menu" class="collapse" aria-labelledby="headingOne">
                    <div class="container">
                        <div id="mobail_menu">
                            <ul class="">
                                <li>
                                    <a href="{$site}/ads">Вакансии</a>
                                </li>
                                <li>
                                    <a href="{$site}/workers">Соискатели</a>
                                </li>
                                <li>
                                    <a href="{$site}/visas">Визы и страны</a>
                                </li>
                            </ul>
                            <div class="button_box">
                                <a class="string" href="{$site}/account">Вход</a>
                                <a class="btn btn-success btn_orange last string" href="{$site}/registr">Регистрация</a>
                            </div>
                        </div>            
                    </div>            
                </div>
            </div>
            <div id="head_slider" class="container-fluid big_container">
                <div class="absolute">
                    <div class="container">
                        <h1>Международная биржа труда и образования за границей.</h1>
                        <h2 class="h4 text-left">Мы поможем вам найти квалифицированного сотрудника или хорошую работу за границей</h2>
                        <div class="button_block row justify-content-md-center">
                            <div class="col-5">
                                <a class="btn btn-success string" href="{$site}/registr?employer=0">
                                    <span>Я Соискатель</span>
                                </a>
                            </div>
                            <div class="col-5">
                                <a class="btn btn-success last string" href="{$site}/registr?employer=1">
                                    <span>Я Работодатель</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel slide" data-ride="carousel">
                    <div class="shadow"></div>
                    <ol class="carousel-indicators">
                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img class="d-block w-100" src="{$root}/img/slide/DSC_5705.jpg" alt="First slide" />
                        </div>
                        <div class="carousel-item">
                            <img class="d-block w-100" src="{$root}/img/slide/photodune-13159493-close-up-of-womans-hand-using-laptop-with-notebook-l.jpg" />
                        </div>
                        <div class="carousel-item">
                            <img class="d-block w-100" src="{$root}/img/slide/product-manager-sales.jpg" />
                        </div>
                    </div>
                </div>
                <div class="rainbow">
                    <div class="rainbow_color_1"></div>
                    <div class="rainbow_color_2"></div>
                </div>
            </div>
        </header>
    </xsl:template>
</xsl:stylesheet>