var screenVideo = $('#screenvideo2');
var myNewClientScreen;
var myWm = undefined;

const serverScreen = "https://" + window.location.hostname + "/janus";
const userBlockScreen = $('#userBlock');
const roleScreen = "listener";
const patronymicScreen = userBlockScreen.data('patronymic');
const surnameScreen = userBlockScreen.data('surname');
const nameScreen = userBlockScreen.data('name');
const myUserNameScreen = surnameScreen + '' + nameScreen + '' + patronymicScreen;
const screenUserId = userBlockScreen.data('user');

var currentRoomScreen = $('#currentRoomScreen').val();
var opaqueIScreen = $('#opaqueIdScreen').val();

var janusScreen = null;
var remoteFeedScreen = null;
var clickToArchive = 0;
var incPlay = 0;
var videoCanvas = document.createElement("video");
let adBlokerFinder = false;

var myClientScreen = function () {

    let myThisObj = this;

    myClientScreen.isNotStarted = true;

    myClientScreen.sendReload = function () {
        // $.ajax({
        //     method: 'POST',
        //     dataType: 'json',
        //     url: '/ajax/refresh-user-situation',
        //     async: false,
        //     data: {userId: $('#userBlock').data('user')},
        //     success: function () {
        window.location.reload(true);
        //     }
        // });
    };

    var $this = this;

    var roomScreen = Number($('#roomNumber').val());
    var opaqueIScreen = null;
    var handleScreen = null;
    var spinnerScreen = null;
    var captureScreen = "screen";
    var screenStarted = false;
    var myChatRoom = Number($('#roomNumber').val());

    var myStr;

    this.destroying = function () {
        remoteFeedScreen.detach({
            success: function () {

                janusScreen.destroy({
                    success: function () {
                        setTimeout(
                            function () {
                                // myNewClientScreen = new myClientScreen();
                                // myNewClientScreen.init();

                                myClientScreen.sendReload();

                            }, 0
                        );
                    },
                });
            },
        });

    };

    function newRemoteFeedScreenScreen(id, display) {

        //if (display !== 'screen') return;

        id = Number(id);
        opaqueIScreen = String(opaqueIScreen);

        janusScreen.attach(
            {
                plugin: "janus.plugin.videoroom",
                opaqueId: opaqueIScreen,
                success: function (pluginHandle) {
                    remoteFeedScreen = pluginHandle;
                    //Janus.log("Plugin attached! (" + remoteFeedScreen.getPlugin() + ", id=" + remoteFeedScreen.getId() + ")");
                    ////Janus.log("  -- This is a subscriber");
                    // We wait for the plugin to send us an offer
                    let listen = {"request": "join", "room": roomScreen, "ptype": "listener", "feed": id};

                    remoteFeedScreen.send({"message": listen});
                },
                error: function (error) {
                    Janus.error("  -- Error attaching plugin...", error);
                    //bootbox.alert("Error attaching plugin... " + error);
                    //sendReload();
                },
                onmessage: function (msg, jsep) {
                    /*Janus.debug(" ::: Got a message (listener) :::");*/
                    /*Janus.debug(msg);*/
                    let event = msg["videoroom"];
                    /*Janus.debug("Event: " + event);*/
                    if (event !== undefined && event !== null) {
                        if (event === "attached") {
                            // Subscriber created and attached
                            if (spinnerScreen === undefined || spinnerScreen === null) {
                                let target = document.getElementById('#screencapture');
                                spinnerScreen = new Spinner({top: 100}).spin(target);
                            } else {
                                spinnerScreen.spin();
                            }
                            //Janus.log("Successfully attached to feed " + id + " (" + display + ") in room " + msg["room"]);
                        } else if (event === "detached") {
                            //alert(1);
                        } else {
                            //console.log(event);
                        }
                    }
                    if (jsep !== undefined && jsep !== null) {
                        /*Janus.debug("Handling SDP as well...");*/
                        /*Janus.debug(jsep);*/
                        //  Answer and attach
                        remoteFeedScreen.createAnswer(
                            {
                                jsep: jsep,
                                media: {audioSend: false, videoSend: false},	// We want recvonly audio/video
                                success: function (jsep) {
                                    /*Janus.debug("Got SDP!");*/
                                    /*Janus.debug(jsep);*/
                                    let body = {"request": "start", "room": roomScreen};
                                    remoteFeedScreen.send({"message": body, "jsep": jsep});
                                },
                                error: function (error) {
                                    Janus.error("WebRTC error:", error);
                                    //bootbox.alert("WebRTC error... " + error);
                                }
                            });

                    }
                },
                onlocalstream: function (stream) {
                    ////console.log("onlocalstream");
                },
                onremotestream: function (stream) {

                    // if (!$('#echoTestBlockBeforeStartUser').is(':visible')) {

                    function setCss() {
                        let leftSide = $('.left-side');
                        let rightSide = $('.right-side');
                        let rightTable = $('.right-table');
                        let tabContent = $('.tab-content');

                        function scrollButton() {
                            let chat_scroll = $('.tab-content-wrapper');
                            chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
                        }

                        let fsBtn = $('.tv-screen');
                        let videoElement = document.getElementById('videoWaterMark');

                        fsBtn.on('click', function () {

                            setTimeout(function () {
                                scrollButton();
                            }, 1000);

                            $(this).toggleClass('full');

                            if (fsBtn.hasClass('full')) {

                                if(videoElement.classList.contains("fullscreen")) {
                                    fullScreenCancel();
                                }
                                leftSide.css({
                                    display: 'block',
                                    flexDirection: 'column',
                                    maxHeight: '100%',
                                    height: 'auto'
                                });

                                rightSide.css('display', 'table-row');

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

                                leftSide.css({width: '100%', height: $("#videoAndChatUserMainBlock").innerWidth() * 0.45});
                                rightSide.css({width: '100%', height: '400px'})

                            } else {
                                if(videoElement.classList.contains("fullscreen")) {
                                    fullScreenCancel();
                                }
                                $('.main-page').css('margin-bottom', '0px');

                                setTimeout(function () {
                                    scrollButton();
                                }, 1000);

                                $('#videoWM').css({width: '100%',  height: '100%'});
                                $('#videoWM').removeAttr('style');
                                rightSide.removeAttr('style');
                                leftSide.removeAttr('style');
                            }
                        });

                        let chatBlock = $('#chatBlock');
                        chatBlock.scrollTop(chatBlock.height());
                        $('#inputMessageChat ').focus();
                    }


                    function registerStream(stream) {
                        setTimeout(
                            () => {
                                Janus.attachMediaStream(videoCanvas, stream);
                            }, 0
                        );
                    }

                    function startScreen(stream) {
                        myStr = stream;

                        videoCanvas.addEventListener("playing", function (error) {


                            if (incPlay === 0) {

                                $('#waitingvideo2').remove();
                                $('#loadingJanus').hide();
                                $('#noConnectionDiv').hide();
                                $('#bigError').hide();
                                $('#noTotalConnectionDiv').hide();

                                incPlay++;
                            }

                            screenVideo.removeClass('hide');

                            setCss();

                            if (spinnerScreen !== null && spinnerScreen !== undefined) spinnerScreen.stop();
                            spinnerScreen = null;

                            var arrBadBitrate = [];
                            var arrNotBadBitrate = [];

                            setInterval( //R
                                function () {

                                    if (remoteFeedScreen.getBitrate() === 'Invalid PeerConnection') {
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

                                    let bigError = $('#bigError');
                                    if (remoteFeedScreen.getBitrate() === '0 kbits/sec') {

                                        if (arrBadBitrate.length > 3) {
                                            if ($('div').is('#bigError')) {
                                                bigError.show();
                                            }
                                        }

                                        arrBadBitrate.push(remoteFeedScreen.getBitrate());

                                        if (arrBadBitrate.length > 20) {
                                            window.location.reload(true);
                                        }

                                    } else {

                                        arrNotBadBitrate.push(remoteFeedScreen.getBitrate());

                                        if (arrNotBadBitrate.length > 3) {
                                            arrBadBitrate = [];
                                            arrNotBadBitrate = [];
                                        }

                                        if ($('div').is('#bigError')) {
                                            bigError.hide();
                                        }

                                    }
                                }, 1000);

                        });


                        $('#videos2').show();
                        $('#loadingClient').hide();

                        screenVideo.show();

                        registerStream(stream);


                        // ????
                        videoCanvas.addEventListener('canplaythrough', function () {


                            videoCanvas.play().catch(function (error) {

                            });

                        }, false);

                        if (videoCanvas.paused) {
                            $('artical#echoTestBlockBeforeStartUser').show();
                        } else {
                            $('artical#echoTestBlockBeforeStartUser').hide();
                        }
                    }

                    setTimeout(
                        function () {
                            if (!screenStarted) {
                                screenStarted = true;
                                startScreen(stream);
                            }
                        }, 0
                    );
                },
                oncleanup: function () {
                    //Janus.log(" ::: Got a cleanup notification (remote feed " + id + ") :::");
                    if (spinnerScreen !== null && spinnerScreen !== undefined) {
                        spinnerScreen.stop();
                    }
                    spinnerScreen = null;
                }
            });
    }

    this.init = function () {

        var startProject = Number($('#startProject').val());

        if (startProject) {

            roomScreen = Number($('#roomNumber').val());
            handleScreen = null;
            spinnerScreen = null;
            captureScreen = "screen";
            screenStarted = false;
            myChatRoom = Number($('#roomNumber').val());
            remoteFeedScreen = null;

            //currentRoomScreen = $('#currentRoomScreen').val();
            currentRoomScreen = roomScreen;

            opaqueIScreen = $('#opaqueIdScreen').val();

            if (!((currentRoomScreen == undefined) &&
                (currentRoomScreen == null) &&
                (opaqueIScreen == undefined) &&
                (opaqueIScreen == null))) {

                setTimeout(
                    () => {
                        // чтобы наверника, лол xD
                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: '/ajax/get-server-room',
                            success: function (data) {
                                // //console.log(data);
                                // myNewClientScreen.init(Number(currentRoomScreen), String(opaqueIScreen));
                            }
                        });
                    }, 0);
            }

            roomScreen = Number(currentRoomScreen);

            Janus.init({
                debug: true,
                dependencies: Janus.useDefaultDependencies(),
                callback: function () {
                    janusScreen = new Janus(
                        {
                            server: serverScreen,
                            success: function () {

                                if ($('div').is('#loadingJanus')) {
                                    if ($('#loadingJanus').is(":hidden") && !clickToArchive) {
                                        $('#loadingJanus').show();
                                    }
                                }

                                janusScreen.attach(
                                    {
                                        plugin: "janus.plugin.videoroom",
                                        success: function (pluginHandle) {

                                            handleScreen = pluginHandle;

                                            let register = {
                                                "request": "join",
                                                "room": roomScreen,
                                                "ptype": "publisher",
                                                "display": myUserNameScreen
                                            };

                                            handleScreen.send({
                                                "message": register,
                                                success: function (result) {

                                                    if ($('.main-page')) {

                                                        // Постоянно, проверяем состояние трансляций, если че то
                                                        setInterval(  //R
                                                            function () {

                                                                let list = {
                                                                    "request": "listparticipants",
                                                                    "room": myChatRoom
                                                                };

                                                                handleScreen.send({
                                                                    "message": list,
                                                                    success: function (result) {

                                                                        var tmpParticipants = result['participants'];

                                                                        if (tmpParticipants.length) {

                                                                            for (let j = 0; j < tmpParticipants.length; j++) {

                                                                                if (tmpParticipants[j]['publisher'] === true) {
                                                                                    myClientScreen.isNotStarted = false;
                                                                                    break;
                                                                                }

                                                                            }
                                                                        }

                                                                        statusProject = !myClientScreen.isNotStarted;
                                                                    },
                                                                    error: function () {
                                                                        window.location.reload(true);
                                                                    }
                                                                });


                                                            }, 3000
                                                        );
                                                    }
                                                }
                                            });
                                        },

                                        onmessage: function (msg, jsep) {

                                            let event = msg["videoroom"];
                                            /*Janus.debug("Event: " + event);*/

                                            if (event === "joined") {
                                                if (roleScreen === "publisher") {
                                                    // This is our session, publish our stream
                                                    /*Janus.debug("Negotiating WebRTC stream for our screen (capture " + captureScreen + ")");*/
                                                    handleScreen.createOffer(
                                                        {
                                                            media: {
                                                                video: captureScreen,
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

                                                                handleScreen.send({"message": publish, "jsep": jsep});
                                                            },
                                                            error: function (error) {
                                                                Janus.error("WebRTC error:", error);
                                                                //bootbox.alert("WebRTC error... " + JSON.stringify(error));
                                                            }
                                                        });
                                                } else {

                                                    // We're just watching a session, any feed to attach to?
                                                    if (msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                        let list = msg["publishers"];

                                                        /*Janus.debug("Got a list of available publishers/feeds:");*/
                                                        /*Janus.debug(list);*/
                                                        for (let f in list) {
                                                            let id = list[f]["id"];
                                                            let display = list[f]["display"];
                                                            /*Janus.debug("  >> [" + id + "] " + display);*/

                                                            setTimeout(
                                                                function () {
                                                                    newRemoteFeedScreenScreen(id, display)
                                                                }, 500
                                                            );
                                                        }
                                                    }
                                                }
                                            } else if (event === "event") {
                                                // Any feed to attach to?
                                                if (roleScreen === "listener" && msg["publishers"] !== undefined && msg["publishers"] !== null) {
                                                    let list = msg["publishers"];
                                                    /*Janus.debug("Got a list of available publishers/feeds:");*/
                                                    /*Janus.debug(list);*/
                                                    for (let f in list) {
                                                        let id = list[f]["id"];

                                                        let display = list[f]["display"];

                                                        /*Janus.debug("  >> [" + id + "] " + display);*/
                                                        newRemoteFeedScreenScreen(id, display)
                                                    }
                                                } else if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                                    // One of the publishers has gone away?
                                                    let leaving = msg["leaving"];
                                                    //Janus.log("Publisher left: " + leaving);
                                                    /* if (roleScreen === "listener" && msg["leaving"] === source) {
                                                      bootbox.alert("The screen sharing session is over, the publisher left", function () {
                                                      // window.location.reload(true);
                                                      });
                                                      }*/
                                                } else if (msg["error"] !== undefined && msg["error"] !== null) {
                                                    // bootbox.alert(msg["error"]);
                                                }
                                            }

                                            // Handle msg, if needed, and check jsep
                                            if (jsep !== undefined && jsep !== null) {
                                                // We have the ANSWER from the plugin
                                                handleScreen.handleRemoteJsep({jsep: jsep});
                                            }
                                        },
                                        onlocalstream: function (stream) {
                                            // //console.log(stream);
                                            // //console.log('++++++++++++');
                                        },
                                        onremotestream: function (stream) {
                                            // //console.log(stream);
                                            // //console.log('----------');
                                        }
                                    });
                            },
                            error: function (cause) {
                                // //console.log(cause);
                                // //console.log('error');
                            },
                            destroyed: function () {

                            }
                        });
                }
            });

        }
    }
};

