<?php

/**
 *
 *
 * ВЬЮХА [views]
 * Диалогового
 * Окна, которое должно служить для управления альтернативным именем записи трансляции
 * и разлагается [,в свою очередь,] до след. логических элементов:
 *
 * 1) Заголовок: Название записи ($record->new_name)
 * 2) Текстовое поле: Укажите название записи трансляции; значение по умолчанию: Трансляция от ГГГГ-ММ-ДД_ЧЧ-ММ-СС
 * [где: ГГГГ-ММ-ДД_ЧЧ-ММ-СС - время запуска процесса записи трансляции]
 * ($record->alt_name)
 *
 * 3) Кнопка «ОК» - должна служить для сохранения альтернативного имени записи трансляции
 * 4) Кнопка «Отмена» - должна служить для отмены ввода альтернативного названия записи
 *
 *
 *
 * @var app\models\Records $record ;
 *
 * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
 * @data 13.09.2019
 */

use app\models\Records;

/**
 * Функция порожденная в нашу юдоль скорби, плача и печали, дабы не писать никому здоровеееееенное такое условие в теле атрибута value,
 *  необходимое нам, в свою очередь, исключительно эха названия Записи ради (лол)
 *
 * @param Records $record
 * @return bool
 *
 * @author Ilya a.k.a @decilya <ilya.v87v@gmail.com>
 * @data 13.09.2019
 */
function testRecordNewName(Records $record)
{
    return ((!empty($record->new_name)) || ($record->new_name != null) || ($record->new_name != ''));
}

?>
<script>
    $(document).ready(function () {
        recordControlModalFormJs.initRecordControlModalForm();
    });
</script>
<section class="vjs-modal-dialog vjs-modal-dialog-content container">
    <div class="row container container-content content">
        <header>
            <h1>Переименовать запись</h1>
        </header>
        <div class="mdl-textfield disabledText">
            <label class="" for="realName">Название записи:</label>
            <input id="realName" class="mdl-textfield__input"
                   value="<?php if (testRecordNewName($record)) {
                       $record->new_name = trim($record->new_name);
                       echo $record->new_name;
                   }
                   ?>"
                   disabled="disabled"
            />
        </div>
        <div class="mdl-textfield">
            <label class="" for="altName">Новое название:</label>
            <input id="altName" data-id="<?= $record->id; ?>" name="Records[alt_name]" class="mdl-textfield__input"
                   value=""
                   placeholder="Трансляция от ГГГГ-ММ-ДД_ЧЧ-ММ-СС"/>
        </div>
        <footer>
            <div class="row">
                <div class="col-sm-6 col-lg-6 col-md-6">
                    <button id="escBtn" class="btn alert-warning">Отмена</button>
                </div>

                <div class="col-sm-6 col-lg-6 col-md-6">
                    <button id="okBtn" class="btn alert-success">ОК</button>
                </div>
            </div>
        </footer>
    </div>
</section>

