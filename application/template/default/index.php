<!DOCTYPE HTML>
<html lang="<?=Registry::i()->user_language["code"]?>">
<head>
    <meta charset="utf-8" />

    <link href="/media/css/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="/media/css/error.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700|Roboto:300,400,500,700" rel="stylesheet">
    
    <link rel="shortcut icon" href="/media/img/favicon.ico" type="image/x-icon">
    
    <script src="/media/js/jquery-3.3.1.min.js"></script>
    <script src="/media/js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/media/js/error.js" type="text/javascript"></script>
    <script src="/media/js/cookie.js" type="text/javascript"></script>
    <?=Module::load("ajax","script");?>
    <script>
        // Определяем TIMEZONE пользователя
        if(!docCookies.hasItem("UTC")){
            var time_string = new Date().toString().match(/\+[0-9]{4}/);
            time_string = new String(time_string).replace(/(\+[0-9]{2})([0-9]{2})/,"$1:$2");
            docCookies.setItem("UTC",time_string,86400, null, null, true);
        }
    </script>
    
    <?=$head?>
    <link href="/application/template/default/css/media.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?=$header?>
    <?=$system_error?>
    <?=$content?>
    <footer>
        <div id="footer_top" class="container-fluid">
            <div class="container">
                <div class="row">
                    <div class="col d-none">
                        <div class="center_block">
                            <h5 class="footer_h">Помощь</h5>
                            <ul class="footer_ul">
                                <li>
                                    <a href="#">Вопросы и ответы</a>
                                </li>
                                <li>
                                    <a href="#">Служба поддержки</a>
                                </li>
                                <li >
                                    <a href="#">Обратная связь</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col">
                        <div class="center_block">
                            <h5 class="footer_h">О нас</h5>
                            <ul class="footer_ul">
                                <li>
                                    <a href="/company">О Компании</a>
                                </li>
                                <li>
                                    <a href="/rules">Правила</a>
                                </li>
                                <li class="d-none">
                                    <a href="#">Сотрудничество</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col">
                        <div class="center_block">
                            <h5 class="footer_h">Контакты</h5>
                            <ul class="footer_ul">
                                <li>
                                    <span class="text_grey"><span>Email:</span> support@seor.ua</span>
                                </li>
                                <li>
                                    <span><span>Адрес:</span> 44a, проспект Володимира Маяковського, Київ, 02000</span>
                                </li>
                                <li class="padding_top">
                                    <span> +38 067 398 11 16</span>
                                    <span> +38 050 279 85 88</span>
                                    <span> +38 093 465 50 93</span>
                                </li>
                                <li class="social_box">
                                    <div class="row">
                                        <div class="col-auto">
                                            <a href="#" id="facebook" class="social_icon">
                                                <div class="box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                        <path d="M16.41 8.65h-3v-2a.8.8 0 0 1 .84-.91h2.12V2.51h-2.93a3.7 3.7 0 0 0-4 4v2.14H7.59V12h1.87v9.5h3.95V12h2.66l.34-3.35z"></path>
                                                    </svg>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" id="twitter" class="social_icon">
                                                <div class="box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                        <path d="M8.192 21c-2.28 0-4.404-.668-6.192-1.815 2.144.252 4.282-.342 5.98-1.672-1.768-.034-3.26-1.202-3.772-2.806.633.12 1.256.085 1.823-.07-1.942-.39-3.282-2.14-3.24-4.01.545.302 1.17.484 1.83.505C2.822 9.93 2.313 7.555 3.37 5.738c1.992 2.444 4.97 4.053 8.326 4.22C11.106 7.434 13.024 5 15.63 5c1.163 0 2.212.49 2.95 1.276.92-.18 1.785-.517 2.565-.98-.3.944-.942 1.736-1.775 2.235.817-.097 1.596-.314 2.32-.635-.542.807-1.228 1.52-2.016 2.088C19.93 14.665 15.694 21 8.192 21z"></path>
                                                    </svg>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" id="google" class="social_icon">
                                                <div class="box">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                        <path d="M24 12.5h-3v3h-2v-3h-3v-2h3v-3h2v3h3M7 18.5c-3.87 0-7-3.13-7-7s3.13-7 7-7c1.89 0 3.47.69 4.69 1.83l-1.9 1.83C9.27 7.66 8.36 7.08 7 7.08c-2.39 0-4.34 1.98-4.34 4.42S4.61 15.92 7 15.92c2.77 0 3.81-1.99 3.97-3.02H7v-2.4h6.61c.06.35.11.702.11 1.16 0 4-2.68 6.84-6.72 6.84z"></path>
                                                    </svg>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col no-width">
                        <div class="center_block">
                            <h5 class="footer_h">Наше месторасположение</h5>
                            <div id="map">
                                
                            </div>
                            <script>
                                $("#map").one("mouseover", function(){
                                    $(this).html('<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d10147.806478334647!2d30.618485!3d50.516467!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40d4d0fd57e9ecaf%3A0x52723727026f2d1e!2z0L_RgNC-0YHQv9C10LrRgiDQktC-0LvQvtC00LjQvNC40YDQsCDQnNCw0Y_QutC-0LLRgdGM0LrQvtCz0L4sIDQ0LCDQmtC40ZfQsiwgMDIwMDA!5e0!3m2!1sru!2sua!4v1526292217713" frameborder="0" style="border:0" allowfullscreen></iframe>');
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <div id="language" class="">
                    <h5 class="footer_h">Языки</h5>
                    <div>
                        <ul class="footer_ul">
                            <li class="d-none">
                                <a href="#">
                                    <span id="lang_1" class="lang_box"><span class="icon"></span>Український</span>
                                </a>
                            </li>
                            <li class="active">
                                <a href="#">
                                    <span id="lang_2" class="lang_box"><span class="icon"></span>Русский</span>
                                </a>
                            </li>
                            <li class="d-none">
                                <a href="#">
                                    <span id="lang_3" class="lang_box"><span class="icon"></span>English</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer_bottom" class="container-fluid">
            <div class="rainbow">
                <div class="rainbow_color_1"></div>
                <div class="rainbow_color_2"></div>
            </div>
        </div>
    </footer>
    <div class="backdrop"></div>
    <script>
        errors.init();
        // Активируем меню вместо logo
        if(header){
            header.mobile_menu();
        }
    </script>
</body>
</html>