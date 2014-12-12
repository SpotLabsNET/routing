openclerk/users
===============

A library for simple routing in PHP.

## Using

This project [openclerk/config](https://github.com/openclerk/config) for config management.

First configure the component with site-specific values:

```php
Openclerk\Config::merge(array(
  "absolute_url" => "http://localhost/",
));
```

## Examples

```php
echo url_for('module/file', array('a' => 1, 'b' => 2));
```

## TODO

1. Actual documentation
1. Tests
1. Publish on Packagist
