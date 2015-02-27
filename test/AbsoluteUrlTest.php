<?php

use \Openclerk\Config;

/**
 * Tests the functionality of {@link absolute_url()}.
 */
class AbsoluteUrlTest extends \PHPUnit_Framework_TestCase {

  function testBasic() {
    Config::overwrite(array(
      "absolute_url" => "http://foo/",
    ));

    $this->assertEquals("http://foo/bar", absolute_url("bar"));
    $this->assertEquals("http://foo/bar/foo", absolute_url("bar/foo"));
  }

  function testSubdirectory() {
    Config::overwrite(array(
      "absolute_url" => "http://foo:80/bar/",
    ));

    $this->assertEquals("http://foo:80/bar/bar", absolute_url("bar"));
    $this->assertEquals("http://foo:80/bar/bar/foo", absolute_url("bar/foo"));
  }

  function testExtraSlashes() {
    Config::overwrite(array(
      "absolute_url" => "http://foo/",
    ));

    $this->assertEquals("http://foo/bar", absolute_url("/bar"));
  }

}
