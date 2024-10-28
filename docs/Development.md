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
