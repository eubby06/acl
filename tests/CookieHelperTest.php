<?php namespace Eubby\Acl\Tests;

use Mockery as m;

class CookieHelperTest extends \PHPUnit_Framework_TestCase
{
	protected $jar;

	protected $cookie;

	public function setUp()
	{
		$this->jar = m::mock('Illuminate\Cookie\CookieJar');
		$this->cookie = new \Eubby\Acl\CookieHelper($this->jar, 'foo');
	}

	public function tearDown()
	{
		m::close();
	}

	public function testOverridesKey()
	{
		$this->assertEquals('foo', $this->cookie->getKey());
	}

	public function testPut()
	{
		$this->jar->shouldReceive('make')->with('foo', 'bar', 123)->once();
		$this->cookie->put('bar', 123);
	}

	public function testForever()
	{
		$this->jar->shouldReceive('forever')->with('foo', 'bar')->once();
		$this->cookie->forever('bar');
	}

	public function testForget()
	{
		$this->jar->shouldReceive('forget')->with('foo')->once();
		$this->cookie->forget();
	}
}