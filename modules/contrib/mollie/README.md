# Mollie for Drupal
Enables online payments in Drupal through Mollie.

## Installation
The recommended way to install this module is
[using Composer](https://www.drupal.org/docs/8/extending-drupal-8/installing-modules-composer-dependencies).

`composer require drupal/mollie`

Alternatively you can install the module from
[the command line using Drush or Drupal Console](https://www.drupal.org/docs/8/extending-drupal-8/installing-modules-from-the-command-line)
or [manually](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

### Dependencies
This module depends on [Mollie API client for PHP](https://github.com/mollie/mollie-api-php).
Although older 2.x versions of the client might work only version 2.6 and newer are supported.
How the client should be installed depends on how this module was installed.

#### Using Composer
If you have installed this module using Composer the Mollie API client for PHP is
installed automatically.

#### Using Drush or Drupal Console or manually
Installing Drupal and Drupal extensions (modules, themes) using an other way
than Composer is deprecated. At this moment Mollie for Drupal only supports
installing the Mollie API client for PHP using Composer. Learn how to
[manage existing Drupal sites using Composer](https://www.drupal.org/node/2718229#managing-existing-site).

## Configuration

### Mollie account
For security reasons your Mollie credentials cannot be managed through the Drupal UI.
Add your Mollie credentials to the settings.php file:
```php
$settings['mollie.settings'] = [
  'live_key' => 'live_YouRMollIeLIVeAPIkeY',
  'test_key' => 'test_YouRMollIetEStAPIkeY',
  'access_token' => 'youROrgaNIsatIOnacCEsstOkeN',
];
```

When you have a test key configured you can toggle between test and live mode on the settings page: _/admin/config/services/mollie_.