$(document).ready(function () {

    document.addEventListener('checkServer', function (e) {
        if (serverStatus) {
            $('#errorInfoStartProject').hide();
            $('#successInfoStartProject').show();

            if (myCheckAdBlock() === false) {
                $('#joinJanus').attr('disabled', false);
            }
        } else {
            $('#errorInfoStartProject').show();
            $('#successInfoStartProject').hide();

            $('#joinJanus').attr('disabled', true);
        }
    }, false);

    $("#joinJanus").on('click', function () {

        let checkStrim = true;

        $('[data-checkbox]').each(function () {
            if (!$($(this).find('.itemCheck')[0]).is(':visible')) {
                checkStrim = false;
                $('#joinJanus').attr('disabled', true);
            }
        });

        if (checkStrim) {
            $('#echoTestBlockBeforeStartUser').hide("slow");
            $("#videoAndChatUserMainBlock").show("slow");

            setTimeout(
                () => {
                    // window.myWm = new Wm();
                    // myWm.init();
                    // myWm.startCheckWatermark();
                },
                666
            );

            videoCanvas.play();
            videoCanvas.muted = false;
        }
    });

    myNewClientScreen = new myClientScreen();
    myNewClientScreen.init();

    $('nav a').on('click', function () {
        clickToArchive = 1;
    });

    $('#btnStartPlay').on('click', function () {
        videoCanvas.play();
    });
});
