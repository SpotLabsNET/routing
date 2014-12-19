<?php

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

    \Openclerk\Router::resetRoutes();
    \Openclerk\Router::addRoutes(array(
      "security/login/password" => "login.php",
      "security/login/:key" => "login-:key.php",
      "security/register/:key" => "register-:key.php?argument=:key",
      "api/v1/currencies" => $this->empty,
      "api/v1/currency/:code" => $this->empty,
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

  function testGetPHPInclude() {
    $this->assertEquals("login.php", \Openclerk\Router::getPHPInclude("login.php"));
    $this->assertEquals("login-openid.php", \Openclerk\Router::getPHPInclude("login-openid.php?a=b&c=d"));
  }

  function testGetAdditionalParameters() {
    $this->assertEquals(array(), \Openclerk\Router::getAdditionalParameters("login.php"));
    $this->assertEquals(array("a" => "b", "c" => "d"), \Openclerk\Router::getAdditionalParameters("login-openid.php?a=b&c=d"));
  }

  function testObjectTranslate() {
    $this->assertEquals(array('callback' => $this->empty, 'arguments' => array()), \Openclerk\Router::translate("api/v1/currencies"));
  }

  function testObjectTranslateWithParameters() {
    $this->assertEquals(array('callback' => $this->empty, 'arguments' => array('code' => 'btc')), \Openclerk\Router::translate("api/v1/currency/btc"));
  }

  function testObjectRender() {
    \Openclerk\Router::process("/api/v1/currency/btc");
    $this->assertEquals(array("code" => "btc"), $this->renderOutput);

    \Openclerk\Router::process("/api/v1/currency/invalid");
    $this->assertEquals(array("code" => "invalid"), $this->renderOutput);
  }

}
