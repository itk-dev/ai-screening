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
