<?php namespace Eubby\Acl\Tests;

date_default_timezone_set('Asia/Singapore');

use DateTime;
use Mockery as m;
use Eubby\Acl\ThrottleModel;

class ThrottleModelTest extends \PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
		ThrottleModel::setAttemptLimit(5);
		ThrottleModel::setSuspensionTime(15);
	}

	public function testReturnUserObject()
	{
		$user = m::mock('StdClass');
		$user->shouldReceive('getResults')->once()->andReturn('foo');

		$throttle = m::mock('Eubby\Acl\ThrottleModel[user]');
		$throttle->shouldReceive('user')->once()->andReturn($user);

		$this->assertEquals('foo', $throttle->getUser());
	}

	public function testCanSetAttemptLimit()
	{
		ThrottleModel::setAttemptLimit(15);
		$this->assertEquals(15, ThrottleModel::getAttemptLimit());
	}

	public function testGettingLoginAttemptsWhenNoAttemptHasBeenMadeBefore()
	{
		$throttle = m::mock('Eubby\Acl\ThrottleModel[clearLoginAttemptsIfAllowed]');
		$throttle->shouldReceive('clearLoginAttemptsIfAllowed')->never();

		$this->assertEquals(0, $throttle->getLoginAttempts());
		$throttle->attempts = 1;
		$this->assertEquals(1, $throttle->getLoginAttempts());
	}

	public function testGettingLoginAttemptsResetsIfSuspensionTimeHasPassedSinceLastAttempt()
	{
		$throttle = m::mock('Eubby\Acl\ThrottleModel[save]');
		$this->addMockConnection($throttle);
		$throttle->getConnection()->getQueryGrammar()->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');

		ThrottleModel::setSuspensionTime(11);
		$lastAttemptAt = new DateTime;
		$lastAttemptAt->modify('-10 minutes');

		$throttle->last_attempt_at = $lastAttemptAt->format('Y-m-d H:i:s');
		$throttle->attempts = 3;
		$this->assertEquals(3, $throttle->getLoginAttempts());

		$throttle->shouldReceive('save')->once();
		ThrottleModel::setSuspensionTime(9);
		$this->assertEquals(0, $throttle->getLoginAttempts());

	}

	public function testSuspend()
	{
		$connection = m::mock('StdClass');
		$connection->shouldReceive('getQueryGrammar')->atLeast(1)->andReturn($connection);
		$connection->shouldReceive('getDateFormat')->atLeast(1)->andReturn('Y-m-d H:i:s');

		$throttle = m::mock('Eubby\Acl\ThrottleModel[save,getConnection]');
		$throttle->shouldReceive('getConnection')->atLeast(1)->andReturn($connection);
		$throttle->shouldReceive('save')->once();

		$this->assertNull($throttle->suspended_at);
		$throttle->suspend();

		$this->assertNotNull($throttle->suspended_at);
		$this->assertTrue($throttle->suspended);
	}

	public function testUnsuspend()
	{
		$connection = m::mock('StdClass');
		$connection->shouldReceive('getQueryGrammar')->atLeast(1)->andReturn($connection);
		$connection->shouldReceive('getDateFormat')->atLeast(1)->andReturn('Y-m-d H:i:s');

		$throttle = m::mock('Eubby\Acl\ThrottleModel[save,getConnection]');;
		$throttle->shouldReceive('getConnection')->atLeast(1)->andReturn($connection);

		$throttle->shouldReceive('save')->once();

		$lastAttemptAt = new DateTime;
		$suspendedAt   = new DateTime;

		$throttle->attempts        = 3;
		$throttle->last_attempt_at = $lastAttemptAt;
		$throttle->suspended       = true;
		$throttle->suspended_at    = $suspendedAt;

		$throttle->unsuspend();

		$this->assertEquals(0, $throttle->attempts);
		$this->assertNull($throttle->last_attempt_at);
		$this->assertFalse($throttle->suspended);
		$this->assertNull($throttle->suspended_at);
	}

	public function testIsSuspended()
	{
		$throttle = new ThrottleModel;
		$this->assertFalse($throttle->isSuspended());
	}

	protected function addMockConnection($model)
	{
		$model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
		$resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
		$model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
		$model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
	}
}