var server = "https://" + window.location.hostname + "/janus";

var janus = null;
var sfutest = null;

var opaqueId = "videoroomtest-" + Janus.randomString(12);
//var opaqueId = "screensharingtest-" + Janus.randomString(12);

var myRoomCamera = 1234;	// Demo room
var cameraUserName = null;
var cameraId = null;
var cameraStream = null;

var cameraCapture = null;

// We use this other ID just to map our subscriptions to us
var mypvtid = null;
var feeds = [];
var bitrateTimer = [];
var role = null;

var isFirstDiv = true;

var doSimulcast = (getQueryStringValue("simulcast") === "yes" || getQueryStringValue("simulcast") === "true");

function checkEnter(field, event) {
    var theCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if (theCode == 13) {
        registerCameraUserName();
        return false;
    } else {
        return true;
    }
}

function registerCameraUserName() {

    let patronymic = $('#userBlock').data('patronymic');
    let surname = $('#userBlock').data('surname');
    let name = $('#userBlock').data('name');

    cameraUserName = surname + "_" + patronymic + '_' + name + '-camera';
}

function publishOwnFeedCamera(useAudio) {

    var data = new Date();
    var d = {
        day: data.getDay(),
        month: (data.getMonth() + 1),
        year: data.getFullYear(),
        hour: data.getHours(),
        minute: data.getMinutes(),
        second: data.getSeconds(),
        milliseconds: data.getMilliseconds()
    };

    var D = {};
    for (var n in d) {
        D[n] = (parseInt(d[n], 10) < 10 ) ? ('0' + d[n]) : (d[n]);
    }

    var z = D.year + '-' + D.month + '-' + D.day;
    z = z + '_' + D.hour + '-' + D.minute + '-' + D.second + '-' + D.milliseconds;

    var fileName = "camera-" + z;

    sfutest.createOffer(
        {
            iceRestart: true,
            media: {
                id: 333,
                audioRecv: true,
                audioSend: useAudio,
                videoRecv: true,
                videoSend: true,
                video: 'lowres',
                myStatus: 'camera',
            },
            simulcast: doSimulcast,
            success: function (jsep) {
                Janus.debug("Got publisher SDP!");
                Janus.debug(jsep);
                var publish = {
                    "id": 333,
                    "request": "configure",
                    "audio": useAudio,
                    "video": true,
                    "record": true,
                    "filename": "/var/www/html/watermark.wrk/records-tmp/" + fileName
                };
                sfutest.send({"message": publish, "jsep": jsep});
            },
            error: function (error) {
                Janus.error("WebRTC error:", error);
                if (useAudio) {
                    publishOwnFeedCamera(false);
                } else {
                    +
                        bootbox.alert("Вы отменили трансляцию веб-камеры. Чтобы включить трансляцию - обновите текущую страницу (Ctrl+R) или нажмите F5.");
                    $('#publish').removeAttr('disabled').click(function () {
                        publishOwnFeedCamera(true);
                    });
                }
            }
        });
}

function unpublishOwnFeedCamera() {
    // Unpublish our stream
    $('#unpublish').attr('disabled', true).unbind('click');
    var unpublish = {"request": "unpublish"};
    sfutest.send({"message": unpublish});
}

function newRemoteFeed(id, display, audio, video) {
    console.log('camera: newRemoteFeed');
}

