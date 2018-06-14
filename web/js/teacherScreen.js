let checkStartCamera = setInterval(
    function(){

        if ($('#myvideo').length > 0){
            startTeacherScreen();
        }

    }, 500
);

function startTeacherScreen() {

    clearInterval(checkStartCamera);

    var server1 = "https://" + window.location.hostname + "/janus";

    var janus1 = null;
    var screentest = null;

    var opaqueId1 = "screensharingtest-" + Janus.randomString(12);

    var myroom1 = 1234;	// Demo room
    var myUserNameScreen = null;
    var myid1 = null;
    var mystream1 = null;

    var capture1 = null;

    var mypvtid1 = null;
    var feeds1 = [];

    let isFirstDiv1 = false;

    function checkEnter(field, event) {
        var theCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
        if (theCode == 13) {
            registerUsername1();
            return false;
        } else {
            return true;
        }
    }

    function registerUsername1() {

        let patronymic = $('#userBlock').data('patronymic');
        let surname = $('#userBlock').data('surname');
        let name = $('#userBlock').data('name');
        myUserNameScreen = surname + "_" + patronymic + '_' + name + '-screen';
    }

    function publishOwnFeedScreen(useAudio) {

        screentest.createOffer(
            {
                iceRestart: true,
                media: {video: capture1, audioSend: useAudio, videoRecv: false, myStatus: 'screen'},	// Screen sharing Publishers are sendonly
                success: function (jsep) {

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

                    var fileName = "screen-" + z;

                    Janus.debug("Got publisher SDP!");
                    Janus.debug(jsep);
                    var publish = {
                        "id": 666,
                        "request": "configure",
                        "audio": true,
                        "video": true,
                        "record": true,
                        "filename": "/var/www/html/watermark.wrk/records-tmp/" + fileName
                    };
                    screentest.send({"message": publish, "jsep": jsep});
                },
                error: function (error) {
                    Janus.error("WebRTC error:", error);
                    bootbox.alert("Вы отменили трансляцию рабочего стола. Чтобы включить трансляцию рабочего стола обновите текущую страницу (Ctrl+R) или нажмите F5.");
                    // bootbox.alert("WebRTC error... " + JSON.stringify(error));
                }
            });
    }

    function toggleMute1() {
        var muted = screentest.isAudioMuted();
        Janus.log((muted ? "Unmuting" : "Muting") + " local stream...");
        if (muted)
            screentest.unmuteAudio();
        else
            screentest.muteAudio();
        muted = screentest.isAudioMuted();
        $('#mute').html(muted ? "Unmute" : "Mute");
    }

    function unpublishOwnFeedScreen() {
        // Unpublish our stream
        //   $('#unpublish').attr('disabled', true).unbind('click');
        //  var unpublish = {"request": "unpublish"};
        //   screentest.send({"message": unpublish});
    }

    function newRemoteFeed1(id, display, audio, video) {
    }

// Helper to parse query string
    function getQueryStringValue1(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

// Helpers to create Simulcast-related UI, if enabled
    function addSimulcastButtons1(feed) {
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
                feeds1[index].send({message: {request: "configure", substream: 0}});
            });
        $('#sl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary')
            .unbind('click').click(function () {
                toastr.info("Switching simulcast substream, wait for it... (normal quality)", null, {timeOut: 2000});
                if (!$('#sl' + index + '-2').hasClass('btn-success'))
                    $('#sl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
                $('#sl' + index + '-1').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
                if (!$('#sl' + index + '-0').hasClass('btn-success'))
                    $('#sl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
                feeds1[index].send({message: {request: "configure", substream: 1}});
            });
        $('#sl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary')
            .unbind('click').click(function () {
                toastr.info("Switching simulcast substream, wait for it... (higher quality)", null, {timeOut: 2000});
                $('#sl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
                if (!$('#sl' + index + '-1').hasClass('btn-success'))
                    $('#sl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
                if (!$('#sl' + index + '-0').hasClass('btn-success'))
                    $('#sl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
                feeds1[index].send({message: {request: "configure", substream: 2}});
            });
        $('#tl' + index + '-0').removeClass('btn-primary btn-success').addClass('btn-primary')
            .unbind('click').click(function () {
                toastr.info("Capping simulcast temporal layer, wait for it... (lowest FPS)", null, {timeOut: 2000});
                if (!$('#tl' + index + '-2').hasClass('btn-success'))
                    $('#tl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
                if (!$('#tl' + index + '-1').hasClass('btn-success'))
                    $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
                $('#tl' + index + '-0').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
                feeds1[index].send({message: {request: "configure", temporal: 0}});
            });
        $('#tl' + index + '-1').removeClass('btn-primary btn-success').addClass('btn-primary')
            .unbind('click').click(function () {
                toastr.info("Capping simulcast temporal layer, wait for it... (medium FPS)", null, {timeOut: 2000});
                if (!$('#tl' + index + '-2').hasClass('btn-success'))
                    $('#tl' + index + '-2').removeClass('btn-primary btn-info').addClass('btn-primary');
                $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-info');
                if (!$('#tl' + index + '-0').hasClass('btn-success'))
                    $('#tl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
                feeds1[index].send({message: {request: "configure", temporal: 1}});
            });
        $('#tl' + index + '-2').removeClass('btn-primary btn-success').addClass('btn-primary')
            .unbind('click').click(function () {
                toastr.info("Capping simulcast temporal layer, wait for it... (highest FPS)", null, {timeOut: 2000});
                $('#tl' + index + '-2').removeClass('btn-primary btn-info btn-success').addClass('btn-info');
                if (!$('#tl' + index + '-1').hasClass('btn-success'))
                    $('#tl' + index + '-1').removeClass('btn-primary btn-info').addClass('btn-primary');
                if (!$('#tl' + index + '-0').hasClass('btn-success'))
                    $('#tl' + index + '-0').removeClass('btn-primary btn-info').addClass('btn-primary');
                feeds1[index].send({message: {request: "configure", temporal: 2}});
            });
    }

    function updateSimulcastButtons1(feed, substream, temporal) {
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

    function preshareScreen() {

        if (!Janus.isExtensionEnabled()) {
            bootbox.alert("You're using a recent version of Chrome but don't have the screensharing extension installed: click <b><a href='https://chrome.google.com/webstore/detail/janus-webrtc-screensharin/hapfgfdkleiggjjpfpenajgdnfckjpaj' target='_blank'>here</a></b> to do so", function () {
                window.location.reload();
            });
            return;
        }

        capture1 = "screen";
        if (navigator.mozGetUserMedia) {
            // Firefox needs a different constraint for screen and window sharing
            bootbox.dialog({
                title: "Транслировать весь экран или окно?",
                message: "Веб-браузер Firefox предоставляет возможность выбора способа трансляции вашего экрана. Пожалуйста, определитесь, собираетесь ли вы транслировать весь экран или одно окно определенного приложения. Определившись, нажмите на соответствующую кнопку.",
                buttons: {
                    screen: {
                        label: "Транслировать весь экран",
                        className: "btn-primary",
                        callback: function () {
                            capture1 = "screen";
                            shareScreen();
                        }
                    },
                    window: {
                        label: "Выбрать окно",
                        className: "btn-success",
                        callback: function () {
                            capture1 = "window";
                            shareScreen();
                        }
                    }
                },
                onEscape: function () {
                    bootbox.alert("Вы отменили трансляцию рабочего стола. Чтобы включить трансляцию рабочего стола обновите текущую страницу (Ctrl+R) или нажмите F5.");
                }
            });
        } else {
            shareScreen();
        }
    }

    function shareScreen() {
        // Create a new room

        registerUsername1();

        var register = {"request": "join", "room": Number("1234"), "ptype": "publisher", "display": 'screen'};
        screentest.send({"message": register});
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
                janus1 = new Janus(
                    {
                        server: server1,
                        success: function () {
                            // Attach to video room test plugin
                            janus1.attach(
                                {
                                    plugin: "janus.plugin.videoroom",
                                    opaqueId: opaqueId1,
                                    success: function (pluginHandle) {

                                        screentest = pluginHandle;
                                        Janus.log("Plugin attached! (" + screentest.getPlugin() + ", id=" + screentest.getId() + ")");

                                        Janus.log("  -- This is a publisher/manager");
                                        // Prepare the username registration

                                        preshareScreen();

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

                                        $("#videolocal1").parent().parent().unblock();
                                        // This controls allows us to override the global room bitrate cap
                                        $('#bitrate1').parent().parent().removeClass('hide').show();

                                        $('#bitrateset1').click(function () {
                                            $('#bitrate1').show();
                                        });

                                        $('#bitrate1 a').click(function () {
                                            var id = $(this).attr("id");
                                            var bitrate = parseInt(id) * 1000;
                                            if (bitrate === 0) {
                                                Janus.log("Not limiting bandwidth via REMB");
                                            } else {
                                                Janus.log("Capping bandwidth to " + bitrate + " via REMB");
                                            }
                                            $('#bitrateset1').html($(this).html() + '<span class="caret"></span>').parent().removeClass('open');
                                            screentest.send({"message": {"request": "configure", "bitrate": bitrate}});
                                            $('#bitrate1').hide();
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
                                                opaqueId: opaqueId1
                                            },
                                            url: '/ajax/server-screen-room-save',
                                            success: function (data) {
                                                //
                                            }
                                        });

                                        Janus.debug(" ::: Got a message (publisher) :::");
                                        Janus.debug(msg);
                                        var event = msg["videoroom"];
                                        Janus.debug("Event: " + event);

                                        if (event != undefined && event != null) {

                                            if (event === "joined") {

                                                // Publisher/manager created, negotiate WebRTC and attach to existing feeds1, if any
                                                myid1 = msg["id"];
                                                mypvtid1 = msg["private_id"];
                                                Janus.log("Successfully joined room " + msg["room"] + " with ID " + myid1);

                                                publishOwnFeedScreen(true);

                                                // Any new feed to attach to?
                                                if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                    var list = msg["publishers"];

                                                    Janus.debug("Got a list of available publishers/feeds1:");
                                                    Janus.debug(list);

                                                    for (var f in list) {
                                                        var id = list[f]["id"];
                                                        var display = list[f]["display"];
                                                        var audio = list[f]["audio_codec"];
                                                        var video = list[f]["video_codec"];
                                                        Janus.debug("  >> [" + id + "] " + display + " (audio: " + audio + ", video: " + video + ")");

                                                        newRemoteFeed1(id, display, audio, video);
                                                    }
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
                                                    Janus.debug("Got a list of available publishers/feeds1:");
                                                    Janus.debug(list);
                                                    for (var f in list) {
                                                        var id = list[f]["id"];
                                                        var display = list[f]["display"];
                                                        var audio = list[f]["audio_codec"];
                                                        var video = list[f]["video_codec"];
                                                        Janus.debug("  >> [" + id + "] " + display + " (audio: " + audio + ", video: " + video + ")");
                                                        newRemoteFeed1(id, display, audio, video);
                                                    }
                                                } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                                    // One of the publishers has gone away?
                                                    var leaving = msg["leaving"];
                                                    Janus.log("Publisher left: " + leaving);
                                                    var remoteFeed = null;
                                                    for (var i = 1; i < 6; i++) {
                                                        if (feeds1[i] != null && feeds1[i] != undefined && feeds1[i].rfid == leaving) {
                                                            remoteFeed = feeds1[i];
                                                            break;
                                                        }
                                                    }
                                                    if (remoteFeed != null) {
                                                        Janus.debug("Feed " + remoteFeed.rfid + " (" + remoteFeed.rfdisplay + ") has left the room, detaching");
                                                        $('#remote' + remoteFeed.rfindex).empty().hide();
                                                        $('#videoremote' + remoteFeed.rfindex).empty();
                                                        feeds1[remoteFeed.rfindex] = null;
                                                        remoteFeed.detach();
                                                    }
                                                } else if (msg["unpublished"] !== undefined && msg["unpublished"] !== null) {
                                                    // One of the publishers has unpublished?
                                                    var unpublished = msg["unpublished"];
                                                    Janus.log("Publisher left: " + unpublished);
                                                    if (unpublished === 'ok') {
                                                        // That's us
                                                        screentest.hangup();
                                                        return;
                                                    }
                                                    var remoteFeed = null;
                                                    for (var i = 1; i < 6; i++) {
                                                        if (feeds1[i] != null && feeds1[i] != undefined && feeds1[i].rfid == unpublished) {
                                                            remoteFeed = feeds1[i];
                                                            break;
                                                        }
                                                    }
                                                    if (remoteFeed != null) {
                                                        Janus.debug("Feed " + remoteFeed.rfid + " (" + remoteFeed.rfdisplay + ") has left the room, detaching");
                                                        $('#remote' + remoteFeed.rfindex).empty().hide();
                                                        $('#videoremote' + remoteFeed.rfindex).empty();
                                                        feeds1[remoteFeed.rfindex] = null;
                                                        remoteFeed.detach();
                                                    }
                                                } else if (msg["error"] !== undefined && msg["error"] !== null) {
                                                    $.ajax({
                                                        method: 'POST',
                                                        dataType: 'json',
                                                        url: '/ajaxrefresh-user-situation',
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

                                            screentest.handleRemoteJsep({jsep: jsep});
                                        }
                                    },
                                    onlocalstream: function (stream) {

                                        Janus.debug(" ::: Got a local stream :::");
                                        mystream1 = stream;
                                        Janus.debug(stream);

                                        if (isFirstDiv1) {

                                            isFirstDiv1 = false;

                                        } else {
                                            if ($('#myvideo1').length === 0) {
                                                $('#videolocal1').append('<video class="rounded centered video-js vjs-big-play-centered" id="myvideo1" width="100%" height="100%" muted="muted"/>');
                                            }

                                            $('#publisher1').removeClass('hide').html(myUserNameScreen).show();
                                            Janus.attachMediaStream($('#myvideo1').get(0), stream);
                                            $("#myvideo1").get(0).muted = "muted";
                                            if (screentest.webrtcStuff.pc.iceConnectionState !== "completed" &&
                                                screentest.webrtcStuff.pc.iceConnectionState !== "connected") {
                                                $("#videolocal1").parent().parent().block({
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
                                                $('#myvideo1').hide();
                                                if ($('#videolocal1 .no-video-container').length === 0) {
                                                    $('#videolocal1').append(
                                                        '<div class="no-video-container">' +
                                                        '<i class="fa fa-video-camera fa-5 no-video-icon"></i>' +
                                                        '<span class="no-video-text">No webcam available</span>' +
                                                        '</div>');
                                                }
                                            } else {
                                                $('#videolocal .no-video-container').remove();
                                                $('#myvideo1').removeClass('hide').show();
                                            }
                                        }

                                        // перезагрузка у всех учеников
                                        setTimeout(
                                            function () {
                                                janusClientChat.sendReloadMsg();
                                            }, 500
                                        );

                                        setTimeout(
                                            function () {
                                                let teacherScreenVideoJs = videojs('#myvideo1', {
                                                    controls: true,
                                                    autoplay: true,
                                                    preload: 'auto',
                                                    //width: '200%',
                                                    aspectRatio: "16:9"
                                                });

                                                $('#myvideo1 .vjs-volume-menu-button').hide();

                                                setTimeout(
                                                    function () {
                                                        teacherScreenVideoJs.play();

                                                        setTimeout(
                                                            function () {
                                                                $('#bitrate1 li:nth-child(5) a').click();
                                                            }, 350
                                                        );
                                                    }, 350
                                                );

                                            }, 10);

                                    },
                                    onremotestream: function (stream) {
                                        // The publisher stream is sendonly, we don't expect anything here
                                    },
                                    oncleanup: function () {
                                        Janus.log(" ::: Got a cleanup notification: we are unpublished now :::");
                                        mystream1 = null;
                                        $('#videolocal').html('<button id="publish" class="btn btn-primary">Publish</button>');

                                        $('#publish').click(function () {
                                            publishOwnFeedScreen(true);
                                        });

                                        $("#videolocal").parent().parent(dd).unblock();
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

            }

        });

    });
}