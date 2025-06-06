# Production

For production some settings must be added to `web/sites/default/settings.local.php`:

``` php
// settings.local.php
$databases['default']['default'] = [
  'database' => 'ai-screening',
  'username' => 'ai-screening',
  'password' => '',
  'prefix' => '',
  'host' => 'host.docker.internal',
  'port' => '',
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
];

/**
 * * Set trusted host pattern.
 */
$settings['trusted_host_patterns'][] = '^ai-screening\.example\.com$';
```

## OpenID Connect

``` php
// settings.local.php
$config['openid_connect.client.generic']['settings']['client_id'] = ''; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['client_secret'] = ''; // Get this from your IdP provider
$config['openid_connect.client.generic']['settings']['authorization_endpoint'] = ''; // Get this from your OpenID Connect Discovery endpoint
$config['openid_connect.client.generic']['settings']['token_endpoint'] = ''; // Get this from your OpenID Connect Discovery endpoint
$config['openid_connect.client.generic']['settings']['end_session_endpoint'] = ''; // Get this from your OpenID Connect Discovery endpoint

$config['openid_connect.settings']['role_mappings']['administrator'] = ['Administrator']; // Check these with your IdP provider
$config['openid_connect.settings']['role_mappings']['editor'] = ['Redaktoer']; // Check these with your IdP provider

// Custom label on log in button.
$settings['locale_custom_strings_en'][''] = [
  'Log in with @client_title' => 'Employee sign-in',
];

$settings['locale_custom_strings_da'][''] = [
  'Log in with @client_title' => 'Medarbejderlogin',
];
```