// Helper to parse query string
function getQueryStringValue(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Helpers to create Simulcast-related UI, if enabled
function addSimulcastButtons(feed) {
    var index = feed;
    $('#remote' + index).parent().append(
        '<div id="simulcast' + index + '" class="btn-group-vertical btn-group-vertical-xs pull-right">' +
        '	<div class"row">' +
        '		<div class="btn-group btn-group-xs" style="width: 100%">' +
        '			<button id="sl' + index + '-2" type="button" class="btn btn-primary" data-toggle="tooltip" title="Switch to higher quality" style="width: 33%">SL 2</button>' +
        '			<button id="sl' + index + '-1" type="button" class="btn btn-primary" data-toggle="tooltip" title="Switch to normal quality" style="width: 33%">SL 1</button>' +
        '			<button id="sl' + index + '-0" type="button" class="btn btn-primary" data-toggle="tooltip" title="Switch to lower quality" style="width: 34%">SL 0</button>' +
        '		</div>' +
        '	</div>' +
        '	<div class"row">' +
        '		<div class="btn-group btn-group-xs" style="width: 100%">' +
        '			<button id="tl' + index + '-2" type="button" class="btn btn-primary" data-toggle="tooltip" title="Cap to temporal layer 2" style="width: 34%">TL 2</button>' +
        '			<button id="tl' + index + '-1" type="button" class="btn btn-primary" data-toggle="tooltip" title="Cap to temporal layer 1" style="width: 33%">TL 1</button>' +
        '			<button id="tl' + index + '-0" type="button" class="btn btn-primary" data-toggle="tooltip" title="Cap to temporal layer 0" style="width: 33%">TL 0</button>' +
        '		</div>' +
        '	</div>' +
        '</div>'
    );
    // Enable the VP8 simulcast selection buttons
    $('#sl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Switching simulcast substream, wait for it... (lower quality)", null, {timeOut: 2000});
            if (!$('#sl' + index + '-2').hasClass('btn-success'))
                $('#sl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
            if (!$('#sl' + index + '-1').hasClass('btn-success'))
                $('#sl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
            $('#sl' + index + '-0').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
            feeds[index].send({message: {request: "configure", substream: 0}});
        });
    $('#sl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Switching simulcast substream, wait for it... (normal quality)", null, {timeOut: 2000});
            if (!$('#sl' + index + '-2').hasClass('btn-success'))
                $('#sl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
            $('#sl' + index + '-1').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
            if (!$('#sl' + index + '-0').hasClass('btn-success'))
                $('#sl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
            feeds[index].send({message: {request: "configure", substream: 1}});
        });
    $('#sl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Switching simulcast substream, wait for it... (higher quality)", null, {timeOut: 2000});
            $('#sl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
            if (!$('#sl' + index + '-1').hasClass('btn-success'))
                $('#sl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
            if (!$('#sl' + index + '-0').hasClass('btn-success'))
                $('#sl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
            feeds[index].send({message: {request: "configure", substream: 2}});
        });
    $('#tl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Capping simulcast temporal layer, wait for it... (lowest FPS)", null, {timeOut: 2000});
            if (!$('#tl' + index + '-2').hasClass('btn-success'))
                $('#tl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
            if (!$('#tl' + index + '-1').hasClass('btn-success'))
                $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
            $('#tl' + index + '-0').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
            feeds[index].send({message: {request: "configure", temporal: 0}});
        });
    $('#tl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Capping simulcast temporal layer, wait for it... (medium FPS)", null, {timeOut: 2000});
            if (!$('#tl' + index + '-2').hasClass('btn-success'))
                $('#tl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
            $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-info');
            if (!$('#tl' + index + '-0').hasClass('btn-success'))
                $('#tl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
            feeds[index].send({message: {request: "configure", temporal: 1}});
        });
    $('#tl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary')
        .unbind('click').click(function () {
            toastr.info("Capping simulcast temporal layer, wait for it... (highest FPS)", null, {timeOut: 2000});
            $('#tl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
            if (!$('#tl' + index + '-1').hasClass('btn-success'))
                $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
            if (!$('#tl' + index + '-0').hasClass('btn-success'))
                $('#tl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
            feeds[index].send({message: {request: "configure", temporal: 2}});
        });
}

function updateSimulcastButtons(feed, substream, temporal) {
    // Check the substream
    var index = feed;
    if (substream === 0) {
        toastr.success("Switched simulcast substream! (lower quality)", null, {timeOut: 2000});
        $('#sl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#sl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#sl' + index + '-0').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
    } else if (substream === 1) {
        toastr.success("Switched simulcast substream! (normal quality)", null, {timeOut: 2000});
        $('#sl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#sl' + index + '-1').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
        $('#sl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary');
    } else if (substream === 2) {
        toastr.success("Switched simulcast substream! (higher quality)", null, {timeOut: 2000});
        $('#sl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
        $('#sl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#sl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary');
    }
    // Check the temporal layer
    if (temporal === 0) {
        toastr.success("Capped simulcast temporal layer! (lowest FPS)", null, {timeOut: 2000});
        $('#tl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#tl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#tl' + index + '-0').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
    } else if (temporal === 1) {
        toastr.success("Capped simulcast temporal layer! (medium FPS)", null, {timeOut: 2000});
        $('#tl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#tl' + index + '-1').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
        $('#tl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary');
    } else if (temporal === 2) {
        toastr.success("Capped simulcast temporal layer! (highest FPS)", null, {timeOut: 2000});
        $('#tl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-success');
        $('#tl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary');
        $('#tl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary');
    }
}

function preshareCamera() {
    // Make sure HTTPS is being used

    if (!Janus.isExtensionEnabled()) {
        bootbox.alert("You're using a recent version of Chrome but don't have the screensharing extension installed: click <b><a href='https://chrome.google.com/webstore/detail/janus-webrtc-screensharin/hapfgfdkleiggjjpfpenajgdnfckjpaj' target='_blank'>here</a></b> to do so", function () {
            window.location.reload();
        });
        return;
    }

    cameraCapture = "screen";
    if (navigator.mozGetUserMedia) {
        // Firefox needs a different constraint for screen and window sharing
        bootbox.dialog({
            title: "Транслировать веб-камеру?",
            message: "Веб-браузер Firefox предоставляет возможность выбора оборудования для трансляции.",
            buttons: {
                window: {
                    label: "Выбрать окно",
                    className: "btn-success",
                    callback: function () {
                        cameraCapture = "window";
                        shareCamera();
                    }
                }
            },
            onEscape: function () {
                bootbox.alert("Вы отменили трансляцию веб-камеры. Чтобы включить трансляцию - обновите текущую страницу (Ctrl+R) или нажмите F5.");
            }
        });
    } else {
        shareCamera();
    }
}

function shareCamera() {

    registerCameraUserName();

    var register = {"request": "join", "room": Number(myRoomCamera), "ptype": "publisher", "display": 'camera'};
    sfutest.send({"message": register});


    // Create a new room
    /*var desc = myRoomCamera + 'camera';
     role = "publisher";
     var create = {"request": "create", "description": desc, "bitrate": 10, "publishers": 1};

     sfutest.send({
     "message": create, success: function (result) {
     var event = result["videoroom"];
     Janus.debug("Event: " + event);
     if (event != undefined && event != null) {

     let roomTmp = result["room"];

     Janus.log("Screen sharing session created: " + roomTmp);
     cameraUserName = randomString(12);
     var register = {"request": "join", "room": roomTmp, "ptype": "publisher", "display": cameraUserName};
     sfutest.send({"message": register});
     }
     }
     });*/
}

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

    Janus.init({
        debug: true,
        dependencies: Janus.useDefaultDependencies(), // or: Janus.useOldDependencies() to get the behaviour of previous Janus versions
        callback: function () {

            $(this).attr('disabled', true).unbind('click');
            // Make sure the browser supports WebRTC
            if (!Janus.isWebrtcSupported()) {
                bootbox.alert("No WebRTC support... ");
                return;
            }

            // Create session
            janus = new Janus(
                {
                    server: server,
                    success: function () {
                        // Attach to video room test plugin
                        janus.attach(
                            {
                                plugin: "janus.plugin.videoroom",
                                opaqueId: opaqueId,
                                success: function (pluginHandle) {

                                    janus.streaming = pluginHandle;

                                    sfutest = pluginHandle;
                                    Janus.log("Plugin attached! (" + sfutest.getPlugin() + ", id=" + sfutest.getId() + ")");
                                    Janus.log("  -- This is a publisher/manager");

                                    let list = {
                                        "request": "list"
                                        // "room": roomCamera,
                                    };

                                    pluginHandle.send({
                                        "message": list,
                                        success: function (result) {
                                            //
                                        }
                                    });

                                    preshareCamera();
                                },
                                error: function (error) {
                                    Janus.error("  -- Error attaching plugin...", error);
                                    bootbox.alert("Error attaching plugin... " + error);
                                },
                                consentDialog: function (on) {
                                    Janus.debug("Consent dialog shouldup_arrow be " + (on ? "on" : "off") + " now");
                                    if (on) {
                                        // Darken screen and show hint
                                        $.blockUI({
                                            message: '<div></div>',
                                            css: {
                                                border: 'none',
                                                padding: '15px',
                                                backgroundColor: 'transparent',
                                                color: '#aaa',
                                                top: '10px',
                                                left: (navigator.mozGetUserMedia ? '-100px' : '300px')
                                            }
                                        });
                                    } else {
                                        // Restore screen
                                        $.unblockUI();
                                    }
                                },
                                mediaState: function (medium, on) {
                                    Janus.log("Janus " + (on ? "started" : "stopped") + " receiving our " + medium);
                                },
                                webrtcState: function (on) {
                                    Janus.log("Janus says our WebRTC PeerConnection is " + (on ? "up" : "down") + " now");
                                    $("#videolocal").parent().parent().unblock();
                                    // This controls allows us to override the global room bitrate cap
                                    $('#bitrate').parent().parent().removeClass('hide').show();

                                    $('#bitrateset').click(function () {
                                        $('#bitrate').show();
                                    });

                                    $('#bitrate a').click(function () {
                                        var id = $(this).attr("id");
                                        var bitrate = parseInt(id) * 1000;
                                        if (bitrate === 0) {
                                            Janus.log("Not limiting bandwidth via REMB");
                                        } else {
                                            Janus.log("Capping bandwidth to " + bitrate + " via REMB");
                                        }
                                        $('#bitrateset').html($(this).html() + '<span class="caret"></span>').parent().removeClass('open');
                                        sfutest.send({"message": {"request": "configure", "bitrate": bitrate}});
                                        $('#bitrate').hide();
                                        return false;
                                    });

                                },
                                onmessage: function (msg, jsep) {

                                    var tmpRoom = msg['room'];

                                    $.ajax({
                                        method: 'POST',
                                        dataType: 'json',
                                        data: {
                                            room: tmpRoom,
                                            opaqueId: opaqueId
                                        },
                                        url: '/ajax/server-camera-room-save',
                                        success: function (data) {
                                            //
                                        }
                                    });

                                    Janus.debug(" ::: Got a message (publisher) :::");
                                    Janus.debug(msg);
                                    var event = msg["videoroom"];
                                    Janus.debug("Event: " + event);

                                    if (event != undefined && event != null) {

                                        // alert(event);

                                        if (event === "joined") {

                                            // Publisher/manager created, negotiate WebRTC and attach to existing feeds, if any
                                            cameraId = msg["id"];
                                            mypvtid = msg["private_id"];
                                            Janus.log("Successfully joined room " + msg["room"] + " with ID " + cameraId);

                                            publishOwnFeedCamera(true);

                                            // Any new feed to attach to?
                                            if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                var list = msg["publishers"];

                                                Janus.debug("Got a list of available publishers/feeds:");
                                                Janus.debug(list);

                                                for (var f in list) {
                                                    var id = list[f]["id"];
                                                    var display = list[f]["display"];
                                                    var audio = list[f]["audio_codec"];
                                                    var video = list[f]["video_codec"];
                                                    Janus.debug("  >> [" + id + "] " + display + " (audio: " + audio + ", video: " + video + ")");

                                                    newRemoteFeed(id, display, audio, video);
                                                }
                                                // console.log('start123');
                                            }

                                        } else if (event === "destroyed") {
                                            // The room has been destroyed
                                            Janus.warn("The room has been destroyed!");
                                            bootbox.alert("The room has been destroyed", function () {
                                                window.location.reload();
                                            });
                                        } else if (event === "event") {

                                            // Any new feed to attach to?
                                            if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                var list = msg["publishers"];
                                                Janus.debug("Got a list of available publishers/feeds:");
                                                Janus.debug(list);
                                                for (var f in list) {
                                                    var id = list[f]["id"];
                                                    var display = list[f]["display"];
                                                    var audio = list[f]["audio_codec"];
                                                    var video = list[f]["video_codec"];
                                                    Janus.debug("  >> [" + id + "] " + display + " (audio: " + audio + ", video: " + video + ")");
                                                    newRemoteFeed(id, display, audio, video);
                                                }
                                            } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                                // One of the publishers has gone away?
                                                var leaving = msg["leaving"];
                                                Janus.log("Publisher left: " + leaving);
                                                var remoteFeed = null;
                                                for (var i = 1; i < 6; i++) {
                                                    if (feeds[i] != null && feeds[i] != undefined && feeds[i].rfid == leaving) {
                                                        remoteFeed = feeds[i];
                                                        break;
                                                    }
                                                }
                                                if (remoteFeed != null) {
                                                    Janus.debug("Feed " + remoteFeed.rfid + " (" + remoteFeed.rfdisplay + ") has left the room, detaching");
                                                    $('#remote' + remoteFeed.rfindex).empty().hide();
                                                    $('#videoremote' + remoteFeed.rfindex).empty();
                                                    feeds[remoteFeed.rfindex] = null;
                                                    remoteFeed.detach();
                                                }
                                            } else if (msg["unpublished"] !== undefined && msg["unpublished"] !== null) {
                                                // One of the publishers has unpublished?
                                                var unpublished = msg["unpublished"];
                                                Janus.log("Publisher left: " + unpublished);
                                                if (unpublished === 'ok') {
                                                    // That's us
                                                    sfutest.hangup();
                                                    return;
                                                }
                                                var remoteFeed = null;
                                                for (var i = 1; i < 6; i++) {
                                                    if (feeds[i] != null && feeds[i] != undefined && feeds[i].rfid == unpublished) {
                                                        remoteFeed = feeds[i];
                                                        break;
                                                    }
                                                }
                                                if (remoteFeed != null) {
                                                    Janus.debug("Feed " + remoteFeed.rfid + " (" + remoteFeed.rfdisplay + ") has left the room, detaching");
                                                    $('#remote' + remoteFeed.rfindex).empty().hide();
                                                    $('#videoremote' + remoteFeed.rfindex).empty();
                                                    feeds[remoteFeed.rfindex] = null;
                                                    remoteFeed.detach();
                                                }
                                            } else if (msg["error"] !== undefined && msg["error"] !== null) {
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
                                        }
                                    }

                                    if (jsep !== undefined && jsep !== null) {

                                        Janus.debug("Handling SDP as well...");
                                        Janus.debug(jsep);

                                        sfutest.handleRemoteJsep({jsep: jsep});

                                    }
                                },
                                onlocalstream: function (stream) {

                                    Janus.debug(" ::: Got a local stream :::");
                                    cameraStream = stream;
                                    Janus.debug(stream);

                                    if (isFirstDiv) {

                                        setInterval(
                                            function () {
                                                "use strict";

                                                $.ajax({
                                                    method: 'POST',
                                                    dataType: 'json',
                                                    url: '/ajax/server-camera-room-save',
                                                    success: function (data) {
                                                        //
                                                    }
                                                });

                                            }, 1000
                                        );

                                        if ($('#myvideo').length === 0) {
                                            $('#videolocal').append('<video class="rounded centered video-js" id="myvideo" width="100%" height="100%"  muted="muted"/>');
                                            // Add a 'mute' button
                                            //   $('#videolocal').append('<button class="btn btn-warning btn-xs" id="mute" style="position: absolute; bottom: 0px; left: 0px; margin: 15px;">Mute</button>');
                                            //    $('#mute').click(toggleMute);
                                            // Add an 'unpublish' button
                                            //$('#videolocal').append('<button class="btn btn-warning btn-xs" id="unpublish" style="position: absolute; bottom: 0px; right: 0px; margin: 15px;">Unpublish</button>');
                                            //$('#unpublish').click(unpublishOwnFeedCamera);
                                        }

                                        $('#publisher').removeClass('hide').html(cameraUserName).show();
                                        Janus.attachMediaStream($('#myvideo').get(0), stream);
                                        $("#myvideo").get(0).muted = "muted";

                                        if (sfutest.webrtcStuff.pc.iceConnectionState !== "completed" &&
                                            sfutest.webrtcStuff.pc.iceConnectionState !== "connected") {
                                            $("#videolocal").parent().parent().block({
                                                message: '<b>Publishing...</b>',
                                                css: {
                                                    border: 'none',
                                                    backgroundColor: 'transparent',
                                                    color: 'white'
                                                }
                                            });
                                        }

                                        var videoTracks = stream.getVideoTracks();
                                        if (videoTracks === null || videoTracks === undefined || videoTracks.length === 0) {
                                            // No webcam
                                            $('#myvideo').hide();
                                            if ($('#videolocal .no-video-container').length === 0) {
                                                $('#videolocal').append(
                                                    '<div class="no-video-container">' +
                                                    '<i class="fa fa-video-camera fa-5 no-video-icon"></i>' +
                                                    '<span class="no-video-text">No webcam available</span>' +
                                                    '</div>');
                                            }
                                        } else {
                                            $('#videolocal .no-video-container').remove();
                                            $('#myvideo').removeClass('hide').show();
                                        }

                                        isFirstDiv = false;

                                        setTimeout(
                                            function () {
                                                let teacherCameraVideoJs = videojs('#myvideo', {
                                                    controls: true,
                                                    autoplay: true,
                                                    preload: 'auto',
                                                    width: '200%'
                                                });

                                                setTimeout(
                                                    function () {
                                                        teacherCameraVideoJs.play();

                                                        $('#myvideo .vjs-volume-menu-button').hide();

                                                        setTimeout(
                                                            function () {
                                                                $('#bitrate li:nth-child(3) a').click();
                                                            }, 1000
                                                        );

                                                    }, 350
                                                );
                                            }, 10);
                                    }
                                },
                                onremotestream: function (stream) {
                                    // The publisher stream is sendonly, we don't expect anything here
                                },
                                oncleanup: function () {
                                    Janus.log(" ::: Got a cleanup notification: we are unpublished now :::");
                                    cameraStream = null;
                                    $('#videolocal').html('<button id="publish" class="btn btn-primary">Publish</button>');

                                    $('#publish').click(function () {
                                        publishOwnFeedCamera(true);
                                    });

                                    $("#videolocal").parent().parent().unblock();
                                    $('#bitrate').parent().parent().addClass('hide');
                                    $('#bitrate a').unbind('click');
                                }
                            });
                    },
                    error: function (error) {
                        Janus.error(error);
                        bootbox.alert(error, function () {
                            window.location.reload();
                        });
                    },
                    destroyed: function () {
                        window.location.reload();
                    }
                });

            // });

        }

    });


});