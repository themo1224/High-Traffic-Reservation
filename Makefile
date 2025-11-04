up:
\tdocker compose up -d --build

down:
\tdocker compose down

reset:
\tdocker compose down -v
\tdocker compose up -d --build
\tdocker compose exec api composer install
\tdocker compose exec api php artisan key:generate
\tdocker compose exec api php artisan migrate --seed

bash:
\tdocker compose exec api bash

logs:
\tdocker compose logs -f --tail=200

migrate:
\tdocker compose exec api php artisan migrate

seed:
\tdocker compose exec api php artisan db:seed

queue:
\tdocker compose exec horizon php artisan horizon
