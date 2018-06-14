function Wm() {

    const userId = $('#userBlock').attr('data-user');

    this.cnt = 1;

    this.altTextForWmImg = '';

    this.selfRandom = function selfRandom(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    this.reBuildBlock = function reBuildBlock(containerPlayer, cnt) {

        let baseUrl = $("#urlHost").val();
        let myMedElemWidth = $('#screenvideo2').width();

        let urlToCreatePng = "https://" + baseUrl + "/site/create-png?id=" + userId + "&width=" + myMedElemWidth;

        let tmpHeight = (myMedElemWidth / 2.6);
        let widthImg = (myMedElemWidth * 25) / 100;
        let heightImg = (tmpHeight * 25) / 100;

        let tmpWmImg = document.createElement('img');
        tmpWmImg.setAttribute('src', urlToCreatePng);

        let p = $(containerPlayer).parent('div');

        let x = p.width() * 15 / 100;

        p.children('.vjs-watermark').detach();

        let wm = document.createElement('div');
        let wmImg = document.createElement('img');

        wmImg.setAttribute('src', tmpWmImg.src);
        wmImg.setAttribute('style', "color: red; font: 35px 'Roboto'; font-weight: bold; display: block !important; width: " + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;');
        wmImg.setAttribute('alt', this.altTextForWmImg);

        wm.classList.add("vjs-watermark");

        var strStyle = 'position: absolute !important; opacity: 100; display: block !important; width: ' + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;';

        wm.setAttribute('style', strStyle);

        switch (cnt) {
            case 1:
                wm.setAttribute('style', 'top: 0px !important; left: '+x+'px !important;');
                break;

            case 2:
                wm.setAttribute('style', 'bottom: 0px !important; left: '+x+'px !important;');
                break;

            case 3:
                wm.setAttribute('style', 'top: 0px !important; right: '+x+'px!important;');
                break;

            default:
                wm.setAttribute('style', 'bottom: 0px !important; right: '+x+'px !important;')
        }

        p.append(wm);
        wm.appendChild(wmImg);
    };

    this.init = function () {

        var tmpObj = this;

        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: '/ajax/get-alt-for-watermark',
            success: function (altTextForWmImg) {
                tmpObj.altTextForWmImg = altTextForWmImg;

                setInterval(function () {

                    let vs = $('video:eq(1)');

                    $.each(vs, function (key, video) {
                        tmpObj.cnt = tmpObj.selfRandom(1, 4);
                        tmpObj.reBuildBlock(video, tmpObj.cnt);
                    });

                }, 7000);
            }
        });
    }

}

let myWm = new Wm();
myWm.init();





