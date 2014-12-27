<?php

use \Openclerk\Router;

class EmptyObject {
  function __construct($test) {
    $this->test = $test;
  }

  function render($args) {
    $this->test->renderOutput = $args;
  }
}

class RoutingTest extends PHPUnit_Framework_TestCase {

  var $empty = null;
  var $renderOutput = null;

  function setUp() {
    $this->empty = new EmptyObject($this);
    $this->renderOutput = null;

    Router::resetRoutes();
    Router::addRoutes(array(
      "security/login/password" => "login.php",
      "security/login/:key" => "login-:key.php",
      "security/register/:key" => "register-:key.php?argument=:key",
      "api/v1/currencies" => $this->empty,
      "api/v1/currency/:code" => $this->empty,
      "help/:key" => "../pages/kb.php?q=:key",
    ));
  }

  function testBasicTranslate() {
    $this->assertEquals("login.php", Router::translate("security/login/password"));
    $this->assertEquals("login-openid.php", Router::translate("security/login/openid"));
    $this->assertEquals("default.php", Router::translate("default"));
    $this->assertEquals("default/default.php", Router::translate("default/default"));
  }

  function testMultipleTranslate() {
    $this->assertEquals("register-openid.php?argument=openid", Router::translate("security/register/openid"));
  }

  function testGetPHPInclude() {
    $this->assertEquals("login.php", Router::getPHPInclude("login.php"));
    $this->assertEquals("login-openid.php", Router::getPHPInclude("login-openid.php?a=b&c=d"));
  }

  function testGetAdditionalParameters() {
    $this->assertEquals(array(), Router::getAdditionalParameters("login.php"));
    $this->assertEquals(array("a" => "b", "c" => "d"), Router::getAdditionalParameters("login-openid.php?a=b&c=d"));
  }

  function testObjectTranslate() {
    $this->assertEquals(array('callback' => $this->empty, 'arguments' => array()), Router::translate("api/v1/currencies"));
  }

  function testObjectTranslateWithParameters() {
    $this->assertEquals(array('callback' => $this->empty, 'arguments' => array('code' => 'btc')), Router::translate("api/v1/currency/btc"));
  }

  function testObjectRender() {
    Router::process("/api/v1/currency/btc");
    $this->assertEquals(array("code" => "btc"), $this->renderOutput);

    Router::process("/api/v1/currency/invalid");
    $this->assertEquals(array("code" => "invalid"), $this->renderOutput);
  }

  function testHelpTranslate() {
    $path = "help/versions";
    $translated = Router::translate($path);
    $include = Router::getPHPInclude($translated);
    $args = Router::getAdditionalParameters($translated);

    $this->assertEquals("../pages/kb.php?q=versions", $translated);
    $this->assertEquals("../pages/kb.php", $include);
    $this->assertEquals(array("q" => "versions"), $args);
  }

}
