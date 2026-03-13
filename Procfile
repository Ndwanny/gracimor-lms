web: php artisan config:cache || true && php artisan route:cache || true && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
worker: php artisan migrate --force && php artisan queue:work --queue=notifications,default --sleep=3 --tries=3 --max-time=3600
