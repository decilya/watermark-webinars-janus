var serverStatus = null;
var interval = 3000;
var mySetInterval;
var event = document.createEvent('Event');
var statusProject = undefined;

event.initEvent('checkServer', true, true);

function checkStatus(interval, reload) {

    reload = reload || undefined;

    clearTimeout(mySetInterval);

    mySetInterval = setTimeout(
        function () {

            let count = 0;

            if (statusProject === true) {

                serverStatus = true;

                if (interval === 1000) {
                    interval = 3000;

                    checkStatus(interval, reload);
                }
            } else if (statusProject === false) {
                count++;

                if (interval === 3000) {

                    interval = 1000;
                    checkStatus(interval, reload);
                }

                if (count === 3) {

                    serverStatus = false;

                    if (reload === true) window.location.reload(true);
                }

            }

            document.dispatchEvent(event);
            checkStatus(interval, reload);

        }, interval
    );
}

$(document).ready(function () {
    checkStatus(interval);
});

