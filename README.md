# investment-manager

Symfony application to manage investment.

## Table of Contents
- [Getting started](#getting-started)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
    - [Development](#development)
        - [Environment](#environment)
- [Troubleshooting](#troubleshooting)
    - [Known issues](#known-issues)
- [Resources](resources)
    
## Getting started

### Prerequisites

You need to make sure that you have  `docker` installed

```
$ which docker
/usr/local/bin/docker
$ which yarn
/usr/local/bin/yarn
$ which php
/usr/bin/php
```

There is no other prerequisite needed in order to setup this project for development.
### Installation

1. Create a `.env` from the `.env.dist` file. Adapt it according to the symfony application

```bash
cp .env.dist .env
```
    
2. Build/run containers with (with and without detached mode)

```bash
docker-compose -f docker-compose.dev.yml -p investment-manager-dev up -d
```

3. Download Composer dependencies

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```bash
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.

4. Download Yarn dependencies

Make sure you have [Yarn installed](https://yarnpkg.com/en/docs/install)
and then run:

```bash
yarn install
```

5. Setup the Database

Create the database and the schema!

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If you get an error that the database exists, that should
be ok. But if you have problems, completely drop the
database (`doctrine:database:drop --force`) and try again.

6. Load the data fixtures (Optional)

```bash
php bin/console doctrine:fixtures:load
```

## Development

**For convenience**

If you are using PhpStorm you may install and enable
the [Symfony Plugin](https://plugins.jetbrains.com/idea/plugin/7219-symfony-plugin)
via the preferences which provides more auto-completion for Symfony projects. 

**Start the built-in web server**

You can use Nginx or Apache, but the built-in web server works great:

```bash
php bin/console server:run *:8080
```

Now check out the site at `http://localhost:8000`

**Start webpack watcher**

Run the following command

```bash
yarn watch
```

### Environment

Environment variables performs better than using `symfony/dotenv` component. For that reason we will pass the variable read from the file `.env` to the environment container.

#### How to add an environment variable to the container

Define the variable in the file `.env`, don't forget to define it in the file `.env.dist` too

```.dotenv
...
# In all environments, the following files are loaded if they exist,
# the later taking precedence over the former:
#
#  * .env                contains default values for the environment variables needed by the app
#  * .env.local          uncommitted file with local overrides
#  * .env.$APP_ENV       committed environment-specific defaults
#  * .env.$APP_ENV.local uncommitted environment-specific overrides
#
# Real environment variables win over .env files.
#
# DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
#
# Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
# https://symfony.com/doc/current/best_practices/configuration.html#infrastructure-related-configuration

###> symfony/framework-bundle ###
APP_ENV=dev
...
```

## Resources

- Twig documentation [https://twig.symfony.com/doc/2.x/](https://twig.symfony.com/doc/2.x/)
- Select2 documentation [https://select2.org/](https://select2.org/)
- Bootstrap 3.3 documentation [https://getbootstrap.com/docs/3.3/getting-started/](https://getbootstrap.com/docs/3.3/getting-started/)
- Font Awesome Free documentation [https://fontawesome.com/](https://fontawesome.com/)


