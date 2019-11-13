function Wm() {

    const userId = siteUser.id;

    this.cnt = 1;

    this.altTextForWmImg = '';

    function stopVideo() {

        if (!$('#echoTestBlockBeforeStartUser').is(':visible')) {

            if ((!($("#bigError").is(":visible"))) ||
                (!($("#noTotalConnectionDiv").is(":visible"))) ||
                (!($("#noConnectionDiv").is(":visible")))) {

                setTimeout(
                    () => {
                        if (!$('#echoTestBlockBeforeStartUser').is(':visible')) {

                            if ((!($("#bigError").is(":visible"))) ||
                                (!($("#noTotalConnectionDiv").is(":visible"))) ||
                                (!($("#noConnectionDiv").is(":visible")))) {

                                checkStatus(1000, true);
                            }
                        }
                    }, 500
                );

            }
        }
    }

    this.startCheckWatermark = function () {
        let checkWatermarkInterval = setInterval(
            () => {
                if (!$('#echoTestBlockBeforeStartUser').is(':visible')) {
                    this.checkWatermark(checkWatermarkInterval);
                }
            }, 3000
        );
    };

    this.selfRandom = function selfRandom(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    this.reBuildBlock = function reBuildBlock(containerPlayer, cnt) {

        let vss = $('video');
        let canvas1 = $('canvas');
        if (canvas1.length > 0) window.location.reload();
        let frame2 = $('frame');
        if (frame2.length > 0) window.location.reload();
        if (vss.length > 1) window.location.reload();


        if (!$('article#adBlock').is(':visible')) {

            let baseUrl = $("#urlHost").val();
            let myMedElemWidth = $('video').width();
            let myMedElemHeight = $('video').height() * 1.2;

            let ran = this.selfRandom(1, 100);
            ran = ran * 0.001;
            myMedElemWidth = myMedElemWidth + ran;
            myMedElemHeight = myMedElemHeight + ran;

            let urlToCreatePng = "https://" + baseUrl + "/site/create-png?height=" + 1080 + "&width=" + 1980;
            let widthImg = myMedElemWidth / 4; // * 25) / 100;
            let heightImg = 100; // (myMedElemHeight * 25) / 100;

            let tmpWmImg = document.createElement('img');
            tmpWmImg.setAttribute('src', urlToCreatePng);

            $.ajax({
                method: 'POST',
                url: urlToCreatePng,
                success: function (data) {
                    //  console.log(data)
                },
                error: function () {
                    if (remoteFeedScreen.getBitrate() !== '0 kbits/sec') {
                        stopVideo();
                    }
                }
            });

            let p = $(containerPlayer).parent('div');
            let x = p.width() * 15 / 100;

            p.children('.vjs-watermark').detach();

            let wm = document.createElement('div');
            let wmImg = document.createElement('img');

            wmImg.setAttribute('src', tmpWmImg.src);
            wmImg.setAttribute('style', "color: red; font: 35px 'Roboto'; font-weight: bold; display: block !important; width: " + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;');
            wmImg.setAttribute('alt', this.altTextForWmImg);

            wm.classList.add("vjs-watermark");
            wmImg.classList.add("vjs-img");

            var strStyle = 'position: absolute !important; opacity: 100 !important; display: block !important; width: ' + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;';

            wm.setAttribute('style', strStyle);


            let h1 = $('video').get(0).clientHeight;
            let w1 = $('video').get(0).clientWidth;

            let h2 = $('video').get(0).videoHeight;
            let w2 = $('video').get(0).videoWidth;

            let y2 = 0;

            let cc = w2 / h2;
            let cc2 = w1 / h1;

            if (cc > 1) {
                let xx = h2 / w2;
                let hx = w1 * xx;
                y2 = Math.abs((h1 - hx) / 2);
            }

            if ((cc < 1)) {
                let yy = w2 / h2;
                let wy = h1 * yy;
                x = Math.abs((w1 - wy) / 2);
            }

            switch (cnt) {
                case 1:
                    wm.setAttribute('style', 'top:' + y2 + 'px !important; left: ' + x + 'px !important;');
                    break;

                case 2:
                    wm.setAttribute('style', 'bottom: ' + y2 + 'px !important; left: ' + x + 'px !important;');
                    break;

                case 3:
                    wm.setAttribute('style', 'top: ' + y2 + 'px !important; right: ' + x + 'px!important;');
                    break;

                default:
                    wm.setAttribute('style', 'bottom: ' + y2 + 'px !important; right: ' + x + 'px !important;');

            }

            p.append(wm);
            wm.appendChild(wmImg);
        }
    };

    this.checkWatermark = function () {

        function initClientAdBlock() {

            if ((window.location.pathname === '/site/index') || (window.location.pathname === '/')) {

                checkAdBlock(function (blocked) {

                    if (blocked) {
                        window.adBlockFuck = undefined;
                    }

                    if (($('#noConnectionDiv').is(':hidden')) &&
                        ($('#noTotalConnectionDiv').is(':hidden')) &&
                        ($('#bigError').is(':hidden')) &&
                        ($('#loadingJanus').is(':hidden'))) {

                        if (window.adBlockFuck === undefined) {

                            window.location.reload(true);
                        }

                    }

                });
            }

        }

        setTimeout(
            () => {
                initClientAdBlock();
            }, 3000
        );
    };

    this.init = function () {

        var tmpObj = this;

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: '/ajax/get-alt-for-watermark',
            success: function (altTextForWmImg) {
                tmpObj.altTextForWmImg = altTextForWmImg;

                let vs = $('video');
                let canvas = $('canvas');
                if (canvas.length > 0) window.location.reload();

                let frame = $('frame');
                if (frame.length > 0) window.location.reload();

                if (vs.length > 1) window.location.reload();

                $.each(vs, function (key, video) {
                    tmpObj.cnt = tmpObj.selfRandom(1, 4);
                    tmpObj.reBuildBlock(video, tmpObj.cnt);
                });

                setInterval(function () {

                    let vs = $('video:eq(0)');

                    if (vs.length > 1) window.location.reload();

                    $.each(vs, function (key, video) {
                        tmpObj.cnt = tmpObj.selfRandom(1, 4);
                        tmpObj.reBuildBlock(video, tmpObj.cnt);
                    });

                }, 7000);

                tmpObj.startCheckWatermark();
            },
            error: function () {

                if (remoteFeedScreen.getBitrate() !== '0 kbits/sec') {
                    stopVideo();
                }
            }
        });

    }
}

$(document).ready(function () {
    var myCheckScreen = setInterval(
        function () {

            if ($('video').is(':visible')) {

                if (!$('div').is('.vjs-watermark')) {
                    var myWm3 = new Wm();
                    myWm3.init();
                    myWm3.startCheckWatermark();
                }

                clearInterval(myCheckScreen);
            }

        }, 3000
    );
});