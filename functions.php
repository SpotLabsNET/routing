<?php

use \Openclerk\Config;
use \Openclerk\Router;

/**
 * Convenience function for {@link Router#urlFor()}
 */
function url_for($module, $arguments = array()) {
  return Router::urlFor($module, $arguments);
}

/**
 * Convenience function for {@link Router#urlAdd()}
 */
function url_add($url, $arguments = array()) {
  return Router::urlAdd($url, $arguments);
}

function absolute_url_for($module, $arguments = array()) {
  return Config::get('absolute_url') . Router::absoluteUrlFor($module, $arguments);
}

/**
 * Return an absolute URL for a page on the current site.
 *
 * Uses the value of {@code absolute_url} provided by {@link Config}.
 * If {@code absolute_url} ends in {@code /} and {@code $url} starts with {@code /},
 * removes one of the extra slashes.
 *
 * @param $url the relative URL to link to
 */
function absolute_url($url) {
  $root = Config::get('absolute_url');

  // trim any extra slash
  if (substr($root, -1) == "/" && substr($url, 0, 1) == "/") {
    $url = substr($url, 1);
  }

  return $root . $url;
}
