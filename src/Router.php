<?php

namespace Openclerk;

class Router {

  static $routes = array();
  static $compiled_routes = null;
  static $compiled_keys = null;

  static function addRoutes($routes) {
    self::$compiled_routes = null;
    if ($routes) {
      self::checkForWildcardRoute();
      self::$routes = array_merge(self::$routes, $routes);
    }
  }

  /**
   * Check that we don't have a wildcard route that would cause any
   * additional added routes to be ignored.
   * @throws RouterException if there is a wildcard route that will cause
   *      additional routes to be ignored
   */
  static function checkForWildcardRoute() {
    foreach (self::$routes as $key => $ignored) {
      if (preg_match("/^:[a-z0-9]+$/i", $key)) {
        throw new RouterException("Wildcard route '$key' will prevent any additional routes from ever being reached");
      }
    }
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
        if ($value !== null) {
          $query[] = urlencode($key) . "=" . urlencode($value);
        }
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
  static $_current_compiled_keys = null;

  static function compileRoutes() {
    $result = array();
    $resultKeys = array();

    foreach (self::$routes as $key => $value) {
      // strip off any leading /s
      $key = preg_replace("/^\\/+/i", "", $key);

      $key = "#^" . preg_quote($key, "#") . "$#i";
      self::$_current_compiled_value = $value;
      self::$_current_compiled_count = 0;
      self::$_current_compiled_keys = array();

      // lots of escaping since we've already quoted them above
      $key = preg_replace("#\\\\\\[([^\]]+)\\\\]#i", "(|\\1)", $key);

      $key = preg_replace_callback("#\\\\:([a-z0-9]+)#i", function ($matches) {
        Router::$_current_compiled_count++;
        // don't try to preg_replace Renderable objects
        if (!is_object(Router::$_current_compiled_value)) {
          Router::$_current_compiled_value = str_replace(":" . $matches[1], "\\" . Router::$_current_compiled_count, Router::$_current_compiled_value);
        }
        Router::$_current_compiled_keys[$matches[1]] = Router::$_current_compiled_count;
        return "([^/]+?)";
      }, $key);

      $result[$key] = Router::$_current_compiled_value;
      $resultKeys[$key] = Router::$_current_compiled_keys;
    }
    self::$compiled_routes = $result;
    self::$compiled_keys = $resultKeys;
  }

  /**
   * Get a list of the compiled route keys and resulting matches,
   * only really for debugging.
   */
  static function getCompiledRoutes() {
    if (self::$compiled_routes === null) {
      self::compileRoutes();
    }

    $result = array();
    foreach (self::$compiled_routes as $key => $value) {
      if (is_string($value)) {
        $result[$key] = $value;
      } else if (is_object($value)) {
        $result[$key] = get_class($value);
      } else if (is_array($value)) {
        $result[$key] = "Array";
      } else {
        $result[$key] = (string) $value;
      }
    }

    return $result;
  }

  /**
   * Get the PHP file that should be included for the given route.
   */
  static function translate($route) {
    if (self::$compiled_routes === null) {
      self::compileRoutes();
    }

    // strip off any leading /s
    $route = preg_replace("/^\\/+/i", "", $route);

    foreach (self::$compiled_routes as $key => $value) {
      if (preg_match($key, $route, $matches)) {
        if (is_object($value)) {
          $arguments = array();
          // get parameterised arguments

          $keys = self::$compiled_keys[$key];
          for ($i = 1; $i < count($matches); $i++) {
            foreach ($keys as $code => $index) {
              if ($index === $i) {
                $arguments[$code] = $matches[$i];
              }
            }
          }

          return array(
            'callback' => $value,
            'arguments' => $arguments,
          );

        } else {
          // don't try to parameterise Renderable objects
          for ($i = 1; $i < count($matches); $i++) {
            $value = str_replace("\\" . $i, $matches[$i], $value);
          }
          return $value;
        }
      }
    }

    // default
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
    if (is_array($translated)) {
      $args = array_merge($translated['arguments'], $_GET);
      $callback = $translated['callback'];
      $callback->render($args);

    } else {
      // otherwise it's a PHP include
      $include = self::getPHPInclude($translated);
      $args_include = self::getAdditionalParameters($include);
      $args_translated = self::getAdditionalParameters($translated);

      if (!file_exists($include)) {
        throw new RouterException("Could not find translated module for '$path'",
          new RouterException("Could not find include '$include' for translated path '$translated'"));
      }

      foreach ($args_include as $key => $value) {
        $_GET[$key] = $value;
      }
      foreach ($args_translated as $key => $value) {
        $_GET[$key] = $value;
      }

      // TODO it would be good if we can remove this smell eventually
      // but openclerk depends on it until we have a flash framework in place
      global $messages;
      global $errors;

      require($include);
    }
  }

  /**
   * Add GET arguments onto a particular URL. Replaces any existing arguments.
   * Also handles #hash arguments.
   */
  static function urlAdd($url, $arguments = array()) {
    $hash = false;
    if (strpos($url, "#") !== false) {
      $hash = substr($url, strpos($url, "#") + 1);
      $url = substr($url, 0, strpos($url, "#"));
    }

    // strip out query
    $previous = array();
    if (strpos($url, "?") !== false) {
      $query = substr($url, strpos($url, "?") + 1);
      $url = substr($url, 0, strpos($url, "?"));
      $bits = explode("&", $query);
      foreach ($bits as $bit) {
        $bit_bits = explode("=", $bit, 2);
        // does not handle e.g. "url?foo"
        if (count($bit_bits) == 2) {
          $previous[urldecode($bit_bits[0])] = urldecode($bit_bits[1]);
        }
      }
    }

    // go through all previous arguments and new arguments, and let
    // them override each other
    $new_arguments = array();
    foreach ($arguments + $previous as $key => $value) {
      if ($value === null) {
        unset($new_arguments[$key]);
      } else {
        $new_arguments[$key] = $value;
      }
    }

    foreach ($new_arguments as $key => $value) {
      if (strpos($url, "?") !== false) {
        $url .= "&" . urlencode($key) . "=" . urlencode($value);
      } else {
        $url .= "?" . urlencode($key) . "=" . urlencode($value);
      }
    }
    if ($hash) {
      $url .= "#" . $hash;
    }
    return $url;
  }

}
