# Readme for AI Screening

## Site installation

Run the following commands to set up the site a new. This will start containers
and run composer install, add a settings.php file and run site-install.

```shell name="site-up"
task site-install
```

When the installation is completed, that admin user is created and the password for logging in the outputted. If you
forget the password, use `drush user:login` command to get a one-time-login URL (note: the URI here only works if
you are using Traefik and [ITK-dev docker setup](https://github.com/itk-dev/devops_itkdev-docker)).

```shell name="site-login"
task drush -- user:login
```

See [ai_screening/README.md](web/modules/custom/ai_screening/README.md) for more settings.

## Updating the site

Run

``` shell name="site-update"
task site-update
```

to update the site.

### Access the site

If you are using out `itkdev-docker-compose` simple use the command below to åbne the site in you default browser.

```shell name="site-open"
open $(task site-url)
```

### Acces the admin

```shell name="site-open-admin"
task drush -- user:login
```

### Drupal config

Export config created from drupal:

```shell
task drush -- config:export
```

Import config from config files:

```shell
task drush -- config:import
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

## Site theme

There is a custom frontend theme installed with common components based on tailwind. See
[web/themes/custom/itkdev/itkdev_base_theme/README.md](web/themes/custom/itkdev/itkdev_base_theme/README.md) for details
on how to build and do development on the theme. TL;DR:

``` shell name="theme-build"
task theme-build
```

Build and watch for changes:

``` shell name="theme-watch"
task theme-watch
```

## Development

See [Development](docs/Development.md) for details on development.

## Production deployment

See [Production](docs/Production.md) for details on production deployment.
