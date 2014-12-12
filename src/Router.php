<?php

namespace Openclerk;

class Router {

  static $routes = array();
  static $compiled_routes = null;

  static function addRoutes($routes) {
    self::$compiled_routes = null;
    self::$routes = array_merge(self::$routes, $routes);
  }

  static function resetRoutes() {
    self::$compiled_routes = null;
    self::$routes = array();
  }

  static function urlFor($module, $arguments) {
    if (self::$compiled_routes === null) {
      self::compileRoutes();
    }

    foreach (self::$compiled_routes as $key => $value) {

    }
  }

  static $_current_compiled_value = null;
  static $_current_compiled_count = null;

  static function compileRoutes() {
    $result = array();
    foreach (self::$routes as $key => $value) {
      $key = "#" . preg_quote($key, "#") . "#i";
      self::$_current_compiled_value = $value;
      self::$_current_compiled_count = 0;

      $key = preg_replace_callback("#\\\\:([a-z]+)#i", function ($matches) {
        Router::$_current_compiled_count++;
        Router::$_current_compiled_value = str_replace(":" . $matches[1], "\\" . Router::$_current_compiled_count, Router::$_current_compiled_value);
        return "([^/]+)";
      }, $key);

      $result[$key] = Router::$_current_compiled_value;
    }
    self::$compiled_routes = $result;
  }

  /**
   * Get the PHP file that should be included for the given route.
   */
  static function translate($route) {
    if (self::$compiled_routes === null) {
      self::compileRoutes();
    }

    foreach (self::$compiled_routes as $key => $value) {
      if (preg_match($key, $route, $matches)) {
        for ($i = 1; $i < count($matches); $i++) {
          $value = str_replace("\\" . $i, $matches[$i], $value);
        }
        return $value;
      }
    }

    return $route . ".php";
  }

}
