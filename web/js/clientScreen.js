const serverScreen = "https://" + window.location.hostname + "/janus";
const userBlockScreen = $('#userBlock');
const roleScreen = "listener";
const patronymicScreen = userBlockScreen.data('patronymic');
const surnameScreen = userBlockScreen.data('surname');
const nameScreen = userBlockScreen.data('name');
const myUserNameScreen = surnameScreen + '' + nameScreen + '' + patronymicScreen;
const screenUserId = userBlockScreen.data('user');


let roomScreen = null;
let opaqueIScreen = null;
let janusScreen = null;
let handleScreen = null;
let spinnerScreen = null;
let captureScreen = "screen";

let screenStarted = false;

$(document).ready(function () {

    let currentRoomScreen = $('#currentRoomScreen').val();
    let opaqueIScreen = $('#opaqueIdScreen').val();

    if (!((currentRoomScreen == undefined) && (currentRoomScreen == null) && (opaqueIScreen == undefined) && (opaqueIScreen == null))) {
        startJanusForScreen(Number(currentRoomScreen), String(opaqueIScreen));
    } else {
        // чтобы наверника, лол xD
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: '/ajax/get-server-room',
            success: function (data) {
                // Janus работает только с не негативным интеджером :)
                startJanusForScreen(Number(currentRoomScreen), String(opaqueIScreen));
            }
        });
    }
});

