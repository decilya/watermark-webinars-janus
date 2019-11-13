var janusScreenObserver = null;


let startTeacherScreenObserverController = function () {

    const serverScreenObserver = "https://" + window.location.hostname + "/janus";
    const userBlockScreenObserver = $('#userBlock');
    const roleScreenObserver = "listener";
    const patronymicScreenObserver = userBlockScreenObserver.data('patronymic');
    const surnameScreenObserver = userBlockScreenObserver.data('surname');
    const nameScreenObserver = userBlockScreenObserver.data('name');
    const myUserNameScreenObserver = surnameScreenObserver + '' + nameScreenObserver + '' + patronymicScreenObserver;
    const screenUserIdObserver = userBlockScreenObserver.data('user');

    let roomScreenObserver = null;
    let opaqueIScreenObserver = null;
    let handleScreenObserver = null;
    let spinnerScreenObserver = null;
    let captureScreenObserver = "screen";


    let screenStartedObserver = false;
    let xObserver = true;

    let myChatRoomObserver = Number($('#roomNumber').val());
    let aaObserver = null;

    let isFirstDiv1 = false;

    let patronymic = $('#userBlock').data('patronymic');
    let surname = $('#userBlock').data('surname');
    let name = $('#userBlock').data('name');
    let myUserNameScreen = surname + "_" + patronymic + '_' + name + '-screen';

    let remoteFeedScreen = null;

    this.starterObserver = function (currentRoomScreen, opaqueIScreen) {

        if ((!currentRoomScreen) || (!opaqueIScreen)) {
            currentRoomScreen = $('#currentRoomScreen').val();
            opaqueIScreen = $('#opaqueIdScreen').val();
        }
        if (!((currentRoomScreen) && (opaqueIScreen))) {
            startJanusForScreenObserver(Number(currentRoomScreen), String(opaqueIScreen));
        } else {
            // чтобы наверника, лол xD
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: '/ajax/get-server-room',
                success: function (data) {
                    // Janus работает только с не негативным интеджером :)
                    startJanusForScreenObserver(Number(currentRoomScreen), String(opaqueIScreen));
                }
            });
        }
    };

    // var currentRoomScreen = $('#currentRoomScreen').val();
    // var opaqueIScreen = $('#opaqueIdScreen').val();

    function newremoteFeedScreenObserver(id, display) {

        //console.log('qwrqwrwqrw1r21r21r21r21r21r2r12r' + display);
        //if (display != 'screen') return;

        id = Number(id);
        opaqueIScreenObserver = String(opaqueIScreenObserver);

        // A new feed has been published, create a new plugin handle and attach to it as a listener
        //source = id;

        janusScreenObserver.attach(
            {
                plugin: "janus.plugin.videoroom",
                opaqueId: opaqueIScreenObserver,
                success: function (pluginHandle) {
                    remoteFeedScreen = pluginHandle;
                    Janus.log("Plugin attached! (" + remoteFeedScreen.getPlugin() + ", id=" + remoteFeedScreen.getId() + ")");
                    Janus.log("  -- This is a subscriber");
                    // We wait for the plugin to send us an offer
                    let listen = {"request": "join", "room": roomScreenObserver, "ptype": "listener", "feed": id};
                    remoteFeedScreen.send({"message": listen});
                },
                error: function (error) {
                    Janus.error("  -- Error attaching plugin...", error);


                    //  //console.log("----------------------------------");
                    //  //console.log(error);

                    //bootbox.alert("Error attaching plugin... " + error);
                },
                onmessage: function (msg, jsep) {
                    /*Janus.debug(" ::: Got a message (listener) :::");*/
                    /*Janus.debug(msg);*/
                    let event = msg["videoroom"];
                    /*Janus.debug("Event: " + event);*/
                    if (event !== undefined && event !== null) {
                        if (event === "attached") {
                            // Subscriber created and attached
                            if (spinnerScreenObserver === undefined || spinnerScreenObserver === null) {
                                let target = document.getElementById('#screencapture');
                                spinnerScreenObserver = new Spinner({top: 100}).spin(target);
                            } else {
                                spinnerScreenObserver.spin();
                            }
                            //Janus.log("Successfully attached to feed " + id + " (" + display + ") in room " + msg["room"]);
                        } else {
                            // What has just happened?
                        }
                    }
                    if (jsep !== undefined && jsep !== null) {
                        /*Janus.debug("Handling SDP as well...");*/
                        Janus.debug(jsep)
                        //  Answer and attach
                        remoteFeedScreen.createAnswer(
                            {
                                jsep: jsep,
                                media: {audioSend: false, videoSend: false},
                                success: function (jsep) {
                                    /*Janus.debug("Got SDP!");*/
                                    /*Janus.debug(jsep);*/
                                    let body = {"request": "start", "room": roomScreenObserver};
                                    remoteFeedScreen.send({"message": body, "jsep": jsep});
                                },
                                error: function (error) {

                                    Janus.error("WebRTC error:", error);
                                    //console.log("----- - - - - -  - -");
                                    //console.log(error);

                                    //  bootbox.alert("WebRTC error... " + error);
                                }
                            });

                    }
                },
                onlocalstream: function (stream) {
                    console.log(stream);
                    ////console.log("onlocalstream");
                },
                onremotestream: function (stream) {


                    if (!screenStartedObserver) {
                        screenStartedObserver = true;
                        if ($('#myvideo1').length === 0) {
                            $('#videoWaterMark').append('<video id="screenvideo2" class="myvideo1" width="100%" height="100%" autoplay muted="muted"/>');
                        }

                        const video = document.querySelector('video');

                        // картинка в картинке
                        video.addEventListener('enterpictureinpicture', () => {

                            if (document.pictureInPictureElement) {
                                document.exitPictureInPicture()
                                    .then(() => { /**/
                                    })
                                    .catch(() => { /**/
                                    });
                            }

                        });
                        let leftSide = $('.left-side');
                        let rightSide = $('.right-side');
                        let rightTable = $('.right-table');
                        let tabContent = $('.tab-content');

                        function scrollButton() {
                            let chat_scroll = $('.tab-content-wrapper');
                            chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
                        }

                        $("#screenvideo2").bind("playing", function (error) {
                            $('#loadingJanus').hide();
                        });

                        $('#screenvideo2').get(0).addEventListener('error', function (error) {
                            //console.log(error);
                        }, false);

                        //////////////////////////// ////////////////////////////

                        $('#publisher1').removeClass('hide').html(myUserNameScreen).show();

                        $("#screenvideo2").get(0).muted = "muted";

                        let videoTracks = stream.getVideoTracks();
                        if (videoTracks === null || videoTracks === undefined || videoTracks.length === 0) {
                            // No webcam
                            $('#screenvideo2').hide();
                            if ($('#videolocal1 .no-video-container').length === 0) {
                                $('#videolocal1').append(
                                    '<div class="no-video-container">' +
                                    '<i class="fa fa-video-camera fa-5 no-video-icon"></i>' +
                                    '<span class="no-video-text">No webcam available</span>' +
                                    '</div>');
                            }
                        } else {
                            $('#videolocal .no-video-container').remove();
                            $('#screenvideo2').removeClass('hide').show();
                        }

                        /*Janus.debug(" ::: Got a local stream :::");*/
                        /*Janus.debug(stream);*/

                        $('#publisher1').removeClass('hide').html(myUserNameScreen).show();

                        $("#screenvideo2").get(0).muted = "muted";
                        let fsBtn = $('.tv-screen');

                        fsBtn.on('click', function () {

                            setTimeout(function () {
                                scrollButton();
                            }, 1000);

                            $(this).toggleClass('full');

                            if (fsBtn.hasClass('full')) {

                                leftSide.css({
                                    display: 'block',
                                    flexDirection: 'column',
                                    maxHeight: '100%',
                                    height: 'auto'
                                });

                                rightSide.css('display', 'table-row');

                                leftSide.removeClass('mdl-cell mdl-cell--8-col');
                                rightSide.removeClass('mdl-cell mdl-cell--4-col');

                                leftSide.addClass('mdl-cell mdl-cell--12-col');
                                rightSide.addClass('mdl-cell mdl-cell--12-col');

                                rightTable.css({
                                   // height: '500',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    marginBottom: '10px',
                                });

                                tabContent.css({
                                   // height: '500',
                                    display: 'flex',
                                    flexDirection: 'column'
                                });

                                $('.main-page').css('margin-bottom', '30px');

                                $('#videoWaterMark').css('max-height', '80vh');
                                $('#videoWaterMark').css('height', '80vh');
                                //$('#screenvideo2').css('max-height', '1080px');

                            } else {

                                $('.main-page').css('margin-bottom', '0px');

                                leftSide.removeClass('mdl-cell mdl-cell--12-col');
                                rightSide.removeClass('mdl-cell mdl-cell--12-col');

                                leftSide.addClass('mdl-cell mdl-cell--8-col');
                                rightSide.addClass('mdl-cell mdl-cell--4-col');

                                setTimeout(function () {
                                    scrollButton();
                                }, 1000);

                                $('#videoWaterMark').css('max-height', '630px');
                                $('#videoWaterMark').css('height', '630px');
                            }
                        });
                        let videoTracksObserver = stream.getVideoTracks();
                        if (videoTracksObserver === null || videoTracksObserver === undefined || videoTracksObserver.length === 0) {
                            $('#screenvideo2').hide();
                            if ($('#videolocal1 .no-video-container').length === 0) {
                                $('#videolocal1').append(
                                    '<div class="no-video-container">' +
                                    '<i class="fa fa-video-camera fa-5 no-video-icon"></i>' +
                                    '<span class="no-video-text">No webcam available</span>' +
                                    '</div>');
                            }
                        } else {
                            $('#videolocal .no-video-container').remove();
                            $('#screenvideo2').removeClass('hide').show();
                        }

                        screenStartedObserver = true;
                        janusClientChat.sendReloadMsg();
                    }

                    setTimeout(
                        function () {

                            Janus.attachMediaStream($('#screenvideo2').get(0), stream);

                            setTimeout(
                                function () {
                                    $('#screenvideo2').get(0).play().catch(function (error) {
                                        window.location.reload(true);
                                    });
                                }, 0
                            );

                            $('#screenvideo2').get(0).addEventListener('canplaythrough', function () {
                                //  alert('go!');
                                $('#screenvideo2').get(0).play();
                                $('#startRecord').show();

                            }, false);

                            var arrBadBitrate = [];
                            var arrNotBadBitrate = [];

                            setInterval(
                                function () {

                                    if (remoteFeedScreen.getBitrate() === 'Invalid PeerConnection') {
                                        // $.ajax({
                                        //     method: 'POST',
                                        //     dataType: 'json',
                                        //     url: '/ajax/refresh-user-situation',
                                        //     data: {userId: screenUserIdObserver},
                                        //     success: function (data) {
                                        statusProject = false;
                                        //     }
                                        // });
                                    }

                                    let bigError = $('#bigError');
                                    if (remoteFeedScreen.getBitrate() === '0 kbits/sec') {

                                        if (arrBadBitrate.length > 3) {
                                            if ($('div').is('#bigError')) {
                                                bigError.show();
                                            }
                                        }

                                        arrBadBitrate.push(remoteFeedScreen.getBitrate());

                                        //console.log(arrBadBitrate.length);

                                        if (arrBadBitrate.length > 20) {
                                            statusProject = false;
                                        }

                                    } else {

                                        arrNotBadBitrate.push(remoteFeedScreen.getBitrate());
                                        if (arrNotBadBitrate.length > 3) {
                                            arrBadBitrate = [];
                                            arrNotBadBitrate = [];

                                            statusProject = true;

                                            // $.ajax({
                                            //     method: 'POST',
                                            //     dataType: 'json',
                                            //     url: '/ajax/set-screen-time',
                                            //     data: {userId: $('#userBlock').data('user')},
                                            //     success: function (result) {
                                            //         console.log('set-screen-time');
                                            //         console.log(result);
                                            //     },
                                            //     error: function (err) {
                                            //         console.log(err);
                                            //     }
                                            // });
                                        }

                                        if ($('div').is('#bigError')) {
                                            bigError.hide();

                                            // setTimeout(
                                            //     () => {
                                            //         bigError.hide();
                                            //     }, 7000
                                            // );
                                        }

                                    }
                                }, 1000
                            );


                        }, 0
                    );

                },
                oncleanup: function () {
                    //Janus.log(" ::: Got a cleanup notification (remote feed " + id + ") :::");
                    //  $('#waitingvideo').remove();
                    if (spinnerScreenObserver !== null && spinnerScreenObserver !== undefined) {
                        spinnerScreenObserver.stop();
                    }
                    spinnerScreenObserver = null;
                },
                error: function () {
                    //window.location.reload(true);
                }
            });
    }


    function startJanusForScreenObserver(currentRoomScreen) {

        roomScreenObserver = Number(currentRoomScreen);

        Janus.init({
            debug: true,
            dependencies: Janus.useDefaultDependencies(),
            callback: function () {
                janusScreenObserver = new Janus(
                    {
                        server: serverScreenObserver,
                        success: function () {

                            if ($('div').is('#loadingJanus')) {
                                if ($('#loadingJanus').is(":hidden")) {
                                    $('#loadingJanus').show();
                                }
                            }
                            opaqueIScreenObserver = String(opaqueIScreenObserver);
                            janusScreenObserver.attach(
                                {

                                    plugin: "janus.plugin.videoroom",
                                    success: function (pluginHandle) {

                                        screenStartedObserver = false;

                                        handleScreenObserver = pluginHandle;
                                        console.log(roomScreenObserver);

                                        let register = {
                                            "request": "join",
                                            "room": roomScreenObserver,
                                            "ptype": "publisher",
                                            "display": myUserNameScreenObserver
                                        };

                                        handleScreenObserver.send({
                                            "message": register,
                                            success: function (result) {

                                                if ($('.main-page')) {

                                                    // Постоянно, какие 3 сек, проверяем состояние трансляций, если че то
                                                    setInterval(
                                                        function () {
                                                            let list = {
                                                                "request": "listparticipants",
                                                                "room": myChatRoomObserver
                                                            };

                                                            handleScreenObserver.send({
                                                                "message": list,
                                                                success: function (result) {

                                                                },
                                                                error: function (errr) {

                                                                    // $.ajax({
                                                                    //     method: 'POST',
                                                                    //     dataType: 'json',
                                                                    //     url: '/ajax/refresh-user-situation',
                                                                    //     data: {userId: $('#userBlock').data('user')},
                                                                    //     success: function (data) {
                                                                    window.location.reload(true);
                                                                    //     }
                                                                    // });
                                                                }
                                                            });


                                                        }, 3000
                                                    );
                                                }


                                            }
                                        });

                                    },
                                    error: function (error) {
                                        Janus.error("  -- Error attaching plugin...", error);
                                        bootbox.alert("Error attaching plugin... " + error);
                                    },
                                    webrtcState: function (on) {
                                        Janus.log("Janus says our WebRTC PeerConnection is " + (on ? "up" : "down") + " now");
                                        $("#screencapture").parent().unblock();
                                        if (on) {
                                            bootbox.alert("Your screen sharing session just started: pass the <b>" + room + "</b> session identifier to those who want to attend.");
                                        } else {
                                            bootbox.alert("Your screen sharing session just stopped.", function () {
                                                janus.destroy();
                                                window.location.reload();
                                            });
                                        }
                                    },
                                    onmessage: function (msg, jsep) {


                                        let event = msg["videoroom"];
                                        /*Janus.debug("Event: " + event);*/

                                        if (event === "joined") {
                                            if (roleScreenObserver === "publisher") {
                                                // This is our session, publish our stream
                                                /*Janus.debug("Negotiating WebRTC stream for our screen (capture " + captureScreenObserver + ")");*/
                                                handleScreenObserver.createOffer(
                                                    {
                                                        media: {
                                                            video: captureScreenObserver,
                                                            audioSend: false,
                                                            videoRecv: false
                                                        },	// Screen sharing Publishers are sendonly
                                                        success: function (jsep) {
                                                            /*Janus.debug("Got publisher SDP!");*/
                                                            /*Janus.debug(jsep);*/
                                                            let publish = {
                                                                "request": "configure",
                                                                "audio": true,
                                                                "video": true
                                                            };

                                                            handleScreenObserver.send({
                                                                "message": publish,
                                                                "jsep": jsep
                                                            });
                                                        },
                                                        error: function (error) {
                                                            // Janus.error("WebRTC error:", error)
                                                            //console.log("WebRTC error... " + JSON.stringify(error));
                                                            // bootbox.alert("WebRTC error... " + JSON.stringify(error));
                                                        }
                                                    });

                                            } else {

                                                // We're just watching a session, any feed to attach to?
                                                if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                    let list = msg["publishers"];

                                                    Janus.debug("Got a list of available publishers/feeds:");
                                                    /*Janus.debug(list);*/
                                                    for (let f in list) {
                                                        let id = list[f]["id"];
                                                        let display = list[f]["display"];
                                                        Janus.debug("  >> [" + id + "] " + display);
                                                        newremoteFeedScreenObserver(id, display)
                                                    }
                                                }
                                            }
                                        } else if (event === "event") {
                                            // Any feed to attach to?
                                            if (roleScreenObserver === "listener" && msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                let list = msg["publishers"];
                                                Janus.debug("Got a list of available publishers/feeds:");
                                                /*Janus.debug(list);*/
                                                for (let f in list) {
                                                    let id = list[f]["id"];

                                                    let display = list[f]["display"];

                                                    Janus.debug("  >> [" + id + "] " + display);
                                                    newremoteFeedScreenObserver(id, display)
                                                }
                                            } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                                // One of the publishers has gone away?
                                                let leaving = msg["leaving"];
                                                //Janus.log("Publisher left: " + leaving);
                                            } else if (msg["error"] !== undefined && msg["error"] !== null) {
                                                //console.log(msg["error"]);
                                                // window.location.reload(true);
                                            }
                                        }

                                        // Handle msg, if needed, and check jsep
                                        if (jsep !== undefined && jsep !== null) {
                                            // We have the ANSWER from the plugin
                                            handleScreenObserver.handleRemoteJsep({jsep: jsep});
                                        }
                                    },
                                    onlocalstream: function (stream) {
                                        console.log(stream);
                                        console.log('++++++++++++');
                                    },
                                    onremotestream: function (stream) {

                                    }
                                });
                        },
                        error: function (cause) {
                            //console.log('============ error ==========================');
                            //console.log(cause);
                        },
                        destroyed: function () {
                            // I should get rid of this
                        }
                    });
            }
        });
    }
};
