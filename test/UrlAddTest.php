<?php

use \Openclerk\Router;

class UrlAddTest extends \PHPUnit_Framework_TestCase {

  function testEmpty() {
    $this->assertEquals("index.php", Router::urlAdd("index.php", array()));
    $this->assertEquals("index.php?foo=bar", Router::urlAdd("index.php?foo=bar", array()));
  }

  /**
   * Basic tests for {@link Rotuer::urlAdd()}.
   */
  function testUrlAdd() {
    $this->assertEquals("url", Router::urlAdd('url', array()));
    $this->assertEquals("url?key=bar", Router::urlAdd('url', array('key' => 'bar')));
    $this->assertEquals("url?key=bar&bar=foo", Router::urlAdd('url', array('key' => 'bar', 'bar' => 'foo')));
  }

  /**
   * Basic tests for {@link Rotuer::urlAdd()} using absolute URLs.
   */
  function testAbsolute() {
    $this->assertEquals("http://openclerk.org/url", Router::urlAdd('http://openclerk.org/url', array()));
    $this->assertEquals("http://openclerk.org/url?key=bar", Router::urlAdd('http://openclerk.org/url', array('key' => 'bar')));
    $this->assertEquals("http://openclerk.org/url?key=bar&bar=foo", Router::urlAdd('http://openclerk.org/url', array('key' => 'bar', 'bar' => 'foo')));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} overwrites.
   */
  function testOverwrite() {
    $this->assertEquals("url?key=foo", Router::urlAdd('url', array('key' => 'bar', 'key' => 'foo')));
    $this->assertEquals("url?key=foo", Router::urlAdd('url?key=bar', array('key' => 'foo')));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} overwrites using absolute URLs.
   */
  function testOverwriteAbsolute() {
    $this->assertEquals("http://openclerk.org/url?key=foo", Router::urlAdd('http://openclerk.org/url', array('key' => 'bar', 'key' => 'foo')));
    $this->assertEquals("http://openclerk.org/url?key=foo", Router::urlAdd('http://openclerk.org/url?key=bar', array('key' => 'foo')));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} maintains hash fragments.
   */
  function testHashFragments() {
    $this->assertEquals("url?key=foo#hash=1", Router::urlAdd('url#hash=1', array('key' => 'bar', 'key' => 'foo')));
    $this->assertEquals("url?key=foo#hash=2", Router::urlAdd('url?key=bar#hash=2', array('key' => 'foo')));
    $this->assertEquals("url?key=foo#key=bar", Router::urlAdd('url?key=bar#key=bar', array('key' => 'foo')));
    $this->assertEquals("url?key=foo#hash=1&key=bar", Router::urlAdd('url?key=bar#hash=1&key=bar', array('key' => 'foo')));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} maintains hash fragments using absolute URLs.
   */
  function testHashFragmentsAbsolute() {
    $this->assertEquals("http://openclerk.org/url?key=foo#hash=1", Router::urlAdd('http://openclerk.org/url#hash=1', array('key' => 'bar', 'key' => 'foo')));
    $this->assertEquals("http://openclerk.org/url?key=foo#hash=2", Router::urlAdd('http://openclerk.org/url?key=bar#hash=2', array('key' => 'foo')));
    $this->assertEquals("http://openclerk.org/url?key=foo#key=bar", Router::urlAdd('http://openclerk.org/url?key=bar#key=bar', array('key' => 'foo')));
    $this->assertEquals("http://openclerk.org/url?key=foo#hash=1&key=bar", Router::urlAdd('http://openclerk.org/url?key=bar#hash=1&key=bar', array('key' => 'foo')));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} can delete keys with {@code null}.
   */
  function testKeyDelete() {
    $this->assertEquals("url", Router::urlAdd('url', array('key' => null)));
    $this->assertEquals("url", Router::urlAdd('url?key=bar', array('key' => null)));
  }

  /**
   * Tests that {@link Rotuer::urlAdd()} can delete keys with {@code null}.
   */
  function testKeyDeleteAbsolute() {
    $this->assertEquals("http://openclerk.org/url", Router::urlAdd('http://openclerk.org/url', array('key' => null)));
    $this->assertEquals("http://openclerk.org/url", Router::urlAdd('http://openclerk.org/url?key=bar', array('key' => null)));
  }

}
