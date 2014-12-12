openclerk/routing
=================

A library for simple routing in PHP.

## Using

This project [openclerk/config](https://github.com/openclerk/config) for config management.

First configure the component with site-specific values:

```php
Openclerk\Config::merge(array(
  "absolute_url" => "http://localhost/path/",
));
```

Add a simple router file:

```php
<?php
// router.php

require(__DIR__ . "/../inc/global.php");
$path = require_get("path", "security/login/password");
\Openclerk\Router::process($path);
?>
```

Add a `.htaccess` that translates paths to this router:

```
RewriteEngine on
RewriteRule ^([^\.]+)$        router.php?path=$1         [L,QSA]
```

Define site routes:

```php
// set up routes
\Openclerk\Router::addRoutes(array(
  "security/login/password" => "login.php",
  "security/login/:key" => "login-:key.php",
  // by default any unmatched routes will require <module>.php
));
```

Now you can use `url_for()` and `absolute_url_for()`:

```php
<a href="<?php echo htmlspecialchars(url_for('security/login/password')); ?>">Login with password</a>
```

## Tests

```php
composer update --dev
vendor/bin/phpunit
```

## TODO

1. Actual documentation
1. Tests
1. Publish on Packagist
