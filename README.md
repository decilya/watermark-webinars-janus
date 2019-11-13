*Система*
-------------
Ubuntu-Server 18.04

sudo apt-get install nginx mysql-server
sudo apt-get install php-common php-curl php-fpm php-gd php-intl php-mbstring php-mysql php-xml

*Composer, vendor и прочая первоначальная настройка*
-------------------------------

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

php composer-setup.php

php -r "unlink('composer-setup.php');"


php composer.phar global require "fxp/composer-asset-plugin:^1.2.0"


php composer.phar install
или
php composer.phar update

sudo chown -R www-data runtime/
sudo chown -R www-data web/assets/

После этого необходимо

1) скопировать конфиги из config/origin в config

2) написать в файле config/db.php название базы (самому создавать БД НЕ НУЖНО), в последующем база создается с помощью скрипта (незабыть указать и подходящий для mysql логин и пароль)
W
3) в консоли выполнить команду

php yii app/create-db

4) Далее стандартно -  просто применить все миграции

php yii migrate

Затем создадим админа системы php yii app/add-admin "admin-email@email.com"

Примечание: админка вынесена в отдельный модуль modules/admin (доступ через http://bla-bla.foo/admin)

5) Необходимо создать папку для записей в корне проекта:

a) mkdir records-tmp/
chown -R www-data records-tmp/

b) mkdir records/
chown -R www-data records/

c)  mkdir records-user/
 chown -R www-data records-user/


 Создадим ссылку на дерикторию 
ln -s /var/www/html/watermark.wrk/records-user/ /var/www/html/watermark.wrk/web/records-user

+ создадим папку для админа
ln -s /var/www/html/watermark.wrk/records /var/www/html/watermark.wrk/web/records

+ создадим папку для создания записи 
ln -s /var/www/html/watermark.wrk/records-tmp /var/www/html/watermark.wrk/web/records-tmp


 ===============================
 Ручной запуск обратботки запросов видео:

 sudo python3 commands/recordbuilder/wm_build_records.py -c commands/recordbuilder/cjr.conf

 (для исспользования команды необходимо скопировать commands/recordbuilder/conf.origin/cjr.conf на ОДИН уровень вверх, чтобы было как в команде, л - логика)
 ================================

*JANUS*
-------------

Демо+Документация
https://janus.conf.meetecho.com/
GIT
https://github.com/meetecho/janus-gateway
Исходники пакетов
https://launchpad.net/ubuntu/+source/janus/