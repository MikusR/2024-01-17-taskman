# A simple Task Manager app

[Test instance](https://taskman.mikusr.info)

Application for making a simple list of tasks

## Screenshots

Main screen
![Main](docs/main.png)

A single task view
![View](docs/view.png)

Simple validation
![Validation](docs/validation.png)

Custom error messages
![Error](docs/error.png)

## Requirements

- PHP 8.3
- MySQL (tested on 8.0.35)

## Install

clone repository

```bash
$ git clone https://github.com/MikusR/2024-01-17-taskman.git
```

use Composer to get dependencies

```bash
$ composer install
```

copy .env.example to .env
and configure access to database
for example:

```ini
TIMEZONE = "Europe/Riga"
DBNAME = "task_manager"
DBUSER = "taskman"
PASSWORD = "taskman"
HOST = "localhost"
DRIVER = "pdo_mysql"
```

database uses this table

```sql
create table tasks
(
    id               int auto_increment
        primary key,
    task_name        varchar(255) null,
    task_description text         null,
    created_at       datetime     null
);

```

run the application from /public directory
for example using the built-in webserver

```bash
 php -S localhost:8765 -t .\public
```

If using apache then check if /etc/apache2/apache2.conf has AllowOverride and use .htaccess. Also check if mod_rewrite
is enabled

```ini
RewriteEngine On
RewriteCond %{REQUEST_URI} : :$0 ^(/.+)/(.*)::\2$
RewriteRule .* - [E=BASE:%1]
RewriteCond %{ENV : REDIRECT_STATUS} =""
RewriteRule ^index\.php(? : /(.*)|$) %{ENV:BASE}/$1 [R=301,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ %{ENV : BASE}/index.php [L]
```

## Used packages

- doctrine/dbal (access to database)
- nesbot/carbon (better time and date)
- twig/twig (template engine)
- https://simplecss.org/ (styling)
- nikic/fast-route (for routing)
- vlucas/phpdotenv (configuration environment)

## App structure and choices

- [public/index.php](public/index.php) entry point for the application.
- [app/Application.php](app/Application.php) deals with setting up the environment, routing. Calls the main controller
  and delegates to Twig.
- [app/Controllers/TaskController.php](app/Controllers/TaskController.php) creates connection to database and creating
  of Tasks.
- [app/Models](app/Models) contains the used models.
- [app/Views](app/Views) contains the Twig templates
