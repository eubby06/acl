<?php namespace Eubby\Acl\Tests;

use Mockery as m;
use Eubby\Acl\Acl;

class AclTest extends \PHPUnit_Framework_TestCase
{
	protected $userModel;
	protected $roleModel;
	protected $throttleModel;
	protected $sessionHelper;
	protected $cookieHelper;
	protected $acl;

	public function setUp()
	{
		$this->acl = new Acl(
			$this->userModel 		= m::mock('Eubby\Acl\UserModel'),
			$this->roleModel 		= m::mock('Eubby\Acl\RoleModel'),
			$this->throttleModel 	= m::mock('Eubby\Acl\ThrottleModel'),
			$this->sessionHelper 	= m::mock('Eubby\Acl\SessionHelper'),
			$this->cookieHelper 	= m::mock('Eubby\Acl\CookieHelper')
			);
	}

	public function tearDown()
	{
		m::close();
	}

	public function testLogsInUser()
	{
		$this->userModel->shouldReceive('getPersistCode')->once()->andReturn('randomstring');
		$this->userModel->shouldReceive('getId')->once()->andReturn(3);

		$this->sessionHelper->shouldReceive('put')->with(array('3','randomstring'))->once();

		$this->cookieHelper->shouldReceive('forever')->with(array('3','randomstring'))->once();

		$this->acl->login($this->userModel, true);
	}

	public function testLogout()
	{
		$this->sessionHelper->shouldReceive('get')->once();
		$this->sessionHelper->shouldReceive('forget')->once();
		$this->cookieHelper->shouldReceive('get')->once();
		$this->cookieHelper->shouldReceive('forget')->once();

		$this->acl->logout();

		$this->assertNull($this->acl->getUser());
	}

	public function testAuthenticate()
	{
		$this->acl = m::mock('Eubby\Acl\Acl[login]');
		$this->acl->__construct(
			$this->userModel,
			$this->roleModel,
			$this->throttleModel,
			$this->sessionHelper,
			$this->cookieHelper
			);

		$credentials = array('username' => 'yonanne', 'password' => 'admin');

		$this->userModel->shouldReceive('findByCredentials')->with($credentials)->once()->andReturn($user = m::mock('Eubby\Acl\UserModel'));

		$this->userModel->shouldReceive('getLoginName')->once()->andReturn('username');

		$this->acl->shouldReceive('login')->with($user, false)->once();

		$user = $this->acl->authenticate($credentials, false);

		$this->assertInstanceOf('Eubby\Acl\UserModel', $user);

	}

	public function testCheck()
	{
		$this->userModel->shouldReceive('find')->with(3)->once()->andReturn($user = m::mock('Eubby\Acl\UserModel[checkPersistCode]'));
		$this->sessionHelper->shouldReceive('get')->once()->andReturn(array('3','randomstring'));
		
		$user->shouldReceive('checkPersistCode')->with('randomstring')->once()->andReturn(true);

		$this->acl->check();
	}
}