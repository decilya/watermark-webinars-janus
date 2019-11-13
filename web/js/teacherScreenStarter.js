var deadline = '2019-10-02 17:00:00';
var intervalTimer = null;
var data = new Date();
var date = new Date();
var seconds = null;
var settings;
var days, hours, minutes;
let startTeacherScreenController = function () {

        var server1 = "https://" + window.location.hostname + "/janus";
        var janus1 = null;
        var screentest = null;
        var opaqueId1 = "screensharingtest-" + Janus.randomString(12);
        var myroom1 = $('#roomNumber').val();
        var myUserNameScreen = null;
        var myid1 = null;
        var mystream1 = null;
        var capture1 = null;
        var mypvtid1 = null;
        var feeds1 = [];
        var isFirstDiv1 = false;
        var jsepMain = null;
        var fileNameMain = null;
        var deadLineTimerMin = $('#deadLineTimerMin').val();

        $.fn.downCount = function (options, callback) {

            settings = $.extend({
                date: new Date(),
                offset: null
            }, options);

            // Throw error if date is not set
            if (!settings.date) {
                $.error('Date is not defined.');
            }

            // Throw error if date is set incorectly
            if (!Date.parse(settings.date)) {
                $.error('Incorrect date format, it should look like this, 12/24/2012 12:00:00.');
            }

            // Save container
            var container = this;

            /**
             * Change client's local date to match offset timezone
             * @return {Object} Fixed Date object.
             */
            var currentDate = function () {
                var date = new Date();
                // turn date to utc
                var utc = date.getTime() + (date.getTimezoneOffset());

                // set new Date object
                var new_date = new Date(utc + (3600000 * settings.offset));

                return new_date;
            };

            /**
             * Main downCount function that calculates everything
             */
            function countdown() {
                var target_date = new Date(settings.date), // set target date
                    current_date = currentDate(); // get fixed current date

                // difference of dates
                var difference = target_date - current_date;

                // if difference is negative than it's pass the target date
                if (difference < 0) {
                    // stop timer
                    clearInterval(intervalTimer);

                    if (callback && typeof callback === 'function') callback();

                    return;
                }

                // basic math variables
                var _second = 1000,
                    _minute = _second * 60,
                    _hour = _minute * 60,
                    _day = _hour * 24;

                // calculate dates
                var days = Math.floor(difference / _day),
                    hours = Math.floor((difference % _day) / _hour),
                    minutes = Math.floor((difference % _hour) / _minute);

                if ((seconds === 1) || (seconds === '1') || (seconds === 0) || (seconds === '0') || (seconds < 1)) {
                    seconds = null;
                }

                // seconds = (seconds == null) ? 59 : Math.floor((difference % _minute) / _second);
                seconds = (seconds == null) ? 59 : (seconds - 1);

                // fix dates so that it will show two digets
                days = (String(days).length >= 2) ? days : '0' + days;
                hours = (String(hours).length >= 2) ? hours : '0' + hours;
                minutes = (String(minutes).length >= 2) ? minutes : '0' + minutes;
                seconds = (String(seconds).length >= 2) ? seconds : '0' + seconds;

                // based on the date change the refrence wording
                var ref_days = (days === 1) ? 'дней' : 'дней',
                    ref_hours = (hours === 1) ? 'часов' : 'часов',
                    ref_minutes = (minutes === 1) ? 'минут' : 'минут',
                    ref_seconds = (seconds === 1) ? 'секунд' : 'секунд';

                // set to DOM
                container.find('.days').text(days);
                container.find('.hours').text(hours);
                container.find('.minutes').text(minutes);
                container.find('.seconds').text(seconds);

                container.find('.days_ref').text(ref_days);
                container.find('.hours_ref').text(ref_hours);
                container.find('.minutes_ref').text(ref_minutes);
                container.find('.seconds_ref').text(ref_seconds);
            }

            // start
            intervalTimer = setInterval(countdown, 1000);
        };

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

        this.setNewData = function () {
            data = new Date();
            date = new Date();
            console.log('======== setNewData ========');
            console.log(data);
        };

        this.setDeadLine = function () {
            data = new Date();
            let dataTmp = data;

            dataTmp = new Date(dataTmp.getTime() + (deadLineTimerMin * 60 * 1000));

            let year = dataTmp.getFullYear();
            let month = dataTmp.getMonth() + 1;

            if (month === 1) {
                month = '01';
            } else if (month === 2) {
                month = '02';
            } else if (month === 3) {
                month = '03';
            } else if (month === 4) {
                month = '04';
            } else if (month === 5) {
                month = '05';
            } else if (month === 6) {
                month = '06';
            } else if (month === 7) {
                month = '07';
            } else if (month === 8) {
                month = '08';
            } else if (month === 9) {
                month = '09';
            }

            let day = dataTmp.getDate();

            if (day === 1) {
                day = '01';
            } else if (day === 2) {
                day = '02';
            } else if (day === 3) {
                day = '03';
            } else if (day === 4) {
                day = '04';
            } else if (day === 5) {
                day = '05';
            } else if (day === 6) {
                day = '06';
            } else if (day === 7) {
                day = '07';
            } else if (day === 8) {
                day = '08';
            } else if (day === 9) {
                day = '09';
            }

            let hours = dataTmp.getHours();
            let minutes = dataTmp.getMinutes();

            deadline = '' + year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':00';
            console.log(deadline);
        };

        this.startPublishOwnFeedScreen = function () {

            this.setNewData();
            this.setDeadLine();
            this.setFileNameFile();

            let publish = {
                "id": 666,
                "request": "configure",
                "audio": true,
                "video": true,
                "record": true,
                "filename": fileNameMain
            };

            screentest.send({"message": publish, "jsep": jsepMain});

            $('.countdown').downCount({
                    date: deadline,
                },
                function () {
                    /* действие после завершения таймера */
                    $('#stopRecord').click();
                    // bootbox.alert("Время истекло!");
                }
            );

            /**
             * Добавить 15 минут
             */
            $('#addTimeForRecord').on('click', function () {

                function addTimeToLine(mForAdd) {

                    let newDateObj = moment(settings.date).add(mForAdd, 'm').toDate();
                    moment.locale('ru');
                    let now = moment();

                    let event1 = moment(newDateObj);
                    let diff = event1.subtract(now.toObject()).format('HH:mm:ss');
                    let diffMoment = moment(diff, 'HH:mm:ss');
                    let deadLineMoment = moment('08:00:00', 'HH:mm:ss');
                    let diffInMinute = deadLineMoment.diff(diffMoment) / 60000;
                    let deadLineInMinute = 8 * 60;
                    let finalDiff = deadLineInMinute - diffInMinute;

                    if (finalDiff < deadLineInMinute) {
                        settings.date = newDateObj;
                    } else {
                        let minToDead = deadLineInMinute - finalDiff;
                        if (minToDead < 0) minToDead = ~~(minToDead * (-1));

                        for (let i = 0; i < minToDead; i++) {
                            addTimeToLine(1);
                        }
                    }
                }

                addTimeToLine(15);
            });
        };

        this.renameClick = function (e, recordId) {
            if (e === 'show') {
                $.ajax({
                    url: '/record/record-control-modal-form',
                    method: 'GET',
                    data: {recordId: recordId},
                    //dataType: 'json',
                    success: function (dataFileLink) {
                        $('#modal-content #content').remove();
                        $('#modal-content').append("<div id='content'></div>");
                        $('#modal-content #content').append(dataFileLink);
                        $('#rename-modal').show();
                    },
                    error: function (error) {
                        bootbox.alert("Error: " + error);
                    }
                });
            }
        };

        this.setFileNameFile = function () {
            //  this.setNewData();

            data = new Date();

            let dataTmp = {
                day: data.getDate(),
                month: (data.getMonth() + 1),
                year: data.getFullYear(),
                hour: data.getHours(),
                minute: data.getMinutes(),
                second: data.getSeconds(),
                milliseconds: data.getMilliseconds()
            };

            let D1 = {};
            for (let nn in dataTmp) {
                D1[nn] = (parseInt(dataTmp[nn], 10) < 10) ? ('0' + dataTmp[nn]) : (dataTmp[nn]);
            }

            let zz = D1.year + '-' + D1.month + '-' + D1.day;
            zz = zz + '_' + D1.hour + '-' + D1.minute + '-' + D1.second; // + '-' + D.milliseconds;

            let fileName1 = "screen_" + zz;

            let myDir1 = siteUser.documentRoot;

            myDir1 = myDir1.replace(/web/g, '');

            let recordsFolder1 = siteUser.recordsFolderTmp;

            myDir1 = myDir1 + recordsFolder1 + '/';

            fileNameMain = myDir1 + fileName1;
        };

        this.stopPublishOwnFeedScreen = function () {

            let publish = {
                "id": 666,
                "request": "configure",
                "audio": true,
                "video": true,
                "record": false,
                "filename": fileNameMain
            };

            screentest.send({"message": publish, "jsep": jsepMain});

            clearInterval(intervalTimer);
            $.ajax({
                url: '/record/get-last-file',
                method: 'POST',
                success: function (filename) {
                    if (filename != null) {

                        $.ajax({
                            url: '/api/new-record',
                            data: {
                                filename: filename,
                                duration: 0,
                                room_id: myroom1
                            },
                            method: 'GET',
                            success: function (recordId) {
                                if (recordId) {
                                    let myStartTeacherScreenControllerTmp = new startTeacherScreenController();
                                    myStartTeacherScreenControllerTmp.renameClick('show', recordId);
                                }
                            },
                            error: function (error) {
                                bootbox.alert("Error: " + error);
                            }
                        });
                    }
                },
                error: function (error) {
                    bootbox.alert("Error: " + error);
                }
            });

        };

        function publishOwnFeedScreen(useAudio) {

            screentest.createOffer(
                {
                    iceRestart: true,
                    media: {video: capture1, audioSend: true, videoRecv: false, myStatus: 'screen'},	// Screen sharing Publishers are sendonly
                    success: function (jsep) {

                        jsepMain = jsep;

                        var data = new Date();
                        var d = {
                            day: data.getDate(),
                            month: (data.getMonth() + 1),
                            year: data.getFullYear(),
                            hour: data.getHours(),
                            minute: data.getMinutes(),
                            second: data.getSeconds(),
                            milliseconds: data.getMilliseconds()
                        };

                        var D = {};
                        for (let n in d) {
                            D[n] = (parseInt(d[n], 10) < 10) ? ('0' + d[n]) : (d[n]);
                        }

                        var z = D.year + '-' + D.month + '-' + D.day;
                        z = z + '_' + D.hour + '-' + D.minute + '-' + D.second + '-' + D.milliseconds;

                        var fileName = "screen_" + z;

                        var myDir = siteUser.documentRoot;

                        myDir = myDir.replace(/web/g, '');

                        var recordsFolder = siteUser.recordsFolderTmp;

                        myDir = myDir + recordsFolder + '/';

                        fileNameMain = myDir + fileName;

                        var publish = {
                            "id": 666,
                            "request": "configure",
                            "audio": true,
                            "video": true,
                            "record": false,
                            "filename": fileNameMain
                        };
                        screentest.send({"message": publish, "jsep": jsep});
                    },
                    error: function (error) {
                        Janus.error("WebRTC error:", error);
                        bootbox.alert("Вы отменили трансляцию рабочего стола. Чтобы включить трансляцию рабочего стола обновите текущую страницу (Ctrl+R) или нажмите F5.");
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
                    // window.location.reload(true);
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
            var desc = $('#desc').val();
            role = "publisher";
            var create = {"request": "create", "description": desc, "bitrate": 3000000, "publishers": 1};
            screentest.send({
                "message": create, success: function (result) {
                    console.log(result);
                    var event = result["videoroom"];
                    Janus.debug("Event: " + event);
                    if (event != undefined && event != null) {
                        // Our own screen sharing session has been created, join it
                        room = result["room"];
                        Janus.log("Screen sharing session created: " + room);
                        myusername = randomString(12);
                        var register = {
                            "request": "join",
                            "room": Number(myroom1),
                            "ptype": "publisher",
                            "display": myusername
                        };
                        screentest.send({"message": register});
                    }
                }
            });
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

        this.init = function () {

            var startProject = Number($('#startProject').val());
            var startScreen = Number($('#startScreen').val());

            if (startProject && startScreen) {

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

                                    if ($('div').is('#loadingJanus')) {
                                        if ($('#loadingJanus').is(":hidden")) {
                                            $('#loadingJanus').show();
                                        }
                                    }

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

                                                    screentest.send({
                                                        "message": {
                                                            "request": "configure",
                                                            "bitrate": bitrate
                                                        }
                                                    });
                                                    $('#bitrate1').hide();

                                                    return false;
                                                });

                                            },
                                            onmessage: function (msg, jsep) {

                                                let tmpRoom = msg['room'];

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

                                                    //console.log("!!!!!!!!! The room has been destroyed !!!!!!!!!!11");

                                                    // bootbox.alert("The room has been destroyed", function () {
                                                    //     window.location.reload(true);
                                                    // });
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
                                                        //     $.ajax({
                                                        //         method: 'POST',
                                                        //         dataType: 'json',
                                                        //         url: '/ajax/refresh-user-situation',
                                                        //         data: {userId: $('#userBlock' ).data('user') },
                                                        //         success: function (data) {
                                                        //             //  window.location.reload(true);
                                                        //         }
                                                        //     });
                                                    }
                                                }

                                                if (jsep !== undefined && jsep !== null) {

                                                    Janus.debug("Handling SDP as well...");
                                                    Janus.debug(jsep);

                                                    screentest.handleRemoteJsep({jsep: jsep});
                                                }
                                            },
                                            onlocalstream: function (stream) {
                                                $.ajax({
                                                    method: 'POST',
                                                    dataType: 'json',
                                                    url: '/ajax/get-server-room',
                                                    success: function (data) {
                                                        let obServer = new startTeacherScreenObserverController();
                                                        obServer.starterObserver(Number(myroom1), data['opaqueIdScreen']);
                                                    },
                                                    error: function (error) {
                                                        //console.log('onLocalStream error');
                                                        //console.log(error);
                                                    }
                                                });
                                            },
                                            onremotestream: function (stream) {
                                                //  //console.log(stream);
                                            },
                                            oncleanup: function () {
                                                Janus.log(" ::: Got a cleanup notification: we are unpublished now :::");
                                                mystream1 = null;
                                                $('#videolocal').html('<button id="publish" class="btn btn-primary">Publish</button>');

                                                $('#publish').click(function () {
                                                    publishOwnFeedScreen(true);
                                                });

                                                $("#videolocal").parent().parent().unblock();
                                                $('#bitrate').parent().parent().addClass('hide');
                                                $('#bitrate a').unbind('click');
                                            }
                                        });
                                },
                                error: function (error) {
                                    Janus.error(error);
                                },
                                destroyed: function () {
                                }
                            });
                    }

                });
            }
        };

        this.destroy = function () {
            janus1.destroy();
        }

    }
