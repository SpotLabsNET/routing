<?php

/**
 * Convenience function for {@link Openclerk\Router#urlFor()}
 */
function url_for($module, $arguments = array()) {
  return \Openclerk\Router::urlFor($module, $arguments);
}

/**
 * Convenience function for {@link Openclerk\Router#urlAdd()}
 */
function url_add($url, $arguments = array()) {
  return \Openclerk\Router::urlAdd($url, $arguments);
}

function absolute_url_for($module, $arguments = array()) {
  return \Openclerk\Config::get('absolute_url') . \Openclerk\Router::absoluteUrlFor($module, $arguments);
}

/**
 * Return an absolute URL for a page on the current site.
 */
function absolute_url($url) {
  return \Openclerk\Config::get('absolute_url') . $url;
}
