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
    <xsl:param name="root" select="header/@root" />
    <xsl:param name="site" select="header/@site" />
    <xsl:param name="action" select="header/@action" />
    
    <xsl:template match="/">
        <header>
            <div id="header_top" class="container-fluid white">
                <div class="container">
                    <div id="logo" class="for_mob">
                        <div class="logo_box">
                            <div class="overlay"></div>
                            <a href="/"></a>
                        </div>
                        <div class="menu_icon_mobile">
                            <svg viewBox="0 0 24 24" class="menu_icon">
                                <g class="style-scope yt-icon">
                                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" class="style-scope yt-icon"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <nav>
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
                        <div id="inform_panel" class="d-flex align-items-center">
                            <div id="notification_menu" class="popup_menu" data-count="{header/@notification}">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="popup_top popup_box d-flex align-items-center">
                                    <div class="text_block">
                                        <span>Уведомление</span>
                                        <span class="notification_count"></span>
                                    </div>
                                </div>
                                <div class="popup_content popup_box"></div>
                            </div>
                            
                            <!-- Создаем переменную с дополнительным классом  для отображения уведомлений-->
                            <xsl:variable name="notification">
                                <xsl:if test="header/@notification != 0">active</xsl:if>
                            </xsl:variable>
                            <div id="notification" class="notification navigation-link position-relative popup_activator {$notification}">
                                <div class="arrow_box">
                                    <div class="arrow"></div>
                                    <div class="arrow bg"></div>
                                </div>
                                <span> </span>
                            </div>
                            <div class="position-relative popup_activator message" id="message">
                                <div class="message_icon"></div>
                                <xsl:if test="header/@message != 0">
                                    <span class="count_box">
                                        <span class="string">
                                            <xsl:value-of select='header/@message' />
                                        </span>
                                        <span class="count"></span>
                                    </span>
                                </xsl:if>
                            </div>
                            
                            <div class="popup_menu" id="pay_menu">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="popup_top popup_box d-flex align-items-center">
                                    <div class="text_block">
                                        <span>Финансы</span>
                                    </div>
                                </div>
                                <div class="popup_content popup_box">
                                    
                                </div>
                            </div>
                            <div class="position-relative position-relative popup_activator" id="pay">
                                <div class="navigation-link">
                                    <span id="coin_count"><xsl:value-of select='floor(header/@seor)' /></span>
                                    <span class="coin_sing"></span>
                                </div>
                                <div class="arrow"></div>
                                <div class="arrow bg"></div>
                            </div>
                            <div class="popup_menu" id="client_menu">
                                <div class="popup_top popup_box d-flex align-items-center">
                                    <div class="text_block d-flex">
                                        <div class="d-flex no-padding">
                                            <a href="/account" class="overflow">
                                                <img class="logo_image" src="{header/logo/@logo_big}" />
                                            </a>
                                        </div>
                                        <div class="inform_box d-flex align-items-center">
                                            <xsl:value-of select="header/@email" />
                                        </div>
                                    </div>
                                </div>
                                <div class="popup_content popup_box">
                                    <xsl:value-of select="header/menu" disable-output-escaping="yes" />
                                </div>
                                <div class="popup_footer popup_box d-flex align-items-center">
                                    <a class="text-center" href="/logout">Выйти</a>
                                </div>
                            </div>
                            <div class="position-relative popup_activator" id="client">
                                <div id="client_logo" class="navigation-link">
                                    <div class="overflow">
                                        <img class="logo_image" src="{header/logo/@logo_small}" />
                                    </div>
                                </div>
                                <div class="arrow a_client"></div>
                                <div class="arrow bg"></div>
                            </div>
                        </div>
                    </nav>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="menu_mobile" class="d-none">
                <div class="bottom_line padding-bottom d-flex align-items-center justify-content-center">
                    <div class="menu_icon_mobile">
                        <svg viewBox="0 0 24 24" class="menu_icon">
                            <g class="style-scope yt-icon">
                                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" class="style-scope yt-icon"></path>
                            </g>
                        </svg>
                    </div>
                    <div class="logo">
                        <div>
                            <div class="overlay"></div>
                            <a href="/"></a>
                        </div>
                    </div>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <a href="{$site}">Главная</a>
                    </li>
                    <li class="list-group-item">
                        <a href="{$site}/ads">Вакансии</a>
                    </li>
                    <li class="list-group-item">
                        <a href="{$site}/workers">Соискатели</a>
                    </li>
                    <li class="list-group-item">
                        <a href="{$site}/visas">Визы и страны</a>
                    </li>
                </ul>
            </div>
            <!-- Модальное окно -->
            <div class="modal fade" id="modal_window" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Промокод</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&#215;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                           
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn_green modal_submit">Применить</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                var $modal_window = $("#modal_window");
                $modal_window.get(0).oldClass = $modal_window.attr("class");
                
                $modal_window.on('hide.bs.modal', function (e) {
                    $(e.target).find(".modal-footer button").off();
                    $(e.target).attr("class",$(e.target).get(0).oldClass);
                });
                
                header.init();
            </script>
        </header>
    </xsl:template>
</xsl:stylesheet>