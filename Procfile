release: php artisan migrate --force && composer dumpautoload 
web: vendor/bin/heroku-php-apache2 -i php_custom.ini public/
worker: php artisan queue:work --sleep=3 --tries=3 --timeout=90 --sleep=3 --memory=128