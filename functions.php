<?php

/**
 * Generate the url for a particular module (i.e. script) and particular arguments (i.e. query string elements).
 * Handles relative paths back to the root, but /clerk/foo/bar to /clerk/bar/foo is untested.
 * Also handles #hash arguments.
 * Should handle absolute arguments OK.
 */
function url_for($module, $arguments = array()) {
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
