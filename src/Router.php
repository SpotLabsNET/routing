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

  static function urlFor($module, $arguments = array()) {
    $is_absolute = (strpos($module, "://") !== false);

    return ($is_absolute ? "" : self::calculateRelativePath()) . self::absoluteUrlFor($module, $arguments);
  }

  static function absoluteUrlFor($module, $arguments = array()) {
    $hash = false;
    if (strpos($module, "#") !== false) {
      $hash = substr($module, strpos($module, "#") + 1);
      $module = substr($module, 0, strpos($module, "#"));
    }

    if ($module == "index") {
      $module = ".";
    }

    $query = array();
    if (count($arguments) > 0) {
      foreach ($arguments as $key => $value) {
        $query[] = urlencode($key) . "=" . urlencode($value);
      }
    }

    return $module . (count($query) ? "?" . implode("&", $query) : "") . ($hash ? "#" . $hash : "");
  }

  static $cached_relativePath = null;

  /**
   * Can be cached.
   */
  static function calculateRelativePath() {
    if (self::$cached_relativePath === null) {
      // construct a relative path for this request based on the request URI, but only if it is set
      if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && !defined('FORCE_NO_RELATIVE')) {
        $uri = $_SERVER['REQUEST_URI'];
        // strip out the hostname from the absolute_url
        $intended = substr(\Openclerk\Config::get('absolute_url'), strpos(\Openclerk\Config::get('absolute_url'), '://') + 4);
        $intended = substr($intended, strpos($intended, '/'));
        // if we're in this path, remove it
        // now generate ../s as necessary
        if (strtolower(substr($uri, 0, strlen($intended))) == strtolower($intended)) {
          $uri = substr($uri, strlen($intended));
        }
        // but strip out any parameters, which might have /s in them, which will completely mess this up
        // (see issue #13)
        if (strpos($uri, "?") !== false) {
          $uri = substr($uri, 0, strpos($uri, "?"));
        }
        self::$cached_relativePath = str_repeat('../', substr_count($uri, '/'));
      } else {
        self::$cached_relativePath = "";
      }
    }
    return self::$cached_relativePath;
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

  /**
   * Return just the PHP include that should be included for the given
   * translated URI.
   */
  static function getPHPInclude($translated) {
    if (strpos($translated, "?") !== false) {
      return substr($translated, 0, strpos($translated, "?"));
    }
    return $translated;
  }

  /**
   * Return just additional query parameters that should be included
   * for the given translated URI.
   */
  static function getAdditionalParameters($translated) {
    $result = array();
    if (strpos($translated, "?") !== false) {
      parse_str(substr($translated, strpos($translated, "?") + 1), $result);
    }
    return $result;
  }

  /**
   * Given the current path, find the correct PHP template,
   * set the appropriate GET variables and {@link require()} the PHP template.
   */
  static function process($path) {
    $translated = self::translate($path);
    $include = self::getPHPInclude($translated);
    $args = self::getAdditionalParameters($include);

    if (!file_exists($include)) {
      throw new RouterException("Could not find translated module for '$path'", new RouterException("Could not find include '$include'"));
    }

    foreach ($args as $key => $value) {
      $_GET[$key] = $value;
    }
    require($include);
  }

}
