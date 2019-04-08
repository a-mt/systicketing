
# Systicketing

## Install PHP 7.2

```
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update -y

sudo apt-get install php7.2-curl php7.2-dev php7.2-gd php7.2-intl php7.2-json \
        php7.2-mysql php7.2-pgsql php7.2-opcache php7.2-bcmath php7.2-mbstring php7.2-soap php7.2-xml -y
sudo apt-get install libapache2-mod-php7.2 -y
sudo apt-get install gcc make autoconf libc-dev pkg-config -y
sudo pecl install apcu -y

sudo bash -c "echo extension=apcu.so > /etc/php/7.2/cli/conf.d/20-apcu.ini"
sudo bash -c "echo extension=apcu.so > /etc/php/7.2/fpm/conf.d/20-apcu.ini"
sudo bash -c "echo extension=apcu.so > /etc/php/7.2/apache2/conf.d/20-apcu.ini"

sudo service apache2 stop
sudo a2dismod php5
sudo a2enmod php7.2
sudo service apache2 start
```

## Install App

    composer install

* Copy `.env.example` to `.env`.
* Update the database credentials in it
* Give access to your Google Drive account (to upload files)

  ```
  php bin/console app:create-drive-credentials
  ```

* Put the content of the generated credentials.json file in `GOOGLE_CREDENTIALS`

## Run server

    php -S 127.0.0.1:8000 -t public

## Deploy to heroku

    heroku addons:create heroku-postgresql:hobby-dev
    git push heroku master
    heroku run 'php bin/console doctrine:schema:create'
    heroku run 'php bin/console doctrine:fixtures:load --env=prod --append'

