
## Update database

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate

## Run fixtures

    php bin/console doctrine:fixtures:load --append

## Run server

    php -S 127.0.0.1:8000 -t public