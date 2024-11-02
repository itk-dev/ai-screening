# Development

## OpenID Connect

The [OpenID Connect module](https://www.drupal.org/project/openid_connect) is yet officially compatible with [Drupal
11](https://www.drupal.org/about/11), but
[chx/drupal-issue-fork](https://www.drupal.org/docs/develop/git/using-gitlab-to-contribute-to-drupal/core-version-compatibility-fixes-for-modules-with-unmerged-changes)
comes to the rescue:

``` shell
docker compose exec phpfpm require chx/drupal-issue-fork
docker compose exec phpfpm require composer drupal-issue-fork https://git.drupalcode.org/issue/openid_connect-3452009/-/tree/3452009-drupal-11-compatibility
docker compose exec phpfpm require composer require drupal/openid_connect:dev-3452009-drupal-11-compatibility
```

### Local OIDC test

During (local) development we use [OpenId Connect Server Mock](https://github.com/Soluto/oidc-server-mock) (cf.
[`docker-compose.oidc.yml`](docker-compose.oidc.yml) which is
[included](https://docs.docker.com/compose/how-tos/multiple-compose-files/include/) in
[`docker-compose.override.yml`](docker-compose.override.yml)).

The following users are available for local testing:

| Username      | Password      | Groups           |
|---------------|---------------|------------------|
| administrator | administrator | AD-administrator |
| editor        | editor        | AD-editor        |
| user          | user          | AD-user[^1]      |

[^1]: The "AD-user" group is not actually used in Drupal (but the AD requires a group on a user). A user that is neither
    an "administrator" or "editor" will be just an "authenticated" user.

## WebProfiler

The [WebProfiler module](https://www.drupal.org/project/webprofiler) can be installed to help during development:

``` shell
task drush -- pm:install webprofiler
```

The module and other development modules are excluded from configuration syncronization (cf.
[`settings.php`](../web/sites/default/settings.php) and <https://www.drupal.org/node/3079028>).

## Webforms

Webforms: Forms configuration (`/admin/structure/webform/config`)

### Custom elements

* `ai_screening_weighted_radios`
* `ai_screening_weighted_textarea`
* `ai_screening_weighted_textfield`

## Drush commands

Some helper Drush commands are added – currently most for debugging purposes:

* `task drush -- ai-screening:project-track:list`: List project tracks
* `task drush -- ai-screening:project-track:show`: Show details for a project track

## Tests

We have a modules test. Run it with

``` shell name="test-module-test"
task test-module-test
```

## Development settings

The Development settings on `/admin/config/development/settings` (cf.
<https://www.drupal.org/docs/develop/development-tools/disabling-and-debugging-caching>) can be set using some command
line incantations:

``` shell name=development-settings-cache-disable
# Disable cache
task drush -- php:eval "Drupal::keyValue('development_settings')->setMultiple(['disable_rendered_output_cache_bins' => TRUE]);"
task drush -- cache:rebuild
```

``` shell name=development-settings-cache-enable
# Enable cache (for production)
task drush -- php:eval "Drupal::keyValue('development_settings')->setMultiple(['disable_rendered_output_cache_bins' => FALSE]);"
task drush -- cache:rebuild
```

``` shell name=development-settings-twig-enable
# Enable Twig development mode
task drush -- php:eval "Drupal::keyValue('development_settings')->setMultiple(['twig_debug' => TRUE, 'twig_cache_disable' => TRUE]);"
task drush -- cache:rebuild
```

``` shell name=development-settings-twig-disable
# Disable Twig development mode (for production)
task drush -- php:eval "Drupal::keyValue('development_settings')->setMultiple(['twig_debug' => FALSE, 'twig_cache_disable' => FALSE]);"
task drush -- cache:rebuild
```

## Delvelopment command line incantations

This section contains some useful command line incantations that can come in handy during development and manual
testing.

``` shell name=test-cron-cleanup
task apply-fixtures -- --yes
task drush -- sql:cli --extra=--table <<'EOF'
SELECT
  (SELECT COUNT(*) FROM project_track) AS '#project_track',
  (SELECT COUNT(*) FROM project_track_tool) AS '#project_track_tool',
  (SELECT COUNT(*) FROM webform_submission) AS '#webform_submission'
EOF

task drush -- --yes cron
task drush -- sql:cli --extra=--table <<'EOF'
SELECT
  (SELECT COUNT(*) FROM project_track) AS '#project_track',
  (SELECT COUNT(*) FROM project_track_tool) AS '#project_track_tool',
  (SELECT COUNT(*) FROM webform_submission) AS '#webform_submission'
EOF
```
