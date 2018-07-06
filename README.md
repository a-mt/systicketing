
# Systicketing

## Install

    composer install

Create a file `.env` and put your settings in it

    # This file is a "template" of which env vars need to be defined for your application
    # Copy this file to .env file for development, create environment variables when deploying to production
    # https://symfony.com/doc/current/best_practices/configuration.html#infrastructure-related-configuration

    ###--------------------------------------------------------
    ###> symfony/framework-bundle ###
    ###--------------------------------------------------------
    APP_ENV=dev
    APP_SECRET=040e05ddzkev64fb8ce55e0fjgb89bec

    ###--------------------------------------------------------
    ###> doctrine/doctrine-bundle ###
    ###--------------------------------------------------------
    # Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Configure your db driver and server_version in config/packages/doctrine.yaml

    DATABASE_URL="mysql://username:password@127.0.0.1:3306/dbname"
    DATABASE_DRIVER="pdo_mysql"
    DATABASE_CHARSET="utf8mb4"
    DATABASE_COLLATE="utf8mb4_unicode_ci"
    # pdo_pgsql UTF8 UTF8

    ###--------------------------------------------------------
    ###> google/apiclient ###
    ###--------------------------------------------------------
    GOOGLE_CREDENTIALS='{"access_token":"...","token_type":"Bearer","expires_in":3600,"created":1530861260,"refresh_token":"..."}'

Give access to your Google Drive account to upload files

    php bin/console app:create-drive-credentials

Update the `GOOGLE_CREDENTIALS` variable in `.env` with the generated credentials.json file

## Run server

    php -S 127.0.0.1:8000 -t public

## Deploy to heroku

    heroku addons:create heroku-postgresql:hobby-dev
    git push heroku master
    heroku run 'php bin/console doctrine:schema:create'
    heroku run 'php bin/console doctrine:fixtures:load --env=prod --append'
