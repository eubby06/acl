<?php namespace Eubby\Acl\Tests;

use Mockery as m;
use Eubby\Acl\UserModel;

class UserModelTest extends \PHPUnit_Framework_TestCase
{
	protected $user;

	public function __construct()
	{
		$this->user = new UserModel;
	}

	public function tearDown()
	{
		m::close();
	}

	public function testHasManyRoles()
	{
		$user = m::mock('Eubby\Acl\UserModel[getKey]');
		$user->shouldReceive('getKey')->once()->andReturn('foo');

		$this->assertEquals('foo', $user->getId());
	}

	public function testUserLoginNameCallsLoginName()
	{
		$user = m::mock('Eubby\Acl\UserModel[getLoginName]');
		$user->shouldReceive('getLoginName')->once()->andReturn('admin');

		$user->getLogin();
	}

	public function testUserLoginCallsLoginAttribute()
	{
		$this->user->email = 'foo@bar.com';

		$this->assertEquals('foo@bar.com', $this->user->getLogin());
	}

	public function testUserPasswordCallsPasswordAttribute()
	{
		UserModel::setHasher($hasher = m::mock('Illuminate\Hashing\BcryptHasher'));
		$hasher->shouldReceive('make')->with('unhashed_password')->once()->andReturn('hashed_password');

		$this->user->password = 'unhashed_password';

		$this->assertEquals('hashed_password', $this->user->getPassword());
	}

	public function testGettingRoles()
	{
		$pivot = m::mock('StdClass');
		$pivot->shouldReceive('get')->once()->andReturn('foo');

		$user = m::mock('Eubby\Acl\UserModel[roles]');
		$user->shouldReceive('roles')->once()->andReturn($pivot);

		$this->assertEquals('foo', $user->getRoles());
	}

	public function testUserHasRole()
	{
		$role1 = m::mock('Eubby\Acl\RoleModel');
		$role1->shouldReceive('getId')->once()->andReturn(123);

		$role2 = m::mock('Eubby\Acl\RoleModel');
		$role2->shouldReceive('getId')->once()->andReturn(234);

		$user = m::mock('Eubby\Acl\UserModel[getRoles]');
		$user->shouldReceive('getRoles')->once()->andReturn(array($role2));

		$this->assertFalse($user->hasRole($role1));
	}

	public function testAddRole()
	{
		$role = m::mock('Eubby\Acl\RoleModel');

		$user = m::mock('Eubby\Acl\UserModel[hasRole]');
		$user->shouldReceive('hasRole')->once()->andReturn(true);

		$user->addRole($role);
	}

	public function testDoesNotAddRoleIfAlreadyExist()
	{
		$role = m::mock('Eubby\Acl\RoleModel');

		$user = m::mock('Eubby\Acl\UserModel[hasRole,roles]');
		$user->shouldReceive('hasRole')->with($role)->once()->andReturn(true);
		$user->shouldReceive('roles')->never();

		$user->addRole($role);
	}

	public function testRemovingFromGroupAttachesRelationship()
	{
		$role = m::mock('Eubby\Acl\RoleModel');

		$pivot = m::mock('StdClass');
		$pivot->shouldReceive('attach')->with($role)->once();

		$user = m::mock('Eubby\Acl\UserModel[hasRole,roles]');
		$user->shouldReceive('hasRole')->once()->andReturn(false);
		$user->shouldReceive('roles')->once()->andReturn($pivot);

		$this->assertTrue($user->addRole($role));
	}

	public function testRemovingFromGroupDetachesRelationship()
	{
		$role = m::mock('Eubby\Acl\RoleModel');

		$relationship = m::mock('StdClass');
		$relationship->shouldReceive('detach')->with($role)->once();

		$user  = m::mock('Eubby\Acl\UserModel[hasRole,roles]');
		$user->shouldReceive('hasRole')->once()->andReturn(true);
		$user->shouldReceive('roles')->once()->andReturn($relationship);

		$this->assertTrue($user->removeRole($role));
	}

	/**
	 *  @expectedException Eubby\Acl\LoginRequiredException
	 */
	public function testThrowsLoginExceptionIfNoneGive()
	{
		$user = new UserModel;
		$user->validate();
	}

	/**
	 *  @expectedException Eubby\Acl\PasswordRequiredException
	 */
	public function testThrowsPasswordExceptionIfNoneGive()
	{
		$user = new UserModel;
		$user->email = 'foo@bar.com';
		$user->validate();
	}

	/**
	 *  @expectedException Eubby\Acl\UserExistsException
	 */
	public function testThrowsUserExistsExceptionIfNoneGive()
	{
		UserModel::setHasher($hasher = m::mock('Illuminate\Hashing\BcryptHasher'));
		$hasher->shouldReceive('make')->with('bazbat')->once()->andReturn('hashed_bazbat');

		$persistedUser = m::mock('Eubby\Acl\UserModel');
		$persistedUser->shouldReceive('getId')->once()->andReturn(123);

		$user = m::mock('Eubby\Acl\UserModel[newQuery]');
		$user->email = 'foo@bar.com';
		$user->password = 'bazbat';

		$query = m::mock('StdClass');
		$query->shouldReceive('where')->with('email', '=', 'foo@bar.com')->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn($persistedUser);

		$user->shouldReceive('newQuery')->once()->andReturn($query);

		$user->validate();
	}
}