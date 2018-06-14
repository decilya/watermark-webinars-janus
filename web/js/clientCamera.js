var serverCamera = "https://" + window.location.hostname + "/janus";
var userBlockCamera = $('#userBlock');
var roleCamera = "listener";
var patronymicCamera = userBlockCamera.data('patronymic');
var surnameCamera = userBlockCamera.data('surname');
var nameCamera = userBlockCamera.data('name');
var myUserNameCamera = surnameCamera + ' ' + nameCamera + ' ' + patronymicCamera;
const cameraUserId = userBlockCamera.data('user');

var roomCamera = null;
var opaqueIdCamera = null;
var janusCamera = null;
var handleCamera = null;
var spinnerCamera = null;
var isFirstCamera = true;
var captureCamera = "screen";
var cameraStarted = false;

var remoteFeed = null;

$(document).ready(function () {
    //
    let currentRoomCamera = $('#currentRoomCamera').val();
    let opaqueIdCamera = $('#opaqueIdCamera').val();

    if (!((currentRoomCamera == undefined) && (currentRoomCamera == null) && (opaqueIdCamera == undefined) && (opaqueIdCamera == null))) {
        startJanusForCamera(Number(currentRoomCamera), String(opaqueIdCamera));
    } else {
        // чтобы наверника, лол xD
        $.ajax({
            method: 'POST',
            dataType: 'json',
            url: '/ajax/get-server-room',
            success: function (data) {
                // Janus работает только с не негативным интеджером :)
                startJanusForCamera(Number(currentRoomCamera), String(opaqueIdCamera));
            }
        });
    }
});

function newRemoteFeedCamera(id, display) {

    if (display != 'camera') return;

    id = Number(id);
    opaqueIdCamera = String(opaqueIdCamera);

    // A new feed has been published, create a new plugin handle and attach to it as a listener
    //source = id;
    var remoteFeed = null;
    janusCamera.attach(
        {
            plugin: "janus.plugin.videoroom",
            opaqueId: opaqueIdCamera,
            success: function (pluginHandle) {
                remoteFeed = pluginHandle;
                Janus.log("Plugin attached! (" + remoteFeed.getPlugin() + ", id=" + remoteFeed.getId() + ")");
                Janus.log("  -- This is a subscriber");
                // We wait for the plugin to send us an offer
                var listen = {"request": "join", "room": roomCamera, "ptype": "listener", "feed": id};
                remoteFeed.send({"message": listen});
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
                console.log("event: " + event);
                if (event != undefined && event != null) {
                    if (event === "attached") {
                        // Subscriber created and attached
                        if (spinnerCamera === undefined || spinnerCamera === null) {
                            //var target = document.getElementById('#screencapture');
                            // spinnerCamera = new Spinner({top: 100}).spin(target);
                        } else {
                            spinnerCamera.spin();
                        }
                        Janus.log("Successfully attached to feed " + id + " (" + display + ") in room " + msg["room"]);
                        $('#screenmenu').hide();
                        $('#room').removeClass('hide').show();
                    } else {
                        // What has just happened?
                    }
                }
                if (jsep !== undefined && jsep !== null) {
                    Janus.debug("Handling SDP as well...");
                    Janus.debug(jsep);
                    // Answer and attach
                    remoteFeed.createAnswer(
                        {
                            jsep: jsep,
                            media: {audioSend: false, videoSend: false},	// We want recvonly audio/video
                            success: function (jsep) {
                                Janus.debug("Got SDP!");
                                Janus.debug(jsep);
                                var body = {"request": "start", "room": roomCamera};
                                remoteFeed.send({"message": body, "jsep": jsep});
                            },
                            error: function (error) {
                                Janus.error("WebRTC error:", error);
                                bootbox.alert("WebRTC error... " + error);
                            }
                        });

                    setInterval(
                        function () {
                            if (remoteFeed.getBitrate() == 'Invalid PeerConnection') {
                                $.ajax({
                                    method: 'POST',
                                    dataType: 'json',
                                    url: '/ajax/refresh-user-situation',
                                    data: {userId: cameraUserId},
                                    success: function (data) {
                                        window.location.reload();
                                    }
                                });
                            }

                            //if ($('.main-page').length != 1) {
                            //    $('#bit1').html(remoteFeed.getBitrate());
                            //}
                        }, 1000
                    );
                }
            },
            onlocalstream: function (stream) {
                console.log("onlocalstream");
            },
            onremotestream: function (stream) {

                if (!cameraStarted) {
                    cameraStarted = true;

                    Janus.attachMediaStream($('#screenvideo1').get(0), stream);

                    let cameraVideoJs = videojs('#screenvideo1', {
                        controls: true,
                        autoplay: true,
                        preload: 'auto',
                        width: '200%'
                        //aspectRatio: "4:3"
                    });

                    cameraVideoJs.on('ready', function () {
                        this.play();
                    });

                    cameraVideoJs.on('playing', function () {
                        $('#videos1').show();
                        $('#videos1 .vjs-volume-menu-button').click();
                        $('#loadingClient').hide();
                    });

                }
            },
            oncleanup: function () {
                Janus.log(" ::: Got a cleanup notification (remote feed " + id + ") :::");
                // $('#waitingvideo').remove();
                if (spinnerCamera !== null && spinnerCamera !== undefined)
                    spinnerCamera.stop();
                spinnerCamera = null;
            }
        });
}