function newremoteFeedScreenScreen(id, display) {

    if (display != 'screen') return;

    id = Number(id);
    opaqueIScreen = String(opaqueIScreen);

    // A new feed has been published, create a new plugin handle and attach to it as a listener
    //source = id;
    var remoteFeedScreen = null;
    janusScreen.attach(
        {
            plugin: "janus.plugin.videoroom",
            opaqueId: opaqueIScreen,
            success: function (pluginHandle) {
                remoteFeedScreen = pluginHandle;
                Janus.log("Plugin attached! (" + remoteFeedScreen.getPlugin() + ", id=" + remoteFeedScreen.getId() + ")");
                Janus.log("  -- This is a subscriber");
                // We wait for the plugin to send us an offer
                var listen = {"request": "join", "room": roomScreen, "ptype": "listener", "feed": id};
                remoteFeedScreen.send({"message": listen});
            },
            error: function (error) {
                Janus.error("  -- Error attaching plugin...", error);
                bootbox.alert("Error attaching plugin... " + error);
            },
            onmessage: function (msg, jsep) {
                Janus.debug(" ::: Got a message (listener) :::");
                Janus.debug(msg);
                var event = msg["videoroom"];
                Janus.debug("Event: " + event);
                if (event != undefined && event != null) {
                    if (event === "attached") {
                        // Subscriber created and attached
                        if (spinnerScreen === undefined || spinnerScreen === null) {
                            var target = document.getElementById('#screencapture');
                            spinnerScreen = new Spinner({top: 100}).spin(target);
                        } else {
                            spinnerScreen.spin();
                        }
                        Janus.log("Successfully attached to feed " + id + " (" + display + ") in room " + msg["room"]);
                    } else {
                        // What has just happened?
                    }
                }
                if (jsep !== undefined && jsep !== null) {
                    Janus.debug("Handling SDP as well...");
                    Janus.debug(jsep)
                    //  Answer and attach
                    remoteFeedScreen.createAnswer(
                        {
                            jsep: jsep,
                            media: {audioSend: false, videoSend: false},	// We want recvonly audio/video
                            success: function (jsep) {
                                Janus.debug("Got SDP!");
                                Janus.debug(jsep);
                                var body = {"request": "start", "room": roomScreen};
                                remoteFeedScreen.send({"message": body, "jsep": jsep});
                            },
                            error: function (error) {
                                Janus.error("WebRTC error:", error);
                                bootbox.alert("WebRTC error... " + error);
                            }
                        });
                    setInterval(
                        function () {
                            if (remoteFeedScreen.getBitrate() == 'Invalid PeerConnection') {
                                $.ajax({
                                    method: 'POST',
                                    dataType: 'json',
                                    url: '/ajax/refresh-user-situation',
                                    data: {userId: screenUserId},
                                    success: function (data) {
                                        window.location.reload();
                                    }
                                });
                            }

                            //  $('#bit2').html(remoteFeedScreen.getBitrate());
                        }, 1000
                    );
                }
            },
            onlocalstream: function (stream) {
                //console.log("onlocalstream");
            },
            onremotestream: function (stream) {

                if (!screenStarted) {

                    screenStarted = true;
                    Janus.attachMediaStream($('#screenvideo2').get(0), stream);

                    var screenVideoJs = videojs('#screenvideo2', {
                        controls: true,
                        audio: false,
                        volume: 0,
                        autoplay: true,
                        preload: 'auto',
                        width: '100%',
                        aspectRatio: "16:9"
                    });

                    screenVideoJs.on('ready', function () {
                        this.play();
                    });

                    screenVideoJs.on('playing', function () {
                        $('#videos2').show();
                        $('#videos2 .vjs-volume-menu-button').click();
                        $('#loadingClient').hide();
                        $('#screenvideo2 .vjs-volume-menu-button').hide();
                    });

                    var fullScreen = $('#screenvideo2 .vjs-control-bar');
                    var leftSide = $('.left-side');
                    var rightSide = $('.right-side');
                    var topVideo = $('.top-video');
                    var bottomVideo = $('.bottom-video');
                    var bottomVideoContainer = $('#videos-container');
                    var bRow = $('.b-row');
                    var rightTable = $('.right-table');
                    var tabContent = $('.tab-content');

                    //console.log(fullScreen);

                    fullScreen.append("<span class='full-screen'></span>");

                    function scrollButton() {
                        var chat_scroll = $('.tab-content-wrapper');
                        chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
                    }

                    var fsBtn = $('.full-screen');

                    fsBtn.on('click', function () {

                        setTimeout(function () {
                            scrollButton();
                        }, 1000);

                        $(this).toggleClass('full');

                        if (fsBtn.hasClass('full')) {

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
                                marginTop: '20px'
                            });

                            rightSide.css('display', 'table-row');

                            leftSide.removeClass('col-sm-8 col-lg-8 col-md-8');
                            rightSide.removeClass('col-sm-4 col-lg-4 col-md-4');

                            leftSide.addClass('col-sm-12 col-lg-12 col-md-12');
                            rightSide.addClass('col-sm-12 col-lg-12 col-md-12');

                            var $wrapp = $('<div class="video-chat-wrapper2"/>');
                            var $bRow = $('<div class="b-row2"/>');


                            bottomVideoContainer.append($wrapp);
                            $wrapp.append($bRow);
                            $wrapp.append(rightSide);

                            rightTable.css({
                                height: '500',
                                display: 'flex',
                                flexDirection: 'column'
                            });
                            tabContent.css({
                                height: '500',
                                display: 'flex',
                                flexDirection: 'column'
                            });

                        } else {

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

                            leftSide.css('display', 'table-cell');
                            rightSide.css('display', 'table-cell');

                            leftSide.addClass('col-sm-8 col-lg-8 col-md-8');
                            rightSide.addClass('col-sm-4 col-lg-4 col-md-4');

                            leftSide.removeClass('col-sm-12 col-lg-12 col-md-12');
                            rightSide.removeClass('col-sm-12 col-lg-12 col-md-12');

                            bRow.append(rightSide);

                            rightTable.css({
                                display: 'table',
                                height: '100%'
                            });

                            tabContent.css({
                                height: '100%',
                                display: 'table-row',
                                border: '0px solid #000000'
                            });
                        }

                    });

                    var chatBlock = $('#chatBlock');
                    chatBlock.scrollTop(chatBlock.height());
                    $('#inputMessageChat ').focus();

                }
            },

            oncleanup: function () {
                Janus.log(" ::: Got a cleanup notification (remote feed " + id + ") :::");
                //  $('#waitingvideo').remove();
                if (spinnerScreen !== null && spinnerScreen !== undefined)
                    spinnerScreen.stop();
                spinnerScreen = null;
            }
        });
}


