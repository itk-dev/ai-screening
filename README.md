# Readme for AI Screening

## Build assets

Run the command below to install assest and tools from package.json

```shell name"assets-install"
docker compose run --rm node yarn install
```

Run the command below to continuesly build assets uppon file changes.

```shell name"assets-watch"
docker compose run --rm node yarn watch
```

Run the command below to continuesly build assets once.

```shell name"assets-watch"
docker compose run --rm node yarn build
```

## Site installation

Run the following commands to set up the site a new. This will start containers
and run composer install, add a settings.php file and run site-install.

```shell name="site-up-new"
task build-site:new
```

If the site has existing config and a settings.php file build the site from that.

```shell name="site-up"
task build-site:existing-conf
```

When the installation is completed, that admin user is created and the password for logging in the outputted. If you
forget the password, use `drush user:login` command to get a one-time-login URL (note: the URI here only works if
you are using Traefik and [ITK-dev docker setup](https://github.com/itk-dev/devops_itkdev-docker)).

```shell name="site-login"
itkdev-docker-compose drush user:login
```

### Access the site

If you are using out `itkdev-docker-compose` simple use the command below to åbne the site in you default browser.

```shell name="site-open"
itkdev-docker-compose open
```

Alternatively you can find the port number that is mapped nginx container that server the site at `http://0.0.0.0:PORT`
by using this command:

```shell
open "http://$(docker compose port nginx 8080)"
```

### Drupal config

Export config created from drupal:

```shell
itkdev-docker-compose drush config:export
```

Import config from config files:

```shell
itkdev-docker-compose drush config:import
```

### Coding standards

```shell name=coding-standards-composer
task compose -- exec phpfpm composer install
task compose -- exec phpfpm composer normalize
```

```shell name=coding-standards-php
docker compose exec phpfpm composer install
docker compose exec phpfpm composer coding-standards-apply/phpcs
docker compose exec phpfpm composer coding-standards-check/phpcs
```

```shell name=coding-standards-twig
docker compose exec phpfpm composer install
docker compose exec phpfpm composer coding-standards-apply/twig-cs-fixer
docker compose exec phpfpm composer coding-standards-check/twig-cs-fixer
```

```shell name=code-analysis
docker compose exec phpfpm composer install
docker compose exec phpfpm composer code-analysis
```

```shell name=coding-standards-markdown
docker run --platform linux/amd64 --rm --volume "$PWD:/md" peterdavehello/markdownlint markdownlint $(git ls-files *.md) --fix
docker run --platform linux/amd64 --rm --volume "$PWD:/md" peterdavehello/markdownlint markdownlint $(git ls-files *.md)
```

```shell name="coding-standards-assets"
docker compose run --rm node yarn install
docker compose run --rm node yarn coding-standards-apply
docker compose run --rm node yarn coding-standards-check
```
