
## Update database

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate

## Run fixtures

    php bin/console doctrine:fixtures:load --append

## Run server

    php -S 127.0.0.1:8000 -t public

## Deploy to heroku

    heroku addons:create heroku-postgresql:hobby-dev
    git push heroku master
    heroku run 'php bin/console doctrine:schema:create'
    heroku run 'php bin/console doctrine:fixtures:load --env=prod --append'