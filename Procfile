web: php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
worker: php artisan queue:work --queue=notifications,default --sleep=3 --tries=3 --max-time=3600
