function disableF5(e) {

    if ((e.which || e.keyCode) == 116) {

        e.preventDefault();


        document.location.reload(true);

        // $.ajax({
        //     method: 'POST',
        //     dataType: 'json',
        //     url: '/ajax/refresh-user-situation',
        //     data: {userId: $('#userBlock').data('user')},
        //     async: false,
        //     success: function (data) {
        //         $.ajax({
        //             method: 'POST',
        //             dataType: 'json',
        //             url: '/ajax/refresh-admin-translate',
        //             async: false,
        //             data: {userId: $('#userBlock').data('user')},
        //             success: function (data) {
        //                 document.location.reload(true);
        //             },
        //             error: function () {
        //                 document.location.reload(true);
        //             }
        //         });
        //     }
        // });
    }
}

$(document).on("keydown", disableF5);

$(document).ready(function () {

    let navLink = $('nav a');


    navLink.each(function (i, elem) {
        if($(this).attr('href') === window.location.pathname) {
            $(this).replaceWith("<span class='mdl-navigation__link active' data-href=" + $(this).attr('href') + " >" + $(this).html() + "</span>");
        } else {
            $(this).replaceWith("<span class='mdl-navigation__link' data-href=" + $(this).attr('href') + " >" + $(this).html() + "</span>");
        }
        
    });

    $('.mdl-navigation__link').on('click', function () {

        let href = $(this).data('href');
        document.location.replace(href);

        // $.ajax({
        //     method: 'POST',
        //     dataType: 'json',
        //     url: '/ajax/refresh-user-situation',
        //     async: false,
        //     data: {userId: $('#userBlock').data('user')},
        //     success: function (data) {
        //         $.ajax({
        //             method: 'POST',
        //             dataType: 'json',
        //             url: '/ajax/refresh-admin-translate',
        //             async: false,
        //             data: {userId: $('#userBlock').data('user')},
        //             success: function (data) {
        //                 document.location.replace(href);
        //             },
        //             error: function () {
        //                 document.location.replace(href);
        //             }
        //         });
        //
        //     },
        //     error: function ($e) {
        //     }
        // });
    });

    let myUserId = $("#userBlock").data('user');

    if (myUserId) {

        /** Статус присутсвия пользователя в системе */
        function sendStatus(myUserId) {
            if (myUserId) {

                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    data: {userId: $('#userBlock').data('user')},
                    url: '/ajax/login-user-info',
                    async: false,
                    success: function (data) {
                        // //console.log(data);
                    }
                });
            }
        }

        sendStatus(myUserId);

        setInterval(
            function () {
                sendStatus(myUserId);
            },
            15000
        );
    }
    //
    // $(window).on('beforeunload', function () {
    //
    //     $.ajax({
    //         method: 'POST',
    //         dataType: 'json',
    //         url: '/ajax/refresh-user-situation',
    //         data: {userId: $('#userBlock').data('user')},
    //         async: false,
    //         success: function (data) {
    //             document.location.reload(true);
    //         }
    //     });
    //
    // });

    // function sendAjaxSetUserSituation() {
    //     $.ajax({
    //         method: 'POST',
    //         dataType: 'json',
    //         url: '/ajax/set-user-situation',
    //         async: false,
    //         data: {userId: myUserId}
    //     });
    // }
    //
    // sendAjaxSetUserSituation();
    //
    // setInterval(
    //     function () {
    //         sendAjaxSetUserSituation();
    //     }, 3000
    // );

    $('.myArchiveLink').on('click', function () {

        let myBtn = $(this);
        myBtn.attr('disabled', true);
        $('.myArchiveLink a').attr('disabled', true);

        if (!myBtn.hasClass("sendClick")) {
            myBtn.addClass("sendClick");

            // $.ajax({
            //     method: 'POST',
            //     dataType: 'json',
            //     url: '/ajax/refresh-user-situation',
            //     async: false,
            //     data: {userId: $('#userBlock').data('user')},
            //     success: function () {
            //         //window.location.replace("https://<?= $_SERVER['HTTP_HOST'] ?><?= \yii\helpers\Url::to(['/record/archive/']); ?>");
            //     },
            //     error: function (jqxhr, status, errorMsg) {
            //
            //     }
            // });

        } else {
            myBtn.attr('disabled', true);
            $('.myArchiveLink a').attr('disabled', true);

            setTimeout(
                function () {
                    myBtn.removeClass("sendClick");
                    $('.myArchiveLink a').attr('disabled', false);
                    myBtn.attr('disabled', false);
                }, 1000
            );
        }
    });

});
