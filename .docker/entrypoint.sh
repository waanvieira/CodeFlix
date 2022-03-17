#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
chown -R www-data:www-data .
chown -R mysql:mysql /var/lib/mysql/code_micro_videos
chown -R mysql:mysql .
composer install
php artisan key:generate
php artisan migrate

php-fpm
