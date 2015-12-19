<?php

  define('ROOT', __DIR__.'/../../');
  require_once ROOT.'classes/response.php';

  class SFResponseTest extends PHPUnit_Framework_TestCase
  {
    public function testSetValue()
    {
      $this->assertEquals('', SFResponse::get('name'));
      SFResponse::set('name', 'testname');
      $this->assertEquals('testname', SFResponse::get('name'));
    }

    public function testExistsAction()
    {
      $this->assertEquals(false, SFResponse::isActionExists('action'), 'action');
      $this->assertEquals(false, SFResponse::isActionExists('action/index'), 'action/index');
      $this->assertEquals(false, SFResponse::isActionExists('action/'), 'action/');
      $this->assertEquals(false, SFResponse::isActionExists('action/index/'), 'action/index/');
      $this->assertEquals(false, SFResponse::isActionExists('action.php'), 'action.php');
      $this->assertEquals(false, SFResponse::isActionExists('action/index.php'), 'action/index.php');
      $this->assertEquals(false, SFResponse::isActionExists('action/__json/'), 'action/__json/');
      $this->assertEquals(false, SFResponse::isActionExists('action/index/__json/'), 'action/index/__json/');
      $this->assertEquals(false, SFResponse::isActionExists('action/__json'), 'action/__json');
      $this->assertEquals(false, SFResponse::isActionExists('action/index/__json'), 'action/index/__json');

      $this->assertEquals(false, SFResponse::isActionExists('/action'), '/action');
      $this->assertEquals(false, SFResponse::isActionExists('/action/index'), '/action/index');
      $this->assertEquals(false, SFResponse::isActionExists('/action/'), '/action/');
      $this->assertEquals(false, SFResponse::isActionExists('/action/index/'), '/action/index/');
      $this->assertEquals(false, SFResponse::isActionExists('/action.php'), '/action.php');
      $this->assertEquals(false, SFResponse::isActionExists('/action/index.php'), '/action/index.php');
      $this->assertEquals(false, SFResponse::isActionExists('/action/__json/'), '/action/__json/');
      $this->assertEquals(false, SFResponse::isActionExists('/action/index/__json/'), '/action/index/__json/');
      $this->assertEquals(false, SFResponse::isActionExists('/action/__json'), '/action/__json');
      $this->assertEquals(false, SFResponse::isActionExists('/action/index/__json'), '/action/index/__json');

      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action'), ROOT.'tests/action');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/index'), ROOT.'tests/action/index');
      $this->assertEquals(false, SFResponse::isActionExists(ROOT.'tests/action.php'), ROOT.'tests/action.php');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/index.php'), ROOT.'tests/action/index.php');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/'), ROOT.'tests/action/');
      $this->assertEquals(false, SFResponse::isActionExists(ROOT.'tests/action/index/'), ROOT.'tests/action/index/');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/__json/'), ROOT.'tests/action/__json/');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/index/__json/'), ROOT.'tests/action/index/__json/');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/__json'), ROOT.'tests/action/__json');
      $this->assertEquals(true, SFResponse::isActionExists(ROOT.'tests/action/index/__json'), ROOT.'tests/action/index/__json');

      $this->assertEquals(true, SFResponse::isActionExists('tests/action'), 'tests/action');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/index'), 'tests/action/index');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/'), 'tests/action/');
      $this->assertEquals(false, SFResponse::isActionExists('tests/action/index/'), 'tests/action/index/');
      $this->assertEquals(false, SFResponse::isActionExists('tests/action.php'), 'tests/action.php');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/index.php'), 'tests/action/index.php');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/__json/'), 'tests/action/__json/');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/index/__json/'), 'tests/action/index/__json/');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/__json'), 'tests/action/__json');
      $this->assertEquals(true, SFResponse::isActionExists('tests/action/index/__json'), 'tests/action/index/__json');
    }

    public function testStopPropagation()
    {
      $this->assertEquals(true, SFResponse::isPropagation());
      SFResponse::stopPropagation();
      $this->assertEquals(false, SFResponse::isPropagation());
    }
  }