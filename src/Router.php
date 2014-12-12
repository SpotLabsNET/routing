<?php

namespace Openclerk;

class Router {

  static $routes = array();

  static function addRoutes($routes) {
    self::$routes = array_merge(self::$routes, $routes);
  }

  static function urlFor($module, $arguments) {

  }

}
