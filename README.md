# Drupal 11 TEMPLATE

This is a Drupal project template for starting new projects. The template
includes default setup and a starting point for:

- PHP Code sniffer
- PHP Code styles
- Github actions
- Tasks
- Local settings
- Docker and overrides
- Readme
- Changelog

## Usage

### Build new project from template

Build a new project using this template

```shell
gh repo create itk-dev/{PROJECTNAME} --template itk-dev/drupal-11-template --public --clone
```

### Prune the project

Prune the project to remove/override template specific files and set up the
project with docker config and settings:

```shell
task template-prune PROJECTNAME={PROJECTNAME}
```

> [!IMPORTANT]  
> Replace `{PROJECTNAME}` with your actual project name.
This will:

- Remove TEMPLATE_THEME and cause coding-standards-check to fail until a new
theme is added.
- Add/override .env and .task.env files with files that uses {PROJECTNAME}
- Add/override a settings.local.php to web/sites/default folder
- Add/override a services.local.yml to web/sites/default folder
- Override CHANGELOG.md
- Replace {PROJECTNAME} placeholder in README.md and CHANGELOG.md
- Override Taskfile.yml

<!-- markdownlint-disable MD025 -->

### ---- After building a project delete this line and all above ----

# Readme for {PROJECTNAME}

## Build assets

## Site installation

Run the following commands to set up the site a new. This will start containers
and run composer install, add a settings.php file and run site-install.

```shell name="site-up"
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
