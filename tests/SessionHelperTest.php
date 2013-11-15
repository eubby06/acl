<?php namespace Eubby\Acl\Tests;

use Mockery as m;

class SessionHelperTest extends \PHPUnit_Framework_TestCase
{
	protected $store;

	protected $session;

	public function setUp()
	{
		$this->store = m::mock('Illuminate\Session\Store');
		$this->session = new \Eubby\Acl\SessionHelper($this->store, 'foo');
	}

	public function tearDown()
	{
		m::close();
	}
	
	public function testOverridingKey()
	{
		$this->assertEquals('foo', $this->session->getKey());
	}

	public function testPut()
	{
		$this->store->shouldReceive('put')->with('foo', 'bar')->once();

		$this->session->put('bar');
	}

	public function testGet()
	{
		$this->store->shouldReceive('get')->with('foo')->once()->andReturn('bar');

		$this->assertEquals('bar', $this->session->get());
	}

	public function testForget()
	{
		$this->store->shouldReceive('forget')->with('foo')->once();

		$this->session->forget();
	}
}