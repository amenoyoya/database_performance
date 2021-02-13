# データベースパフォーマンス計測

## Environment

- Shell: `bash`
- Docker: `19.03.12`
    - docker-compose: `1.26.0`

### Structure
```bash
./
|_ docker/
|  |_ db/ # db service container profile
|  |  |_ my.cnf # mysql config file => service://db:/etc/mysql/conf.d/my.cnf:ro
|  |
|  |_ web/ # web service container profile
|     |_ conf/
|     |  |_ 000-default.conf # apache VirtualHost config file => service://web:/etc/apache2/sites-available/000-default.conf
|     |  |_ php.ini # php config file => service://web:/etc/php.d/php.ini
|     |
|     |_ Dockerfile # container building file
|
|_ www/ # project directory => service://web:/var/www/
|  |_ public/ # apache document root
|  :
|
|_ .env # environmental variables file
|_ docker-compose.yml
```

### Docker
- networks:
    - **lampnet**: `bridge`
        - all service containers below belong to this network.
- volumes:
    - **db-data**: `local`
        - volume for `db` service container.
- service containers:
    - **web**: `php:7.4-apache`
        - apache + mod_php web server
        - http://localhost:${WEB_PORT} => service://web:80
            - ENV.WEB_PORT: `7480`
    - **db**: `mysql:5.7`
        - mysql database server
        - database socket: tcp://db:3306
    - **phpmyadmin**: `phpmyadmin/phpmyadmin`
        - phpMyAdmin for `db` service container
        - http://localhost:${PMA_PORT} => service://phpmyadmin:80
            - ENV.PMA_PORT: `5780`
    - **mailhog**: `mailhog/mailhog`
        - mail sending/receiving sandbox server
        - http://localhost:${MAILHOG_PORT} => service://mailhog:8025
            - ENV.MAILHOG_PORT: `2580`
        - mail sending socket: smtp://mailhog:1025

***

## Execution

### Launch Docker containers
```bash
# export USER_ID => web service container user `www-data`
$ export USER_ID=$UID

# build and launch docker containers
$ docker-compose build
$ docker-compose up -d
```

### Prepare php libraries and database
```bash
# install php libraries by composer in web service container
$ docker-compose exec web composer install

# migrate and seed database
$ docker-compose exec web php migrate.php
```
