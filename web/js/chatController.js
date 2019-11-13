//$('.b-row2', '.main-page').fadeOut(0);
$(document).ready(function () {

    function scrollButton() {
        var chat_scroll = $('.tab-content-wrapper');
        if ($('.msg-item').length > 11) {
            chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
        }
    }

    setTimeout(function () {
        scrollButton();
    }, 1500);

    function Dispatcher() {
        this.listeners = {};
    }

    Dispatcher.prototype = {
        on: function (eventName, func) {
            if (typeof this.listeners[eventName] === 'undefined') {
                this.listeners[eventName] = [];
            }
            this.listeners[eventName].push(func);
        },
        trigger: function (eventName, data) {
            if (typeof this.listeners[eventName] === 'undefined') {
                return;
            }
            this.listeners[eventName].forEach(function (handler) {
                if (handler) {
                    handler(data);
                }
            })
        }
    };

    var dispatcher = new Dispatcher();

    var ids = setInterval(function () {  //R

        var body = document.querySelector('body');

        if (body.offsetWidth < 780) {
            dispatcher.trigger('minWidth');
            clearInterval(ids);
        }

    }, 1000);

    $(window).resize(function () {

        var body = document.querySelector('body');

        if (body.offsetWidth < 780) {
            dispatcher.trigger('minWidth');
            $('body').addClass('full-scr');
        }

    });

    $(window).resize(function () {

        var body = document.querySelector('body');

        if ($('body').hasClass('full-scr') && body.offsetWidth > 780) {
            dispatcher.trigger('maxWidth');
        }

    });

    dispatcher.on('maxWidth', function () {

        var fullScreen = $('#screenvideo2 .vjs-control-bar');
        var leftSide = $('.left-side');
        var rightSide = $('.right-side');
        var topVideo = $('.top-video');
        var bottomVideo = $('.bottom-video');
        var bottomVideoContainer = $('#videos-container');
        var bRow = $('.b-row');
        var rightTable = $('.right-table');
        var tabContent = $('.tab-content');

        var aa = $('.video-chat-wrapper2');
        aa.fadeOut();

        setTimeout(function () {
            scrollButton();
        }, 1000);

        bottomVideoContainer.css({
            alignItems: 'flex-end',
            flexDirection: 'column',
            marginTop: '0px'
        });

        leftSide.css('display', 'block');
        rightSide.css('display', 'block');

       // leftSide.addClass('col-sm-8 col-lg-8 col-md-8');
       // rightSide.addClass('col-sm-4 col-lg-4 col-md-4');

        leftSide.removeClass('col-sm-12 col-lg-12 col-md-12');
        rightSide.removeClass('col-sm-12 col-lg-12 col-md-12');

        rightSide.css('padding', '0');
        rightSide.css('margin-top', '15px');

        rightSide.removeClass('row');

        bRow.append(rightSide);

        rightTable.css({
            display: 'table',
            //height: '500px'
        });

        tabContent.css({
            height: '100%',
            display: 'table-row',
            border: '0px solid #000000'
        });

        var aa = $('.video-chat-wrapper2');
        aa.each(function (i) {
            $(this).addClass('www' + i);
            if ($(this).hasClass('www0')) {
                $(this).hide();
            }
        });

        $('.full-screen').removeClass('full');
        $('body').removeClass('full-scr');
        $('body').addClass('min-scr');
    });


    dispatcher.on('minWidth', function () {

        var fullScreen = $('#screenvideo2 .vjs-control-bar');
        var leftSide = $('.left-side');
        var rightSide = $('.right-side');
        var topVideo = $('.top-video');
        var bottomVideo = $('.bottom-video');
        var bottomVideoContainer = $('#videos-container');
        var bRow = $('.b-row');
        var rightTable = $('.right-table');
        var tabContent = $('.tab-content');

        setInterval(function () {  //R
            if ($(document).width() < 768) {
                $(".full-screen").css('display', 'none');
            } else if ($(document).width() > 768) {
                $(".full-screen").css('display', 'block');
            }
        }, 1000);

        $(".full-screen").addClass('full');

        leftSide.css({
            display: 'flex',
            flexDirection: 'column',
            maxHeight: '100%',
            height: 'auto'
        });

        topVideo.css({
            order: '2'
        });

        bottomVideo.css({
            order: '1'
        });

        bottomVideoContainer.addClass('flex-col');
        bottomVideoContainer.css({
            alignItems: 'flex-start',
            flexDirection: 'row',
           // marginTop: '20px'
        });

        // rightSide.css('display', 'table-row');
        rightSide.css('display', 'block');
        rightSide.css('padding', '15px');
        rightSide.addClass('row');

      //  leftSide.removeClass('col-sm-8 col-lg-8 col-md-8');
        rightSide.removeClass('col-sm-4 col-lg-4 col-md-4');

     //   leftSide.addClass('col-sm-12 col-lg-12 col-md-12');
        rightSide.addClass('col-sm-12 col-lg-12 col-md-12');

        var $wrapp = $('<div class="video-chat-wrapper2"/>');
        var $bRow = $('<div class="b-row2"/>');

        var aa = $('.video-chat-wrapper2');
        //aa.remove();

        bottomVideoContainer.append($wrapp);

        rightSide.css('padding', '0');
        rightSide.css('margin-top', '0px');

        rightTable.css({
            //height: '500px',
            display: 'flex',
            flexDirection: 'column'
        });
        tabContent.css({
            height: '500px',
            display: 'flex',
            flexDirection: 'column'
        });
    });

    // setInterval(function () {
    //
    //     $('#inputMessageChat').on('focus', function () {
    //         $('.glyphicon-send').css({
    //             color: '#5BC0DE'
    //         });
    //
    //         setInterval(function () {
    //             var hei = $('.nav-tabs li:nth-child(3)').height();
    //             $('.nav-tabs li:nth-child(1)').height(hei);
    //         }, 100);
    //
    //         $('#inputMessageChat').on('focusout', function () {
    //             $('.glyphicon-send').css({
    //                 color: '#888888'
    //             });
    //             $('#inputMessageChat').removeClass('borderBlue');
    //         });
    //
    //         var textarea = document.querySelector('#inputMessageChat');
    //         textarea.addEventListener('contextmenu', autosize2);
    //         textarea.addEventListener('keydown', autosize);
    //
    //     });
    // }, 1000);


    function autosize() {
        var el = this;
        setTimeout(function () {
            el.style = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 1000);
    }

    function autosize2() {
        var el = this;
        setTimeout(function () {
            el.style = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 111);
    }

    /**
     * Chat msg window set default size by click
     */
    $('#sendMessage').on('click', function () {

        janusClientChat.sendData();

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
    $('#inputMessageChat').on('keypress', function (e) {

        if (e.keyCode === 13) {
            e.preventDefault();
            autosize();
            $('#sendMessage').click();

        }
    });

    scrollButton();

    $(window).resize(function () {
        scrollButton();
    });

    // setInterval(function () {
    //
    //     $('.video-js .vjs-tech').attr('style', 'width: 100% !important;max-height: 949px;');
    //
    // }, 10);

});

// =============================================================================== //

var serverChat = "https://" + window.location.hostname + "/janus";
var janusClientChat = {};
var textRoomClient = null;
var opaqueChatClientId = "textroomtest-" + Janus.randomString(12);
var myChatRoom = Number($('#roomNumber').val());
var myClientChatId = null;
var participants = {};
var transactions = {};
var userBlockChat = $('#userBlock');
var patronymicChat = siteUser.patronymic;
var surnameChat = siteUser.surname;
var nameChat = siteUser.name;
var clientChatUserName = surnameChat + ' ' + nameChat + ' ' + patronymicChat;

function setChatPresents(participants) {

    var tmpSet = new Set();

    for (var index in participants) {
        tmpSet.add(participants[index]);
    }

    if ($('#present')) {
        if ($('#present').length) {
            $('#present p').remove();

            tmpSet.forEach((value, valueAgain, set) => {
                $('#present').append('<p>' + value + '</p>');
            });

            $('#nowOnline').text(tmpSet.size);
        }
    }
}

$('.main .right-table .nav-tabs li a').on('click', function (e) {

    let msgBox = $('.right-table .enter-msg');

    if ($(e.target).attr('href') === '#panel2') {
        msgBox.hide(500);
    } else if ($(e.target).attr('href') === '#panel1') {
        msgBox.show(500);
    }

});

//
// setInterval(function () {

$('#inputMessageChat').on('keypress', function (e) {
    if (e.keyCode === 13) {
        janusClientChat.sendData();
    }
});
//
// }, 1000);

janusClientChat.sendData = function () {
    var data = $('#inputMessageChat').val();
    if ((data == null) || (!data)) {
        return;
    }

    this.sendMsg(data);
};


janusClientChat.sendReloadMsg = function () {
    var data = "SYSTEM_reload_SYSTEM";

    this.sendMsg(data);
};

function checkMsg(msg) {

    setTimeout(function () {
        var chat_scroll = $('.tab-content-wrapper');
        chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
    }, 1000);
    setTimeout(() => {
        $('#chatBlock').animate({ scrollTop: $('#chatroom').height() }, "slow");
    }, 500)
    if (msg === "SYSTEM_reload_SYSTEM") {
        if ($('.main-page').length) {
            window.location.reload(true);
        }
        return;
    }
    return true;
}


function checkEnter(field, event) {
    var theCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if (theCode == 13) {
        if (field.id == 'username')
            registerChatUserName();
        else if (field.id == 'sendMessage')
            janusClientChat.sendData();
        return false;
    } else {
        return true;
    }
}

function registerChatUserName() {

    var username = clientChatUserName;

    myClientChatId = randomString(12);
    var transaction = randomString(12);
    var register = {
        textroom: "join",
        transaction: transaction,
        room: myChatRoom,
        username: myClientChatId,
        display: username
    };

    transactions[transaction] = function (response) {
        if (response["textroom"] === "error") {

            // //console.log("================================");
            // //console.log(response);

            // Something went wrong
            // $.ajax({
            //     method: 'POST',
            //     dataType: 'json',
            //     url: '/ajax/refresh-user-situation',
            //     data: {userId: siteUser.id},
            //     async: false,
            //     success: function (data) {
            // window.location.reload(true);
            //     }
            // });

            return;
        }

        // We're in
        $('#roomjoin').hide();
        $('#room').removeClass('hide').show();
        $('#participant').removeClass('hide').html(clientChatUserName).show();
        // $('#chatroom').css('height', ($(window).height() - 420) + "px");
        $('#sendMessage').removeAttr('disabled');
        // Any participants already in?
        if (response.participants && response.participants.length > 0) {
            for (var i in response.participants) {
                var p = response.participants[i];
                participants[p.username] = p.display ? p.display : p.username;
                if (p.username !== myClientChatId && $('#rp' + p.username).length === 0) {
                    // Add to the participants list
                    $('#list').append('<li id="rp' + p.username + '" class="list-group-item">' + participants[p.username] + '</li>');
                    $('#rp' + p.username).css('cursor', 'pointer').click(function () {
                        var username = $(this).attr('id').split("rp")[1];
                        sendPrivateMsg(username);
                    });
                }

                //$('#chatroom').append('<p style="color: green;">[' + getDateString() + '] <i>' + participants[p.username] + ' joined</i></p>');
                if ($('#chatroom').get(0)) {
                    $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                }
            }
        }
    };

    textRoomClient.data({
        text: JSON.stringify(register),
        error: function (reason) {
            bootbox.alert(reason);
            $('#username').removeAttr('disabled').val("");
            $('#register').removeAttr('disabled').click(registerChatUserName);
        }
    });

}

function sendPrivateMsg(username) {

    var display = participants[username];
    if (!display) return;

    bootbox.prompt("Private message to " + display, function (result) {
        if (result && result !== "") {
            var message = {
                textroom: "message",
                transaction: randomString(12),
                room: myChatRoom,
                to: username,
                text: result
            };

            textRoomClient.data({
                text: JSON.stringify(message),
                error: function (reason) {
                    bootbox.alert(reason);
                },
                success: function () {
                    $('#chatroom').append('<p style="color: purple;">[' + getDateString() + '] <b>[whisper to ' + display + ']</b> ' + result);
                    $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                }
            });
        }
    });
}


// Helper to format times
function getDateString(jsonDate) {

    var when = new Date();
    if (jsonDate) {
        when = new Date(Date.parse(jsonDate));
    }
    return ("0" + when.getHours()).slice(-2) + ":" + ("0" + when.getMinutes()).slice(-2);
}

// Just an helper to generate random usernames
function randomString(len, charSet) {
    charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
        var randomPoz = Math.floor(Math.random() * charSet.length);
        randomString += charSet.substring(randomPoz, randomPoz + 1);
    }
    return randomString;
}


$(document).ready(function () {

    // Initialize the library (all console debuggers enabled)
    Janus.init({
        debug: "all", callback: function () {

            // Make sure the browser supports WebRTC
            if (!Janus.isWebrtcSupported()) {
                bootbox.alert("No WebRTC support... ");
                return;
            }

            // Create session
            janusClientChat = new Janus(
                {
                    server: serverChat,
                    success: function () {
                        // Attach to text room plugin
                        janusClientChat.attach(
                            {
                                plugin: "janus.plugin.textroom",
                                opaqueId: opaqueChatClientId,
                                success: function (pluginHandle) {
                                    textRoomClient = pluginHandle;
                                    //Janus.log("Plugin attached! (" + textRoomClient.getPlugin() + ", id=" + textRoomClient.getId() + ")");
                                    // Setup the DataChannel
                                    var body = {"request": "setup"};
                                    /*Janus.debug("Sending message present(" + JSON.stringify(body) + ")");*/
                                    textRoomClient.send({"message": body});

                                    setTimeout(
                                        function () {
                                            setChatPresents(participants);
                                        }, 300
                                    );

                                    setInterval(   //R
                                        function () {
                                            setChatPresents(participants);
                                        }, 2500
                                    );

                                },
                                error: function (error) {
                                    console.error("  -- Error attaching plugin...", error);
                                    bootbox.alert("Error attaching plugin... " + error);
                                },
                                webrtcState: function (on) {
                                    //Janus.log("Janus says our WebRTC PeerConnection is " + (on ? "up" : "down") + " now");
                                    //$("#videoleft").parent().unblock();
                                },
                                onmessage: function (msg, jsep) {
                                    /*Janus.debug(" ::: Got a message :::");*/
                                    /*Janus.debug(msg);*/

                                    if (msg["error"] !== undefined && msg["error"] !== null) {
                                        bootbox.alert(msg["error"]);
                                    }
                                    if (jsep !== undefined && jsep !== null) {
                                        // Answer
                                        textRoomClient.createAnswer(
                                            {
                                                jsep: jsep,
                                                media: {audio: false, video: false, data: true},	// We only use datachannels
                                                success: function (jsep) {
                                                    /*Janus.debug("Got SDP!");*/
                                                    /*Janus.debug(jsep);*/
                                                    var body = {"request": "ack"};
                                                    textRoomClient.send({"message": body, "jsep": jsep});
                                                },
                                                error: function (error) {
                                                    Janus.error("WebRTC error:", error);
                                                    bootbox.alert("WebRTC error... " + JSON.stringify(error));
                                                }
                                            });
                                    }
                                },
                                ondataopen: function (data) {
                                    //Janus.log("The DataChannel is available!");
                                    registerChatUserName();
                                },
                                ondata: function (data) {
                                    //SYSTEM_reload_SYSTEM

                                    /*Janus.debug("We got data from the DataChannel! " + data);*/
                                    //~ $('#datarecv').val(data);
                                    var json = JSON.parse(data);
                                    var transaction = json["transaction"];
                                    if (transactions[transaction]) {
                                        // Someone was waiting for this
                                        transactions[transaction](json);
                                        delete transactions[transaction];
                                        return;
                                    }
                                    var what = json["textroom"];
                                    if (what === "message") {
                                        // Incoming message: public or private?
                                        var msg = json["text"];

                                        if (checkMsg(msg)) {
                                            msg = msg.replace(new RegExp('<', 'g'), '&lt');
                                            msg = msg.replace(new RegExp('>', 'g'), '&gt');
                                            var from = json["from"];
                                            var dateString = getDateString(json["date"]);
                                            var whisper = json["whisper"];
                                            if (whisper === true) {
                                                // Private message
                                                $('#chatroom').append('<p style="color: purple;">[' + dateString + '] <b>[whisper from ' + participants[from] + ']</b> ' + msg);
                                                $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                                            } else {
                                                // Public message !!!
                                                $('#chatroom').append('<p>[' + dateString + '] <b>' + participants[from] + ':</b> ' + msg);
                                                $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                                            }
                                        }

                                    } else if (what === "join") {
                                        // Somebody joined
                                        var username = json["username"];
                                        var display = json["display"];
                                        participants[username] = display ? display : username;
                                        if (username !== myClientChatId && $('#rp' + username).length === 0) {
                                            // Add to the participants list
                                            $('#list').append('<li id="rp' + username + '" class="list-group-item">' + participants[username] + '</li>');
                                            $('#rp' + username).css('cursor', 'pointer').click(function () {
                                                var username = $(this).attr('id').split("rp")[1];
                                                sendPrivateMsg(username);
                                            });
                                        }
                                        // $('#chatroom').append('<p style="color: green;">[' + getDateString() + '] <i>' + participants[username] + ' joined</i></p>');
                                        if ($('#chatroom').get(0) !== undefined) {
                                            $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                                        }
                                    } else if (what === "leave") {
                                        // Somebody left
                                        var username = json["username"];
                                        var when = new Date();
                                        $('#rp' + username).remove();
                                        // $('#chatroom').append('<p style="color: green;">[' + getDateString() + '] <i>' + participants[username] + ' left</i></p>');
                                        $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                                        delete participants[username];
                                    } else if (what === "kicked") {
                                        // Somebody was kicked
                                        var username = json["username"];
                                        var when = new Date();
                                        $('#rp' + username).remove();
                                        $('#chatroom').append('<p style="color: green;">[' + getDateString() + '] <i>' + participants[username] + ' was kicked from the room</i></p>');
                                        $('#chatroom').get(0).scrollTop = $('#chatroom').get(0).scrollHeight;
                                        delete participants[username];
                                        if (username === myClientChatId) {
                                            bootbox.alert("You have been kicked from the room", function () {
                                                // $.ajax({
                                                //     method: 'POST',
                                                //     dataType: 'json',
                                                //     url: '/ajax/refresh-user-situation',
                                                //     data: {userId: siteUser.id},
                                                //     async: false,
                                                //     success: function (data) {
                                                //        window.location.reload(true);
                                                //     }
                                                // });
                                            });
                                        }
                                    } else if (what === "destroyed") {
                                        if (json["room"] !== myChatRoom)
                                            return;
                                        // Room was destroyed, goodbye!
                                        Janus.warn("The room has been destroyed!");
                                        bootbox.alert("The room has been destroyed", function () {
                                            // $.ajax({
                                            //     method: 'POST',
                                            //     dataType: 'json',
                                            //     url: '/ajax/refresh-user-situation',
                                            //     data: {userId: siteUser.id},
                                            //     async: false,
                                            //     success: function (data) {
                                            //        window.location.reload(true);
                                            //     }
                                            // });
                                        });
                                    }
                                },
                                oncleanup: function () {
                                    //Janus.log(" ::: Got a cleanup notification :::");
                                    $('#sendMessage').attr('disabled', true);
                                }
                            });
                    },
                    error: function (error) {
                        Janus.error(error);
                        // bootbox.alert(error, function () {
                        //     $.ajax({
                        //         method: 'POST',
                        //         dataType: 'json',
                        //         url: '/ajax/refresh-user-situation',
                        //         data: {userId: userBlockChat.data('user')},
                        //         success: function (data) {
                        //             window.location.reload(true);
                        //         }
                        //     });
                        // });
                    },
                    destroyed: function () {
                        // $.ajax({
                        //     method: 'POST',
                        //     dataType: 'json',
                        //     url: '/ajax/refresh-user-situation',
                        //     data: {userId: siteUser.id},
                        //     async: false,
                        //     success: function (data) {
                        //         //    window.location.reload(true);
                        //     }
                        // });
                    }
                });

            // });
        }
    });

    janusClientChat.sendData = function () {
        var data = $('#inputMessageChat').val();

        if ((data == null) || (data == undefined) || (data == '')) return;

        this.sendMsg(data);
    };

    janusClientChat.sendReloadMsg = function () {
        this.sendMsg("SYSTEM_reload_SYSTEM");
    };

    janusClientChat.sendMsg = function (data) {
        if (!data) return;

        var message = {
            textroom: "message",
            transaction: randomString(12),
            room: myChatRoom,
            text: data
        };

        textRoomClient.data({
            text: JSON.stringify(message),
            error: function (reason) {
                bootbox.alert(reason);
            },
            success: function () {

                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        message: data,
                        userId: siteUser.id,
                        courseId: $('#courseId').val(),
                        roomId: myChatRoom
                    },
                    url: '/ajax/send-message',
                    success: function () {
                        $('#inputMessageChat ').focus();
                    }
                });

                $('#inputMessageChat').val('');
            }
        });
    };

    // Кол-во пользователей в системе
    //setInterval(
    //    function () {
    //        $.ajax({
    //            method: 'POST',
    //            dataType: 'json',
    //            data: {},
    //            url: '/ajax/calc-active-user',
    //            success: function (data) {
    //                $('#nowOnline').text(data.length);
    //            }
    //        });
    //    }
    //    , 2000
    //);

});
