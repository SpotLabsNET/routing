<?php

use \Openclerk\Router;

class RouteAnythingCheckTest extends PHPUnit_Framework_TestCase {

  function testWildcardCheck() {
    Router::resetRoutes();
    Router::addRoutes(array(
      "foo" => "foo.php",
    ));

    Router::addRoutes(array(
      ":anything" => "bar.php?key=:anything",
    ));

    try {
      // because things are added in reverse order, this rule can never fire due to
      // the wildcard above
      Router::addRoutes(array(
        "ignored" => "ignored.php",
      ));

      $this->fail("Should have thrown an exception");
    } catch (\Openclerk\RouterException $e) {
      // expected
    }
  }

}