function startJanusForCamera(currentRoomCamera) {

    roomCamera = Number(currentRoomCamera);

    Janus.init({
        debug: true,
        dependencies: Janus.useDefaultDependencies(),
        callback: function () {
            janusCamera = new Janus(
                {
                    server: serverCamera,
                    success: function () {

                        janusCamera.attach(
                            {
                                plugin: "janus.plugin.videoroom",
                                success: function (pluginHandle) {

                                    handleCamera = pluginHandle;

                                    var register = {
                                        "request": "join",
                                        "room": roomCamera,
                                        "ptype": "publisher",
                                        "display": myUserNameCamera
                                    };

                                    handleCamera.send({
                                        "message": register,
                                        success: function (result) {
                                            console.log(result);
                                        }
                                    });

                                    // Постоянно проверяем состояние трансляций, если
                                    setInterval(
                                        function () {
                                            "use strict";

                                            if ($('.main-page')) {

                                                var list = {
                                                    "request": "listparticipants",
                                                    "room": myChatRoom
                                                };

                                                handleCamera.send({
                                                    "message": list,
                                                    success: function (result) {

                                                        var tmpParticipants = result['participants'];
                                                        var notPublisher = true;

                                                        if (tmpParticipants) {

                                                            if (tmpParticipants.length != undefined) {

                                                                for (var j = 0; j < tmpParticipants.length; j++) {

                                                                    if (tmpParticipants[j]['publisher'] == true) {
                                                                        notPublisher = false;
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if (notPublisher) {
                                                            $('#notStart').show();
                                                            $('#loadingClient').hide();

                                                        } else {
                                                            $('#notStart').hide();
                                                            // $('#loadingClient').hide();
                                                        }
                                                    },
                                                    error: function () {
                                                        $.ajax({
                                                            method: 'POST',
                                                            dataType: 'json',
                                                            url: '/ajax/refresh-user-situation',
                                                            data: {userId: cameraUserId},
                                                            success: function (data) {
                                                                window.location.reload();
                                                            }
                                                        });
                                                    }
                                                });

                                            }

                                        }, 2000
                                    );
                                },

                                onmessage: function (msg, jsep) {

                                    var event = msg["videoroom"];
                                    Janus.debug("Event: " + event);

                                    if (event === "joined") {
                                        if (roleCamera === "publisher") {
                                            // This is our session, publish our stream
                                            Janus.debug("Negotiating WebRTC stream for our screen (capture " + captureCamera + ")");
                                            handleCamera.createOffer(
                                                {
                                                    media: {video: captureCamera, audioSend: true, videoRecv: false},	// Screen sharing Publishers are sendonly
                                                    success: function (jsep) {
                                                        Janus.debug("Got publisher SDP!");
                                                        Janus.debug(jsep);
                                                        var publish = {
                                                            "request": "configure",
                                                            "audio": true,
                                                            "video": true
                                                        };
                                                        handleCamera.send({"message": publish, "jsep": jsep});
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
                                                    newRemoteFeedCamera(id, display)
                                                }
                                            }
                                        }
                                    }
                                    else if (event === "event") {
                                        // Any feed to attach to?
                                        if (roleCamera === "listener" && msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                            var list = msg["publishers"];
                                            Janus.debug("Got a list of available publishers/feeds:");
                                            Janus.debug(list);
                                            for (var f in list) {
                                                var id = list[f]["id"];

                                                var display = list[f]["display"];

                                                Janus.debug("  >> [" + id + "] " + display);
                                                newRemoteFeedCamera(id, display)
                                            }
                                        } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                            // One of the publishers has gone away?
                                            var leaving = msg["leaving"];
                                            Janus.log("Publisher left: " + leaving);
                                            /*if (roleCamera === "listener" && msg["leaving"] === source) {
                                             bootbox.alert("The screen sharing session is over, the publisher left", function () {
                                             window.location.reload();
                                             });
                                             }*/
                                        } else if (msg["error"] !== undefined && msg["error"] !== null) {
                                            bootbox.alert(msg["error"]);
                                        }
                                    }

                                    if (jsep) {
                                        // Handle msg, if needed, and check jsep
                                        if (jsep !== undefined && jsep !== null) {
                                            // We have the ANSWER from the plugin
                                            handleCamera.handleRemoteJsep({jsep: jsep});
                                        }
                                    }
                                },
                                onlocalstream: function (stream) {

                                },
                                onremotestream: function (stream) {

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
