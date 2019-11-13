let recordControlModalFormJs = {

    init: function () {
        this.requestClick();
        this.seeClick();
        this.renameClick();
        this.renameModalClose();
        this.delClick();
    },

    /**
     * Запросить
     *
     * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
     * @data 29.11.2018
     *
     */
    requestClick: function () {
        $('.request').on('click', function () {

            let myLink = $(this);
            let fileName = myLink.data('name');
            let userId = myLink.data('user');

            $.ajax({
                url: '/ajax/set-request-file',
                method: 'POST',
                data: {fileName: fileName, userId: userId},
                dataType: 'json',
                success: function () {
                    myLink.hide();
                    myLink.parent().find('.coming').show();
                },
                error: function (error) {
                    bootbox.alert("Error: " + error);
                }
            });
        });
    },

    /**
     * Смотреть
     *
     * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
     * @data 29.11.2018
     */
    seeClick: function () {
        $('.see').on('click', function () {

            let myLink = $(this);
            let fileName = myLink.data('name');
            let userId = myLink.data('user');

            $.ajax({
                url: '/ajax/see-file',
                method: 'POST',
                data: {fileName: fileName, userId: userId},
                dataType: 'json',
                success: function (dataFileLink) {
                    let videoBlock = $('#videoBlock');
                    videoBlock.show();
                    $("#videoBlock")[0].scrollIntoView();
                    let video = $('#myVideo');
                    let src = video.attr('src');
                    video.attr('src', dataFileLink, +'&autoplay=1');
                },
                error: function (error) {
                    console.log(error);
                    bootbox.alert("Error: " + error);
                }
            });
        });
    },
    delClick: function () {
        $('.delete').on('click', function () {
            let myLink = $(this);
            let fileName = myLink.data('name');
            let userId = myLink.data('user');
            let altFileName = myLink.data('altname');
            let fileId = myLink.data('id');

            bootbox.dialog({
                title: 'Удаление записи трансляции',
                message: 'Вы действительно хотите безвозвратно удалить запись трансляции "' + altFileName + '"?',
                buttons: {
                    ok: {
                        label: "ОК",
                        className:
                            "btn-primary",
                        callback:

                            function () {
                                $.ajax({
                                    url: '/ajax/delete-file',
                                    method: 'POST',
                                    data: {fileName: fileName, userId: userId, altFileName: altFileName},
                                    dataType: 'json',
                                    success: function (dataFileLink) {
                                        $(".myItem[data-id='" + fileId + "']").remove();
                                    },
                                    error: function (error) {
                                        console.log(error);
                                        bootbox.alert("Error: " + error);
                                    }
                                });
                            }
                    },
                    esc: {
                        label: "Отмена",
                        className:
                            "btn-success",
                        callback:
                            function () {
                                //
                            }
                    }
                }
            });


        });
    },

    /**
     * Переименовать
     *
     * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
     * @data 13.09.2019
     */
    renameClick: function () {
        $('.rename').on('click', function () {
                let recordId = $(this).data('id');
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
        )
    },

    /**
     * Закрыть модальное окно с переименованием Записи
     *
     * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
     * @data 24.09.2019
     */
    renameModalClose: function () {

        $('#rename-modal .close').on('click', function () {
            $('#rename-modal').hide();
            recordControlModalFormJs.stickTogether();
        });

    },

    /**
     *  Склеить
     */
    stickTogether: function () {
        if (window.location.href.indexOf("translate-room") > -1) {
            // так вот, если мы находиммя на странице site/translate-room то над б склеивать с прошлыми null
            $.ajax({
                url: '/ajax/stick-together',
                method: 'POST',
                dataType: 'json',
                success: function () {
                    alert('готово');
                },
                error: function (error) {
                    bootbox.alert("Error: " + error);
                }
            });

        }
    },

    /**
     * Методы для модального окна _record-control-modal-form.php
     *
     * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
     * @data 26.09.2019
     */
    initRecordControlModalForm: function () {
        // фокус на инпуте ввода названия
        $('#altName').focus();

        /**
         * Модалка "Переименовать запись" кнопка Отмена тык
         *
         * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
         * @data 26.09.2019
         */
        $('#escBtn').on('click', function () {
            $('#rename-modal .close').click();
        });

        /**
         * Модалка "Переименовать запись" кнопка Ок тык
         *
         * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
         * @data 26.09.2019
         */
        $('#okBtn').on('click', function () {
            let altName = $('#altName').val().trim();
            let recordId = $('#altName').attr('data-id');

            if ((altName !== '') && (altName !== null)) {
                $.ajax({
                    url: '/ajax/set-alt-name-record',
                    method: 'POST',
                    data: {recordId: recordId, altName: altName},
                    dataType: 'json',
                    success: function () {
                        $('.nameRecord[data-id="' + recordId + '"]').html(altName);
                        $('#rename-modal .close').click();

                        // А теперь проверим атрибут, если он есть то это окно со страницы трансляции и нужно добавить 
                        // id для всех записей без билд айдишника для их склейке
                        recordControlModalFormJs.stickTogether();
                    },
                    error: function (error) {
                        bootbox.alert("Error: " + error);
                    }
                });
            } else {
                bootbox.alert('Введите новое название записи');
            }
        });
    }
};


$(document).ready(function () {
    recordControlModalFormJs.init();
});