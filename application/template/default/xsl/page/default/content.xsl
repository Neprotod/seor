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
    <xsl:param name="root" select="page/@root" />
    <xsl:param name="site" select="page/@site" />
    <xsl:template match="/">
        <section id="specializations" class="container-fluid big_container white">
            <div class="container">
                <h2 class="padding_big">Разделы и специализации</h2>
                <div id="signs_block">
                    <div class="left float_col">
                        <ul>
                            <li id="sign_1" class="specialization">
                                <a href="{$site}/ads?specialization=1">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Сельское хозяйство</span>
                                </a>
                            </li>
                            <li id="sign_2" class="specialization">
                                <a href="{$site}/ads?specialization=2">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Строительство</span>
                                </a>
                            </li>
                            <li id="sign_3" class="specialization">
                                <a href="{$site}/ads?specialization=3">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Обслуживание</span>
                                </a>
                            </li>
                            <li id="sign_4" class="specialization">
                                <a href="{$site}/ads?specialization=4">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Транспорт водители</span>
                                </a>
                            </li>
                            <li id="sign_5" class="specialization">
                                <a href="{$site}/ads?specialization=5">
                                    <div class="img">
                                        <div class="sing img_big"></div>
                                    </div>
                                    <span class="text">Производство</span>
                                </a>
                            </li>
                            <li id="sign_6" class="specialization">
                                <a href="{$site}/ads?specialization=6">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Секретариат</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="left float_col">
                        <ul>
                            <li id="sign_7" class="specialization">
                                <a href="{$site}/ads?specialization=7">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Отели Рестораны</span>
                                </a>
                            </li>
                            <li id="sign_8" class="specialization">
                                <a href="{$site}/ads?specialization=8">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Безопасность</span>
                                </a>
                            </li>
                            <li id="sign_9" class="specialization">
                                <a href="{$site}/ads?specialization=9">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Красота спорт</span>
                                </a>
                            </li>
                            <li id="sign_10" class="specialization">
                                <a href="{$site}/ads?specialization=10">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Полиграфия</span>
                                </a>
                            </li>
                            <li id="sign_11" class="specialization">
                                <a href="{$site}/ads?specialization=11">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Для студентов</span>
                                </a>
                            </li>
                            <li id="sign_12" class="specialization">
                                <a href="{$site}/ads?specialization=12">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Топ-менеджмент</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="left float_col">
                        <ul>
                            <li id="sign_13" class="specialization">
                                <a href="{$site}/ads?specialization=13">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Работа на дому</span>
                                </a>
                            </li>
                            <li id="sign_14" class="specialization">
                                <a href="{$site}/ads?specialization=14">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">IT</span>
                                </a>
                            </li>
                            <li id="sign_15" class="specialization">
                                <a href="{$site}/ads?specialization=15">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Руководство</span>
                                </a>
                            </li>
                            <li id="sign_16" class="specialization">
                                <a href="{$site}/ads?specialization=16">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Бухгалтерия</span>
                                </a>
                            </li>
                            <li id="sign_17" class="specialization">
                                <a href="{$site}/ads?specialization=17">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Дизайн</span>
                                </a>
                            </li>
                            <li id="sign_18" class="specialization">
                                <a href="{$site}/ads?specialization=18">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Телекоммуникации</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="left float_col">
                        <ul>
                            <li id="sign_19" class="specialization">
                                <a href="{$site}/ads?specialization=19">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Шоу-бизнес</span>
                                </a>
                            </li>
                            <li id="sign_20" class="specialization">
                                <a href="{$site}/ads?specialization=20">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Логистика склады</span>
                                </a>
                            </li>
                            <li id="sign_21" class="specialization">
                                <a href="{$site}/ads?specialization=21">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Маркетинг</span>
                                </a>
                            </li>
                            <li id="sign_22" class="specialization">
                                <a href="{$site}/ads?specialization=22">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Медицина</span>
                                </a>
                            </li>
                            <li id="sign_23" class="specialization">
                                <a href="{$site}/ads?specialization=23">
                                    <div class="img">
                                        <div class="sing"></div>
                                    </div>
                                    <span class="text">Образование</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </section>
        <section id="top_vacancies" class="container-fluid big_container grey">
            <div class="container">
                <h2>Топ Вакансий</h2>
                <h3 class="padding_big">Лучшие вакансии за неделю:</h3>
                <div class="row">
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/1.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/2.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/3.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/3.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/1.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card_box">
                            <a href="#" class="vacancies row">
                                <div class="img col">
                                    <img src="{$root}/img/2.png" />
                                </div>
                                <div class="info col">
                                    <div class="bold">Требуется швея первого разряда</div>
                                    <div class="string">Описание товара, краткое чтоб человек мог хоть как-то ознакомится с объявле...</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <a href="#" class="table_box center_box">
                    <h4 class="navigation-link">Все объявления</h4>
                </a>
            </div>
        </section>
        <section id="guarantees" class="container-fluid big_container white">
            <div class="container">
                <h3 class="padding_big">Гарантии качества</h3>
                <div class="row justify-content-md-center">
                    <div class="col-3">
                        <div class="table_box margin_center">
                            <div id="shild" class="img"></div>
                            <div class="big text-center">Щит</div>
                            <div class="string text-center">Знак щита у названия компании гарантирует то что компания прошла проверку и является надежной.</div>
                        </div>
                    </div>
                    <div class="col-3 for_hidden">
                        <div class="table_box margin_center">
                            <div class="vertical_line"></div>
                            <div id="or_element">
                                <span>или</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div id="star" class="img"></div>
                        <div class="big text-center">Топ Star</div>
                        <div class="string text-center">Исполнитель или Компания входит в рейтинг Топ-100 лучших на SEOR</div>
                    </div>
                </div>
            </div>
        </section>
    </xsl:template>
    
    <xsl:template match="*"></xsl:template>
</xsl:stylesheet>