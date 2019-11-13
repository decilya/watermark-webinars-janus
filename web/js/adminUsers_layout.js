function disableF5(e) {
    if ((e.which || e.keyCode) == 116 || (event.ctrlKey && event.keyCode == 116)) {

        e.preventDefault();

        // $.ajax({
        //     method: 'POST',
        //     dataType: 'json',
        //     url: '/ajax/refresh-user-situation',
        //     data: {userId: $('#userBlock').data('user')},
        //     async: false,
        //     success: function (data) {
                 document.location.reload(true);
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

    let myUserId = $("#userBlock").data('user');

    $('.mdl-navigation__link').on('click', function () {

        let href = $(this).data('href');
        document.location.replace(href);

    });


    // $(window).on('beforeunload', function () {
    //
    //     document.location.reload(true);
    //
    // });
    //
    //
    // function sendAjaxSetUserSituation() {
    //
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

});