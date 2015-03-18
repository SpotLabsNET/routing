openclerk/routing [![Build Status](https://travis-ci.org/openclerk/routing.svg)](https://travis-ci.org/openclerk/routing)
=================

A library for simple routing in PHP.

## Using

This project uses [openclerk/config](https://github.com/openclerk/config) for config management.

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

try {
  \Openclerk\Router::process($path);
} catch (\Openclerk\RouterException $e) {
  header("HTTP/1.0 404 Not Found");
  echo htmlspecialchars($e->getMessage());
}
?>
```

Add a `.htaccess` that translates paths to this router:

```
RewriteEngine on

# Forbid access to any child PHP scripts
RewriteRule ^([^\.]+)/([^\.]+).php$   -                   [F]

RewriteRule ^([^\.]+)$                router.php?path=$1  [L,QSA]
```

Define site routes:

```php
// set up routes
\Openclerk\Router::addRoutes(array(
  "security/login/password" => "security/login.php?type=password",
  "security/login/:key" => "security/login-:key.php?type=:key",
  // by default any unmatched routes will require <module>.php
));
```

Now you can use `url_for()` and `absolute_url_for()`:

```php
<a href="<?php echo htmlspecialchars(url_for('security/login/password')); ?>">Login with password</a>
```

## Renderable objects

You can also pass along an object with a `render($args)` method, which will be called instead:

```php
class MyApi {
  function render($args) {
    // $args = array('code' => ...)
  }
}

\Openclerk\Router::addRoutes(array(
  "api/currency/:code" => new MyApi(),
));
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
