<?php

function url_for($module, $arguments = array()) {
  return \Openclerk\Router::urlFor($module, $arguments);
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
