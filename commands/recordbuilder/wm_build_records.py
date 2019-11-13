import os
import sys
import glob
import argparse
import logging
import subprocess
import configparser

import wmlib

pp = wmlib.ProcessPid('/tmp/cjr.pid')
if pp.check_pid(pp.read_pid_from_file()):
    print('Скрипт уже запущен')
    sys.exit(1)
try:
    pp.create_pid_file()
except wmlib.PermissionDenied as e:
    print(e)
    sys.exit(1)

# Обработка аргументов ------------------------------------------------
ap = argparse.ArgumentParser()
ap.add_argument("-c", "--config", required=True, help="configuration file")
args = vars(ap.parse_args())
# args['config'] = './cjr.conf'
if not os.path.exists(args['config']):
    print('Не найден файл конфигурации - %s' % args['config'])
    pp.delete_pid_file()
    sys.exit(1)

# Получаем данные из главной секции -----------------------------------
config = configparser.ConfigParser()
try:
    config.read(args['config'])
except configparser.MissingSectionHeaderError:
    print('ERROR. Неверный формат конфигурационного файла')
    pp.delete_pid_file()
    sys.exit(1)

log_file = config.get('DEFAULT', 'log_file', fallback='./cjr.log')
tmp_path = config.get('DEFAULT', 'tmp_path', fallback='/tmp')

# Включаем логирование ----------------------------------------------------
try:
    logging.basicConfig(filename=log_file, level=logging.INFO,
                        format='%(asctime)s - %(levelname)s - %(message)s')
except (PermissionError, FileNotFoundError):
    logging.error('Не могу открыть лог - %s' % log_file)

logging.info('Запуск')

# Обрабатываем данные из секций площадок -----------------------------------
section_keys = ['source_path_janus', 'prebuild_path', 'fail_path_janus', 'backup_path_janus',
                'api_url', 'personal_path']
section = config.sections()[0]

try:
    for key in section_keys:
        config.get(section, key)
except configparser.NoOptionError as e:
    logging.error('В конфиге в секции [%s] нет ключа %s' % (section, e))
    sys.exit(1)

try:
    rb = wmlib.RecordBuilder(config.get(section, 'prebuild_path'), tmp_path)
    sm = wmlib.FileMoverAndCleaner(config.get(section, 'backup_path_janus'))
    fm = wmlib.FileMoverAndCleaner(config.get(section, 'fail_path_janus'))
    wmapi = wmlib.WaterMarkApi(config.get(section, 'api_url'))

    # Сборка исходных файлов
    for wm_data in wmapi.get_source_list():
        mask = os.path.join(config.get(section, 'source_path_janus'), wm_data['filename'] + '*.*')
        files = glob.glob(mask)

        # Нашли нужное к-во исходных файлов
        success = False
        duration = 0
        result = wm_data['filename']
        if len(files) == 2:
            try:
                result = rb.join(files[0], files[1])

                # При успешной склейки перемещаем в архив исходников
                for i in files:
                    sm.move(i)

                duration = rb.get_duration(result)
                success = True

            except wmlib.BuildError as message:
                print(message)

                # При ошибке склейки перемещаем в архив испорченных исходников
                for i in files:
                    fm.move(i)

            else:
                logging.info('Склейл {0} + {1} --> {2}'.format(files[0], files[1], result))

        status = 1 if success else 2
        api_answer_params = {'id': wm_data['id'], 'filename': os.path.basename(result),
                             'duration': duration, 'status': status}
        wmapi.new_record(api_answer_params)

    # Сборка персональных файлов с ватермарками.
    for wm_data in wmapi.get_personal_list():
        try:
            src_file = os.path.join(config.get(section, 'prebuild_path'),
                                    wm_data['filename'])
            dst_file = os.path.join(config.get(section, 'personal_path'),
                                    wm_data['dest_folder'])
            wm_text = '\n'.join(wm_data.pop('wm').values())
            api_answer_params = {'filename': wm_data['filename'],
                                 'user_hash': wm_data['dest_folder']}
            if wmlib.personal_record_builder(src_file, dst_file, wm_text):
                logging.info('Собрал персональный файл %s' % dst_file)
                api_answer_params.update({'result': 1})
            else:
                api_answer_params.update({'result': 0})
            wmapi.post_result(api_answer_params)
        except wmlib.BuildError as e:
            logging.error(e)
except (wmlib.PathNotFound, wmlib.PermissionDenied, PermissionError,
        subprocess.CalledProcessError, FileNotFoundError, wmlib.ApiError,
        wmlib.BuildError) as e:
    logging.error(e)

logging.info('Остановка')
pp.delete_pid_file()
