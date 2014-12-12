<?php

namespace Openclerk;

class Router {

  static $routes = array();
  static $compiled_routes = null;

  static function addRoutes($routes) {
    self::$routes = array_merge(self::$routes, $routes);
  }

  static function urlFor($module, $arguments) {
    if (self::$compiled_routes === null) {
      self::compileRoutes();
    }

    foreach (self::$compiled_routes as $key => $value) {

    }
  }

  static function compileRoutes() {
    // TODO
  }

}
