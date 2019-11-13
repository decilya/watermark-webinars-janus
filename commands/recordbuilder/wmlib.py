import os
import json
import urllib
import requests
import subprocess


class PathNotFound(Exception):
    pass


class BuildError(Exception):
    pass


class PermissionDenied(Exception):
    pass


class ApiError(Exception):
    pass


class FilesDescriptions:
    audio_suffix = '-audio.mjr'
    video_suffix = '-video.mjr'
    src_extension = '.mjr'
    tmp_audio_extension = '.opus'
    tmp_video_extension = '.webm'
    dst_extension = '.webm'


class FileMoverAndCleaner:
    def __init__(self, new_path, age_keep=10):
        if not os.path.exists(new_path):
            os.makedirs(new_path)
        self.new_path = new_path
        self.age_keep = age_keep

    def move(self, source_file):
        new_file = os.path.join(self.new_path, os.path.basename(source_file))
        os.rename(source_file, new_file)
        if not os.path.exists(new_file):
            return False
        return True

    def clear(self):
        pass


class ProcessPid:
    def __init__(self, pid_file):
        self.pid_file = pid_file

    def read_pid_from_file(self):
        if os.path.isfile(self.pid_file):
            return int(open(self.pid_file, 'r').read())
        else:
            return -1

    def create_pid_file(self):
        try:
            open(self.pid_file, 'w').write(str(os.getpid()))
        except PermissionDenied:
            raise PermissionDenied('Не могу создать pid-файл: %s' % self.pid_file)

    @staticmethod
    def check_pid(process_pid):
        if process_pid < 0:
            return False
        try:
            os.kill(process_pid, 0)
        except ProcessLookupError:
            return False
        else:
            return True

    def delete_pid_file(self):
        os.unlink(self.pid_file)


class RecordBuilder(FilesDescriptions):
    def __init__(self, destination_path, tmp_path):
        if not os.path.exists(destination_path):
            os.makedirs(destination_path)
        self.destination_path = destination_path
        if not os.path.exists(tmp_path):
            os.makedirs(tmp_path)
        self.tmp_path = tmp_path
        self.error_files = []

    def __convert_janus_to_temp(self, object):
        file_name = os.path.basename(object)
        new_object = ''
        if file_name.endswith(self.audio_suffix):
            new_object = os.path.splitext(file_name)[0] + \
                         self.tmp_audio_extension
        elif file_name.endswith(self.video_suffix):
            new_object = os.path.splitext(file_name)[0] + \
                         self.tmp_video_extension
        new_object = os.path.join(self.tmp_path, new_object)
        subprocess.check_call(['janus-pp-rec', object, new_object],
                              stdout=subprocess.DEVNULL,
                              stderr=subprocess.DEVNULL)
        # subprocess.call(['/home/aleksei/janus-pp-rec', object, new_object],
        #                  stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        if not os.path.exists(new_object):
            raise BuildError('ERROR. Конвертирование: %s --> %s' %
                             (object, new_object))
        return new_object

    @staticmethod
    def __delete_temp(object):
        if os.path.exists(object):
            os.remove(object)

    # Два файла: звуковая и видео дорожки
    def join(self, first, second):
        new_first_tmp = self.__convert_janus_to_temp(first)
        new_second_tmp = self.__convert_janus_to_temp(second)
        if not new_first_tmp or not new_second_tmp:
            self.error_files.append(first)
            self.__delete_temp(new_first_tmp)
            self.error_files.append(second)
            self.__delete_temp(new_second_tmp)
            return ''
        result_file = os.path.join(self.destination_path,
                                   '-'.join(os.path.splitext(os.path.basename(first))[0].split('-')[0:-1]) + self.dst_extension)
        code = subprocess.call(['ffmpeg', '-y', '-i', new_first_tmp, '-i', new_second_tmp,
                                '-c', 'copy', result_file],
                               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        self.__delete_temp(new_first_tmp)
        self.__delete_temp(new_second_tmp)
        if not os.path.exists(result_file) or code != 0:
            raise BuildError('ERROR. Склейка: %s + %s --> %s' %
                             (new_first_tmp, result_file, result_file))
        return result_file

    def get_duration(self, filename: str) -> int:
        """
        Получение длительности файла записи
        :return положительное число - длительность, -1 - ошибка
        """
        params = ["ffprobe", "-i", filename, "-v", "quiet", "-print_format", "json", "-show_format"]
        try:
            output = subprocess.check_output(params)
            output_json = json.loads(output.decode())
            return output_json['format']['duration']
        except subprocess.CalledProcessError:
            return -1


class WaterMarkApi:
    def __init__(self, api_url):
        self.api_url = api_url
        self.__source_list_method = 'get-files-list'
        self.__new_record_method = 'new-record'
        self.__personal_list_method = 'get-list-for-build'
        self.__result_method = 'build-result'

    @staticmethod
    def __open_url(url, params=''):
        requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)
        try:
            page = requests.get(url, params=params, verify=False)
        except requests.exceptions.MissingSchema as e:
            raise ApiError(e)
        if page.status_code != 200:
            raise ApiError('ERROR. Код ответа api %s - %s' % (url, page.status_code))
        # if 'application/json' not in page.headers['Content-Type']:
        #     raise ApiError('ERROR. Формат ответа api %s не %s' % (url, 'application/json'))
        return page

    def get_source_list(self):
        url = urllib.parse.urljoin(self.api_url, self.__source_list_method)
        return self.__open_url(url).json()

    def new_record(self, params):
        url = urllib.parse.urljoin(self.api_url, self.__new_record_method)
        self.__open_url(url, params)
        return True

    def get_personal_list(self):
        url = urllib.parse.urljoin(self.api_url, self.__personal_list_method)
        return self.__open_url(url).json()

    def post_result(self, params):
        url = urllib.parse.urljoin(self.api_url, self.__result_method)
        self.__open_url(url, params)
        return True


def personal_record_builder(source_file, destination_path, wm_text):
    if not os.path.exists(destination_path):
        os.makedirs(destination_path)
    if not os.path.exists(source_file):
        raise BuildError('ERROR. Нет исходного файла %s' % source_file)
    result_file = os.path.join(destination_path, os.path.basename(source_file))
    code = subprocess.call(['ffmpeg', '-y', '-i', source_file,
                            '-vf', 'drawtext=font=arial:text={}:fontcolor=red@0.5:fontsize=22: \
                             x=if(eq(mod(n\,100)\,0)\,rand(0\,1)*(w-text_w)\,x): \
                             y=if(eq(mod(n\,100)\,0)\,rand(0\,1)*(h-text_h)\,y)'.format(wm_text),
                            '-lossless', '1', '-threads', '16', '-quality', 'realtime',
                            '-speed', '8', '-tile-columns', '6', '-frame-parallel', '1',
                            '-vsync', '2', '-shortest', '-c:a', 'copy', result_file],
                           stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    if not os.path.exists(result_file) or code != 0:
        raise BuildError('ERROR. Не удалось собрать %s' % result_file)
    return True
