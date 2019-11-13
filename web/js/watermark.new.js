class WaterMark {

    constructor(video, canvas, image) {

        image = image || undefined;
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.video = video;
        this.waterMark = image;
        this.wmURL = undefined;
        this.canvasSizingObject = {};
        this.yOffset = 0;
        this.xOffset = 0;
        this.watermarkPosition = 1;
        this.targetWidth = 0;
        this.targetHeight = 0;
        this.textWidth = 10;
        this.textHeight = 10;
        this.colorRandom = false;
        this.enableLSD = false;
    }


    // setters for Canvas
    set newCanvasById(id) {
        this.canvas = document.getElementById(id);
    }

    set newCanvasByClass(newClass) {
        this.canvas = document.getElementsByClassName(newClass);
    }

    set selectCanvasId(id) {
        id = id || 0;
        this.canvas = this.canvas[id]
    }

    set canvasSizeFromOtherDomObjectById(id) {
        this.canvasSizingObject = document.getElementById(id);
    }


    // setters for Video
    set newVideoById(id) {
        this.video = document.getElementById(id);
    }

    set newVideoByClass(newClass) {
        this.video = document.getElementsByClassName(newClass);
    }

    set selectVideoId(id) {
        id = id || 0;
        this.video = this.video[id]
    }


    // setters for watermark
    set newWatermark(image) {
        this.waterMark = image;
    }

    set watermarkURL(url) {
        this.wmURL = url;
    }

    // setter for text

    set canvasText(text) {
        this.text = {
            name: text.name,
            address: text.address,
            phone: text.phone,
            email: text.email,
            color: text.color,
        };
    }

    set LSD(boolean) {
        this.enableLSD = boolean;
    }
    set textRandomColor(boolean) {
        this.colorRandom = boolean;
    }
    set displayByText(boolean) {
        this.textDisplay = boolean;
    }
    // getters
    get data() {
        return [this.canvas, this.video, this.waterMark, this.canvasSizingObject, this.image];
    }


    async fetchWaterkarkImageByURL() {
        if(this.wmURL !== undefined) {
            let response = await fetch(this.wmURL);
            let image = await response.blob();

            let img = new Image;

            img.src = URL.createObjectURL(image);
            this.image = img;
        } else {
            return new Error('Отсутсвует ссылка на изображение. Выполните WaterMark().watermarkURL(`url`) чтобы задать ссылку для скачивания изображения.')
        }
    }

    startLoopDrawCanvas(timeout, canvasWidth, canvasHeight) {
        timeout = timeout  || 20;

        if(this.canvasSizingObject !== undefined || this.canvasSizingObject !== {}) {
            canvasWidth = canvasWidth || this.canvasSizingObject.clientWidth;
            canvasHeight = canvasHeight || this.canvasSizingObject.clientHeight;
        }

        this.canvas.width = canvasWidth;
        this.canvas.height = canvasHeight;

        if(this.video.videoWidth / this.video.videoHeight >= 1.77) {
            this.targetWidth = canvasWidth;
            this.targetHeight = canvasWidth / (this.video.videoWidth / this.video.videoHeight);
            this.xOffset = 0;
            this.yOffset = (canvasHeight - this.targetHeight) / 2
        } else {
            this.targetWidth = canvasHeight * (this.video.videoWidth / this.video.videoHeight);
            this.targetHeight = canvasHeight;
            this.yOffset = 0;
            this.xOffset = (canvasWidth - this.targetWidth) / 2
        }

        let textRatio = canvasWidth / 80;
        let textForCanvas = `${this.text.name}\n${this.text.phone === undefined || this.text.phone === '' ? this.text.phone : this.text.address}\n${this.text.email}`;
        let lines = textForCanvas.split('\n');
        this.textHeight = textRatio < 14 ? 14 : canvasWidth / 80;

        if(this.image.complete !== false) {

            this.ctx.drawImage(this.video, this.xOffset, this.yOffset, this.targetWidth, this.targetHeight);
            if(this.text !== undefined && this.textDisplay) {

                this.ctx.font = `${this.textHeight}pt Arial`;
                this.ctx.textBaseline = 'top';
                this.ctx.fillStyle = this.enableLSD ? '#'+Math.floor(Math.random()*16777215).toString(16) : this.text.color;
                let tempTextWidth = [];

                for (let j = 0; j<lines.length; j++) {
                    this.ctx.fillText(lines[j], this.watermarkXOffset, this.watermarkYOffset + (j * (this.textHeight+10)) );
                    let canvasTextWidth = this.ctx.measureText(lines[j]).width;
                    tempTextWidth.push(Math.floor(canvasTextWidth));
                }

                this.textWidth = this.getMaxOfArray(tempTextWidth);

            } else {

                this.ctx.drawImage(this.image, this.watermarkXOffset, this.watermarkYOffset)

            }

        }

        this.timeoutId = setTimeout(() => { this.startLoopDrawCanvas() }, timeout);
    }
    enableReloadBySize(boolean, size) {
        size = size || false;

        if(boolean) {
            this.reloadBySize = size;
        } else {
            this.reloadBySize = boolean;
        }

    }
    startLoopDrawWatermark() {
        if(this.reloadBySize && this.canvasSizingObject.offsetWidth > this.reloadBySize) {
            window.location.reload();
        }
        if(this.watermarkXOffset === undefined ) {
            this.watermarkXOffset = this.xOffset;
            this.watermarkYOffset = this.yOffset;
        }

        if(this.colorRandom) {
            this.text.color = '#'+Math.floor(Math.random()*16777215).toString(16);
        }
        switch(this.watermarkPosition) {
            case 1:
                this.watermarkXOffset = this.xOffset + 5;
                this.watermarkYOffset = this.yOffset + 5;
                break;
            case 2:
                if(this.textDisplay) {
                    this.watermarkXOffset = this.targetWidth + this.xOffset - this.textWidth - 5;
                } else {
                    this.watermarkXOffset = this.targetWidth + this.xOffset - 405;
                }
                this.watermarkYOffset = this.yOffset + 5;
                break;
            case 3:
                this.watermarkXOffset = this.xOffset + 5;

                if(this.textDisplay) {
                    this.watermarkYOffset = this.targetHeight + this.yOffset - (( this.textHeight + 10 ) * 3);
                } else {
                    this.watermarkYOffset = this.targetHeight + this.yOffset - 85;
                }
                break;
            case 4:

                if(this.textDisplay) {
                    this.watermarkXOffset = this.targetWidth + this.xOffset - this.textWidth - 5;
                    this.watermarkYOffset = this.targetHeight + this.yOffset - (( this.textHeight + 10 ) * 3);
                } else {
                    this.watermarkXOffset = this.targetWidth + this.xOffset - 405;
                    this.watermarkYOffset = this.targetHeight + this.yOffset - 85;
                }
                break;
        }

        this.watermarkPosition = this.randomInteger(1,4, this.watermarkPosition);

    }
    getMaxOfArray(numArray) {
        return Math.max.apply(null, numArray);
    }
    randomInteger(min, max, currentNumber) {
        // получить случайное число от (min-0.5) до (max+0.5)
        let rand = min - 0.5 + Math.random() * (max - min + 1);
        return Math.round(rand) === currentNumber ? this.randomInteger(min, max, currentNumber) : Math.round(rand);
    }
}

setTimeout(() => {
    let watermark = new WaterMark(videoCanvas, document.getElementById('videoWM'));
    watermark.canvasSizeFromOtherDomObjectById = 'videoWaterMark';
    watermark.watermarkURL = `${window.location.protocol}//${window.location.host}/site/watermark`;
    watermark.textRandomColor = true;
    watermark.displayByText = true;
    watermark.canvasText = waterMarkInfo;
    watermark.LSD = false;
    watermark.enableReloadBySize(false, 2500);
    watermark.fetchWaterkarkImageByURL()
        .then( () => {
            watermark.startLoopDrawWatermark();
            setInterval(() => { watermark.startLoopDrawWatermark(); }, 3000);
            watermark.startLoopDrawCanvas(25);
        })
}, 5000)
