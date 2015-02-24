<?php

use \Openclerk\Router;

class EmptyObject {
  var $rendered = false;

  function __construct($test) {
    $this->test = $test;
  }

  function render($args) {
    $this->test->renderOutput = $args;
    $this->rendered = true;
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
      "api/v1/currenciesArray" => array($this->empty),
      "/api/v1/currenciesAbsolute" => $this->empty,
      "api/v1/currency/:code" => $this->empty,
      "/api/v2/currencies" => "currencies2.php",
      "api/v3/currencies" => "currencies3.php",
      "help/:key" => "../pages/kb.php?q=:key",
    ));
  }

  function testBasicTranslate() {
    $this->assertEquals("login.php", Router::translate("security/login/password"));
    $this->assertEquals("login-openid.php", Router::translate("security/login/openid"));
    $this->assertEquals("default.php", Router::translate("default"));
    $this->assertEquals("default/default.php", Router::translate("default/default"));
  }

  /**
   * Routes added with a leading / can still be translated as expected
   */
  function testAbsolutePathTranslate() {
    $this->assertEquals("currencies2.php", Router::translate("api/v2/currencies"));
    $this->assertEquals("currencies2.php", Router::translate("/api/v2/currencies"));
    $this->assertEquals("currencies3.php", Router::translate("api/v3/currencies"));
    $this->assertEquals("currencies3.php", Router::translate("/api/v3/currencies"));
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

  function testUrlFor() {
    $this->assertEquals("security/login/password", url_for("security/login/password"));
  }

  function testUrlForArguments() {
    $this->assertEquals("security/login/password?key=foo", url_for("security/login/password", array('key' => 'foo')));
    $this->assertEquals("security/login/password", url_for("security/login/password", array('key' => null)));
  }

  /**
   * Routes can be added as an array as well as an object
   */
  function testProcessObject() {
    $this->assertFalse($this->empty->rendered);
    Router::process("api/v1/currencies");
    $this->assertTrue($this->empty->rendered);
  }

  /**
   * Routes can be added as an array as well as an object
   */
  function testProcessArray() {
    $this->assertFalse($this->empty->rendered);
    Router::process("api/v1/currenciesArray");
    $this->assertTrue($this->empty->rendered);
  }

  function testProcessAbsolute() {
    $this->assertFalse($this->empty->rendered);
    Router::process("api/v1/currenciesAbsolute");
    $this->assertTrue($this->empty->rendered);
  }

}
