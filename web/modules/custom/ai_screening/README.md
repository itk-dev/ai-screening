# AI Screening

## Settings

Default user language (set when user is created):

``` php
# settings.local.php
// Default user language code (default: 'da')
$settings['ai_screening']['user']['default_langcode'] = 'da';
```

OpenID Connect groups claim:

``` php
# settings.local.php
// Default OpenID Connect groups claim (default: 'role')
$settings['ai_screening']['openid_connect']['groups_claim'] = 'groups';
```
