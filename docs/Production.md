# Production

## OpenID Connect

``` php
# settings.local.php

$config['openid_connect.client.generic']['settings']['client_id'] = '…'; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['client_secret'] = '…'; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = '…'; // Get this from your OpenID Connect Discovery endpoint
$config['openid_connect.client.generic']['settings']['token_endpoint'] = '…'; // Get this from your OpenID Connect Discovery endpoint

$config['openid_connect.settings']['role_mappings']['editor'] = ['GG-Rolle-B2C-AIScreening-editor];

// Custom label on log in button.
$settings['locale_custom_strings_en'][''] = [
  'Log in with @client_title' => 'Employee sign-in',
];

$settings['locale_custom_strings_da'][''] = [
  'Log in with @client_title' => 'Medarbejderlogin',
];
```