;

$(document).ready(function () {

    navigator.mediaDevices.enumerateDevices().then(function (devices) {
        let audioExist = devices.some(function (device) {
            return device.kind === 'audioinput';
        });

        if (!audioExist) {
            $('#teacherScreenBlock').hide();
            $('#teacherScreenBlock:nth-child(even)').hide();
            $('#teacherScreenBlock:nth-child(odd)').hide();
            $('#noErrorDevice').hide();
            $('#teacherScreenBlockPanel').hide();
            $('#errorDevice').show();

            $('#myInfoBlockForErrorDevice').show();

            return false;
        } else {
            $('#myInfoBlockForErrorDevice').hide();
            $('#errorDevice').hide();

            $('#noErrorDevice').show();
            $('#teacherScreenBlock').show();
            $('#teacherScreenBlockPanel').show();

            $('#teacherScreenBlock:nth-child(even)').show();
            $('#teacherScreenBlock:nth-child(odd)').show();

            var myStartTeacherScreenController = new startTeacherScreenController();
            myStartTeacherScreenController.init(function () {
                    return true;
                }
            );

            // проверим, есть ли уже у нас запущенный и не законченный эти преподом урок



            $('#startRecord').on('click', function () {
                seconds = null;
                myStartTeacherScreenController.startPublishOwnFeedScreen();
                $(this).hide();
                $('#stopRecord').show();
                $('.countdown').show();
                $('#addTimeForRecord').show();
            });

            $('#stopRecord').on('click', function () {
                myStartTeacherScreenController.stopPublishOwnFeedScreen();
                $('#startRecord').show();
                $(this).hide();
                $('.countdown').hide();
                $('.minutes').html('00');
                $('.seconds').html('00');
                $('#addTimeForRecord').hide();
            });

        }
    });
});