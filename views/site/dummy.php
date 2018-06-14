<?php

/* @var $this yii\web\View */

$this->registerCssFile('@web/css/site.css');

?>

<style>
    .imgHolder div {
        float: left;
        margin-right: 20px;
    }

    .blokForImg {
        width: 650px;
        margin: 0 auto;
        overflow: hidden;
    }

    .site-dummy {
        padding-top: 15px;
        width: 800px;
        position: relative;
        margin: 0 auto;
        display: block;
    }

    .site-dummy h2 {
        text-align: center;
        font-family: "ProximaNova-Bold", sans-serif !important;
        font-weight: normal !important;
        font-size: 140% !important;
    }

    .site-dummy h2 + p {
        text-align: center;
        font-family: "ProximaNova-Light", sans-serif !important;
        font-weight: normal !important;
        font-size: 100% !important;
    }

    .site-dummy .imgHolder div {
        margin: 0 auto;
        width: 120px;
    }

    .site-dummy .imgHolder div a {
        display: inline;
    }

    .site-dummy .imgHolder div img {
        display: block;
        margin: 0 auto;
        width: 85px;
    }

    .site-dummy .imgHolder div span {
        display: block;
        text-align: center;
        font-family: "ProximaNova-Light", sans-serif !important;
        font-weight: normal !important;
        font-size: 100% !important;
    }

    .site-dummy ol li {
        font-size: 100% !important;
    }

</style>

<div id="dummyPage">
    <div class="site-dummy">

        <h2>Внимание! Вы используете устаревший браузер.</h2>

        <p>Обновите браузер или установите один из предложенных ниже:</p>

        <div class="blokForImg">
            <div class="imgHolder">
                <div>
                    <a href="https://www.google.ru/chrome/browser/desktop/index.html" target="_blank">
                        <img src="/img/browsers/browser-chrome.png" alt="Chrome"/>
                    </a>
                    <a href="https://www.google.ru/chrome/browser/desktop/index.html" target="_blank">
                        <span>Chrome</span>
                    </a>
                </div>

                <div>
                    <a href="http://www.opera.com/ru" target="_blank">
                        <img src="/img/browsers/browser-opera.png" alt="Opera"/>
                    </a>
                    <a href="http://www.opera.com/ru" target="_blank">
                        <span>Opera</span>
                    </a>
                </div>

                <div>
                    <a href="https://www.mozilla.org/ru/firefox/new/" target="_blank">
                        <img src="/img/browsers/browser-firefox.png" alt="Firefox"/>
                    </a>
                    <a href="https://www.mozilla.org/ru/firefox/new/"
                       target="_blanhttp://windows.microsoft.com/ru-ru/internet-explorer/download-iek">
                        <span>Firefox</span>
                    </a>
                </div>

                <div>
                    <a href="https://www.microsoft.com/ru-ru/windows/microsoft-edge" target="_blank">
                        <img src="/img/browsers/egg.png" alt="Microsoft Edge"/>
                    </a>
                    <a href="https://www.microsoft.com/ru-ru/windows/microsoft-edge" target="_blank">
                        <span>Microsoft Edge</span>
                    </a>
                </div>

                <div>
                    <a href="https://www.microsoft.com/ru-ru/windows/microsoft-edge" target="_blank">
                        <img src="/img/browsers/ylogo.png" alt="Яндекс.Браузер "/>
                    </a>
                    <a href="https://www.microsoft.com/ru-ru/windows/microsoft-edge" target="_blank">
                        <span>Яндекс.Браузер</span>
                    </a>
                </div>

            </div>
        </div>
    </div>


</div>