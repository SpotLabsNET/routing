<?php

/**
 * Generate the url for a particular module (i.e. script) and particular arguments (i.e. query string elements).
 * Handles relative paths back to the root, but /clerk/foo/bar to /clerk/bar/foo is untested.
 * Also handles #hash arguments.
 * Should handle absolute arguments OK.
 */
function url_for_old($module, $arguments = array()) {
  if (!is_array($arguments)) {
    throw new \InvalidArgumentException("Expected arguments to be an array, was " . gettype($arguments));
  }

  $is_absolute = (strpos($module, "://") !== false);
  $hash = false;
  if (strpos($module, "#") !== false) {
    $hash = substr($module, strpos($module, "#") + 1);
    $module = substr($module, 0, strpos($module, "#"));
  }
  if ($module === "index") {
    $module = ".";
  }
  $query = array();
  if (count($arguments) > 0) {
    foreach ($arguments as $key => $value) {
      $query[] = urlencode($key) . "=" . urlencode($value);
    }
  }
  return ($is_absolute ? "" : calculate_relative_path()) . $module . /* ".php" . */ (count($query) ? "?" . implode("&", $query) : "") . ($hash ? "#" . $hash : "");
}

function url_for($module, $arguments = array()) {
  return \Openclerk\Router::urlFor($module, $arguments);
}

/**
 * Can be cached.
 */
$global_calculate_relative_path = null;
function calculate_relative_path() {
  global $global_calculate_relative_path;
  if ($global_calculate_relative_path === null) {
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
      $global_calculate_relative_path = str_repeat('../', substr_count($uri, '/'));
    } else {
      $global_calculate_relative_path = "";
    }
  }
  return $global_calculate_relative_path;
}

/**
 * Return an absolute URL for a page on the current site.
 */
function absolute_url($url) {
  return \Openclerk\Config::get('absolute_url') . $url;
}
