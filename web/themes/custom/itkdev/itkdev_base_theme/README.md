# ITK Dev Frontend Theme for Drupal

## Build assets

Run the command below to install assest and tools from package.json

```shell name"assets-install"
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme install
```

Run the command below to continuesly build assets uppon file changes.

```shell name"assets-watch"
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme watch
```

Run the command below to continuesly build assets once.

```shell name"assets-build"
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme build
```

## Coding standards

Run the commands below to check and apply coding standards

```shell name="coding-standards-assets"
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme install
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme coding-standards-apply
docker compose run --rm node yarn --cwd /app/web/themes/custom/itkdev/itkdev_base_theme coding-standards-check
```


## Common visuals

Images you should consider adding in the theme

- screenshot.png
  Screenshot of the page for presentation in the admin
- favicon.ico
  Classic favicon for the browser
- logo.svg
  Default system branding for the theme
