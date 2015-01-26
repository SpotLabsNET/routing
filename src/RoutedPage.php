<?php

namespace Openclerk;

/**
 * Allows pages and user interface components to be defined at runtime.
 */
abstract class RoutedPage {

  abstract function render($arguments);

  /**
   * Get the path for this routed page along with arguments
   * @return e.g. "/admin/exceptions" or "/currencies/:currency"
   */
  abstract function getPath();

}