function startJanusForScreen(currentRoomScreen) {

    roomScreen = Number(currentRoomScreen);

    Janus.init({
        debug: true,
        dependencies: Janus.useDefaultDependencies(),
        callback: function () {
            janusScreen = new Janus(
                {
                    server: serverScreen,
                    success: function () {

                        janusScreen.attach(
                            {
                                plugin: "janus.plugin.videoroom",
                                success: function (pluginHandle) {

                                    handleScreen = pluginHandle;

                                    var register = {
                                        "request": "join",
                                        "room": roomScreen,
                                        "ptype": "publisher",
                                        "display": myUserNameScreen
                                    };

                                    handleScreen.send({
                                        "message": register,
                                        success: function (result) {
                                            console.log(result);
                                        }
                                    });

                                },

                                onmessage: function (msg, jsep) {

                                    var event = msg["videoroom"];
                                    Janus.debug("Event: " + event);

                                    if (event === "joined") {
                                        if (roleScreen === "publisher") {
                                            // This is our session, publish our stream
                                            Janus.debug("Negotiating WebRTC stream for our screen (capture " + captureScreen + ")");
                                            handleScreen.createOffer(
                                                {
                                                    media: {video: captureScreen, audioSend: false, videoRecv: false},	// Screen sharing Publishers are sendonly
                                                    success: function (jsep) {
                                                        Janus.debug("Got publisher SDP!");
                                                        Janus.debug(jsep);
                                                        var publish = {
                                                            "request": "configure",
                                                            "audio": false,
                                                            "video": true
                                                        };


                                                        handleScreen.send({"message": publish, "jsep": jsep});
                                                    },
                                                    error: function (error) {
                                                        Janus.error("WebRTC error:", error);
                                                        bootbox.alert("WebRTC error... " + JSON.stringify(error));
                                                    }
                                                });


                                        } else {

                                            // We're just watching a session, any feed to attach to?
                                            if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                var list = msg["publishers"];

                                                Janus.debug("Got a list of available publishers/feeds:");
                                                Janus.debug(list);
                                                for (var f in list) {
                                                    var id = list[f]["id"];
                                                    var display = list[f]["display"];
                                                    Janus.debug("  >> [" + id + "] " + display);
                                                    newremoteFeedScreenScreen(id, display)
                                                }
                                            }
                                        }
                                    }
                                    else if (event === "event") {
                                        // Any feed to attach to?
                                        if (roleScreen === "listener" && msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                            var list = msg["publishers"];
                                            Janus.debug("Got a list of available publishers/feeds:");
                                            Janus.debug(list);
                                            for (var f in list) {
                                                var id = list[f]["id"];

                                                var display = list[f]["display"];

                                                Janus.debug("  >> [" + id + "] " + display);
                                                newremoteFeedScreenScreen(id, display)
                                            }
                                        } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                            // One of the publishers has gone away?
                                            var leaving = msg["leaving"];
                                            Janus.log("Publisher left: " + leaving);
                                            /*if (roleScreen === "listener" && msg["leaving"] === source) {
                                             bootbox.alert("The screen sharing session is over, the publisher left", function () {
                                             window.location.reload();
                                             });
                                             }*/
                                        } else if (msg["error"] !== undefined && msg["error"] !== null) {
                                            bootbox.alert(msg["error"]);
                                        }
                                    }

                                    // Handle msg, if needed, and check jsep
                                    if (jsep !== undefined && jsep !== null) {
                                        // We have the ANSWER from the plugin
                                        handleScreen.handleRemoteJsep({jsep: jsep});
                                    }
                                },
                                onlocalstream: function (stream) {
                                    console.log(stream);
                                    console.log('++++++++++++');

                                },
                                onremotestream: function (stream) {
                                    console.log(stream);
                                    console.log('----------');

                                }
                            });
                    },
                    error: function (cause) {
                        console.log(cause);
                        console.log('error');
                    },
                    destroyed: function () {
                        // I should get rid of this
                    }
                });
        }
    });
}
