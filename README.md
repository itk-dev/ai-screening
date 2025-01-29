# Readme for AI Screening

A _Project_ has one or more _Tracks_ each of which in turn has one or more _Tools_:

``` mermaid
---
title: AI Screening classes
---
classDiagram
    Tool --> Track
    Track --> Project
    class Project {
        %% string title
    }
    Tool --> WebformSubmission
```

## Site installation

Run the following commands to set up the site a new. This will start containers
and run composer install, add a settings.php file and run site-install.

```shell name="site-up"
task site-install
```

When the installation is completed, that admin user is created and the password for logging in the outputted. If you
forget the password, use `drush user:login` command to get a one-time-login URL (note: the URI here only works if
you are using Traefik and [ITK-dev docker setup](https://github.com/itk-dev/devops_itkdev-docker)).

### Fixtures

To add fixtures to the site two tasks are provided.

> ### ! IMPORTANT
>
> Applying fixtures will delete all existing content on the site

```shell name="fixtures"
task apply-fixtures
```

Fixtures are grouped to allow only for certain fixtures to be loaded. The "base"
group holds only a minimum of content fixtures for the base functionality of the
site to work out of the box. Other fixtures add example content.

```shell name="fixtures-groups"
task apply-fixtures -- --groups=base,user
```

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
docker run --rm --volume "$PWD:/md" itkdev/markdownlint $(git ls-files *.md) --fix
docker run --rm --volume "$PWD:/md" itkdev/markdownlint $(git ls-files *.md)
```

```shell name=coding-standards-markdown
docker run --rm --volume "$PWD:/md" itkdev/markdownlint $(git ls-files *.md) --fix
docker run --rm --volume "$PWD:/md" itkdev/markdownlint $(git ls-files *.md)
```

``` shell name=coding-standards-js
task prettier
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
