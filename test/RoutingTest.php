<?php

require(__DIR__ . "/../vendor/autoload.php");

class RoutingTest extends PHPUnit_Framework_TestCase {

  function setUp() {
    \Openclerk\Router::resetRoutes();
    \Openclerk\Router::addRoutes(array(
      "security/login/password" => "login.php",
      "security/login/:key" => "login-:key.php",
      "security/register/:key" => "register-:key.php?argument=:key",
    ));
  }

  function testBasicTranslate() {
    $this->assertEquals("login.php", \Openclerk\Router::translate("security/login/password"));
    $this->assertEquals("login-openid.php", \Openclerk\Router::translate("security/login/openid"));
    $this->assertEquals("default.php", \Openclerk\Router::translate("default"));
    $this->assertEquals("default/default.php", \Openclerk\Router::translate("default/default"));
  }

  function testMultipleTranslate() {
    $this->assertEquals("register-openid.php?argument=openid", \Openclerk\Router::translate("security/register/openid"));
  }

}
