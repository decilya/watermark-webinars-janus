//$('.b-row2', '.main-page').fadeOut(0);
$(document).ready(function () {

    function scrollButton() {
        var chat_scroll = $('.tab-content-wrapper');
        chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
    }

    setTimeout(function () {
        scrollButton();
    }, 1500);


    setInterval(function () {

        $('#inputMessageChat').on('focus', function () {
            $('.glyphicon-send').css({
                color: '#5BC0DE'
            });

            setInterval(function () {
                var hei = $('.nav-tabs li:nth-child(3)').height();
                $('.nav-tabs li:nth-child(1)').height(hei);
            }, 10);

            $('#inputMessageChat').on('focusout', function () {
                $('.glyphicon-send').css({
                    color: '#888888'
                });
                $('#inputMessageChat').removeClass('borderBlue');
            });

            var textarea = document.querySelector('#inputMessageChat');
            textarea.addEventListener('contextmenu', autosize2);
            textarea.addEventListener('keydown', autosize);

        });
    }, 1000);


    function autosize() {
        var el = this;
        setTimeout(function () {
            el.style.cssText = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 1000);

    }

    function autosize2() {
        var el = this;
        setTimeout(function () {
            el.style.cssText = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 111);

    }


    /**
     * Chat msg window set default size by click
     */
    $('#sendMessage').on('click', function () {

        setTimeout(function () {
            scrollButton();
        }, 1000);
        setTimeout(function () {
            $('#inputMessageChat').css('height', '40px');
            scrollButton();
        }, 110);
    });

    /**
     * Chat msg window set default size by enter
     */
    $('#inputMessageChat').keypress(function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();

            $('#sendMessage').click();

        }
    });

    scrollButton();

    $(window).resize(function () {
        scrollButton();
    });

    setInterval(function () {

        $('.video-js .vjs-tech').attr('style', 'width: 100% !important;max-height: 949px;');

    }, 10);

});


