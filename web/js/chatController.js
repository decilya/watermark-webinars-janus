'use strict';

var serverChat = "https://" + window.location.hostname + "/janus";
var janusClientChat = null;
var textRoomClient = null;
var opaqueChatClientId = "textroomtest-" + Janus.randomString(12);
var myChatRoom = 1234;	// Demo room
var myClientChatId = null;
var participants = {};
var transactions = {};
var userBlockChat = $('#userBlock');
var patronymicChat = userBlockChat.data('patronymic');
var surnameChat = userBlockChat.data('surname');
var nameChat = userBlockChat.data('name');
var clientChatUserName = surnameChat + ' ' + nameChat + ' ' + patronymicChat;
var janusClientChat = {};

function setChatPresents(participants) {

    let tmpSet = new Set();

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

janusClientChat.sendData = function () {
    var data = $('#inputMessageChat').val();
    if ((data == "") || (data == null) || (data == undefined)) {
        return;
    }

    this.sendMsg(data);
};

janusClientChat.sendReloadMsg = function () {
    "use strict";

    var data = "SYSTEM_reload_SYSTEM";

    this.sendMsg(data);
};


$('#sendMessage').click(function () {
    janusClientChat.sendData();
});


function checkMsg(msg) {

    setTimeout(function () {
        var chat_scroll = $('.tab-content-wrapper');
        chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
    }, 1000);

    if (msg == "SYSTEM_reload_SYSTEM") {
        if ($('.main-page').length) {
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/refresh-user-situation',
                data: {userId: userBlockChat.data('user')},
                success: function (data) {
                    window.location.reload();
                }
            });
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

            // Something went wrong
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/refresh-user-situation',
                data: {userId: cameraUserId},
                success: function (data) {
                    window.location.reload();
                }
            });

            return;
        }

        // We're in
        $('#roomjoin').hide();
        $('#room').removeClass('hide').show();
        $('#participant').removeClass('hide').html(clientChatUserName).show();
        $('#chatroom').css('height', ($(window).height() - 420) + "px");
        $('#sendMessage').removeAttr('disabled');
        // Any participants already in?
        console.log("Participants:", response.participants);
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
    if (!display)
        return;
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

    return;
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
                                    Janus.log("Plugin attached! (" + textRoomClient.getPlugin() + ", id=" + textRoomClient.getId() + ")");
                                    // Setup the DataChannel
                                    var body = {"request": "setup"};
                                    Janus.debug("Sending message present(" + JSON.stringify(body) + ")");
                                    textRoomClient.send({"message": body});

                                    setTimeout(
                                        function () {
                                            setChatPresents(participants);
                                        }, 300
                                    );

                                    setInterval(
                                        function () {
                                            "use strict";
                                            setChatPresents(participants);
                                        }, 2500
                                    );

                                },
                                error: function (error) {
                                    console.error("  -- Error attaching plugin...", error);
                                    bootbox.alert("Error attaching plugin... " + error);
                                },
                                webrtcState: function (on) {
                                    Janus.log("Janus says our WebRTC PeerConnection is " + (on ? "up" : "down") + " now");
                                    //$("#videoleft").parent().unblock();
                                },
                                onmessage: function (msg, jsep) {
                                    Janus.debug(" ::: Got a message :::");
                                    Janus.debug(msg);

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
                                                    Janus.debug("Got SDP!");
                                                    Janus.debug(jsep);
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
                                    Janus.log("The DataChannel is available!");
                                    registerChatUserName();
                                },
                                ondata: function (data) {
                                    //SYSTEM_reload_SYSTEM

                                    Janus.debug("We got data from the DataChannel! " + data);
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
                                                $.ajax({
                                                    method: 'POST',
                                                    dataType: 'json',
                                                    url: '/ajax/refresh-user-situation',
                                                    data: {userId: userBlockChat.data('user')},
                                                    success: function (data) {
                                                        window.location.reload();
                                                    }
                                                });
                                            });
                                        }
                                    } else if (what === "destroyed") {
                                        if (json["room"] !== myChatRoom)
                                            return;
                                        // Room was destroyed, goodbye!
                                        Janus.warn("The room has been destroyed!");
                                        bootbox.alert("The room has been destroyed", function () {
                                            $.ajax({
                                                method: 'POST',
                                                dataType: 'json',
                                                url: '/ajax/refresh-user-situation',
                                                data: {userId: userBlockChat.data('user')},
                                                success: function (data) {
                                                    window.location.reload();
                                                }
                                            });
                                        });
                                    }
                                },
                                oncleanup: function () {
                                    Janus.log(" ::: Got a cleanup notification :::");
                                    $('#sendMessage').attr('disabled', true);
                                }
                            });
                    },
                    error: function (error) {
                        Janus.error(error);
                        bootbox.alert(error, function () {
                            $.ajax({
                                method: 'POST',
                                dataType: 'json',
                                url: '/ajax/refresh-user-situation',
                                data: {userId: userBlockChat.data('user')},
                                success: function (data) {
                                    window.location.reload();
                                }
                            });
                        });
                    },
                    destroyed: function () {
                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: '/ajax/refresh-user-situation',
                            data: {userId: userBlockChat.data('user')},
                            success: function (data) {
                                window.location.reload();
                            }
                        });
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
        "use strict";

        if ((data == null) || (data == undefined) || (data == '')) return;

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
                        userId: $('#userBlock').data('user'),
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
