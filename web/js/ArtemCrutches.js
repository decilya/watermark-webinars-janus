//Функция по превращению "элементов" в фулсрин приложение
function fullScreen(element) {
    // element = элемент документа, к примеру document.getElementById('videoShowWithWaterMark')

    // Проверки на возможность сделать фулскрин и функции кросбраузерности
    if(element.requestFullscreen) {
      element.requestFullscreen();
    } else if(element.webkitrequestFullscreen) {
      element.webkitRequestFullscreen();
    } else if(element.mozRequestFullscreen) {
      element.mozRequestFullScreen();
    }
}
//
// // Opera 8.0+
// var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
//
// // Firefox 1.0+
// var isFirefox = typeof InstallTrigger !== 'undefined';
//
// // Safari 3.0+ "[object HTMLElementConstructor]"
// var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
//
// // Internet Explorer 6-11
// var isIE = /*@cc_on!@*/false || !!document.documentMode;
//
// // Edge 20+
// var isEdge = !isIE && !!window.StyleMedia;
//
// // Chrome 1 - 71
// var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
//
// // Blink engine detection
// var isBlink = (isChrome || isOpera) && !!window.CSS;
//
//
// if(isChrome === false) {
//     window.location.href = '/site/browser-outdated';
// }

function fullScreenCancel() {
    var isInFullScreen = (document.fullscreenElement && document.fullscreenElement !== null) ||
        (document.webkitFullscreenElement && document.webkitFullscreenElement !== null) ||
        (document.mozFullScreenElement && document.mozFullScreenElement !== null) ||
        (document.msFullscreenElement && document.msFullscreenElement !== null);
    if (isInFullScreen)  {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

$(document).ready(function () {
    videoCanvas.muted = false;
    videoCanvas.volume = 0.35;

    $('.full-screen').click(function() {
        let videoElement = document.getElementById('videoWaterMark');

        if(videoElement.classList.contains("fullscreen")) {
            fullScreenCancel();
            let fullScreenIcon = document.getElementById('fullScreenIcon');
            fullScreenIcon.className = 'fas fa-expand';
        } else {
            fullScreen(videoElement);
            let fullScreenIcon = document.getElementById('fullScreenIcon');
            fullScreenIcon.className = 'fas fa-compress'
        }
        videoElement.classList.toggle('fullscreen');

    })
    document.addEventListener("fullscreenchange", function(){
        let leftSide = $('.left-side');
        let rightSide = $('.right-side');
        let fsBtn = $('.tv-screen');

        fsBtn.removeClass('full');
        $('#videoWM').css({width: '100%',  height: '100%'});
        $('#videoWM').removeAttr('style');
        rightSide.removeAttr('style');
        leftSide.removeAttr('style');
    }, false);

    $('.volume').click(function() {
        let volumeChange = document.getElementById('volumeChange');
        if( videoCanvas.muted ) {
            volumeChange.className = 'fas fa-volume-up';
            slider.slider('value', 100);
            videoCanvas.volume = 1;
            videoCanvas.muted = false;
        } else {
            volumeChange.className = 'fas fa-volume-mute';
            slider.slider('value', 0);
            videoCanvas.volume = 1;
            videoCanvas.muted = true;
        }
    })

    //Отключаем райт клик
    $("video").bind("contextmenu",function(){
        return false;
    });
    document.addEventListener("fullscreenchange", function() {
        let videoElement = document.getElementById('videoWaterMark');
    }, false);

    $('#screenvideo2, .vjs-control-bar, vjs-watermark, vjs-img, #videoWaterMark').mouseenter(function(event) {
        event.stopPropagation();
        $('.vjs-control-bar').css('display', 'flex')
    })

    $('#screenvideo2, .vjs-control-bar, vjs-watermark, vjs-img, #videoWaterMark').mouseleave(function(event) {
        event.stopPropagation();
        $('.vjs-control-bar').css('display', 'none')
    })

    $('#screenvideo2, .vjs-control-bar, vjs-watermark, vjs-img, #videoWaterMark').mouseover(function(event) {
        event.stopPropagation();
        $('.vjs-control-bar').css('display', 'flex')
    })
    //Store frequently elements in variables
    var slider  = $('#slider')
    let volumeChange = document.getElementById('volumeChange');
    volumeChange.className = 'fas fa-volume-off';
    //Call the Slider
    slider.slider({
        //Config
        range: "min",
        min: 1,
        max: 100,
        value: 35,

        start: function(event,ui) {
        },

        //Slider Event
        slide: function(event, ui) { //When the slider is sliding

            let volumeChange = document.getElementById('volumeChange');
            let value  = slider.slider('value')

            videoCanvas.volume = value / 100;
            if(value <= 5) {
                videoCanvas.muted = true;
                videoCanvas.volume = 0;
                volumeChange.className = 'fas fa-volume-mute';
            } 
            else if (value <= 35) {
                videoCanvas.muted = false;
                volumeChange.className = 'fas fa-volume-off';
            } 
            else if (value <= 65) {
                videoCanvas.muted = false;
                volumeChange.className = 'fas fa-volume-down';
            } 
            else {
                videoCanvas.muted = false;
                volumeChange.className = 'fas fa-volume-up';
            };

            
        },
        stop: function(event,ui) {
        },
    });

})

