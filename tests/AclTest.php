<?php namespace Eubby\Acl\Tests;

use Mockery as m;
use Eubby\Acl\Acl;

class AclTest extends \PHPUnit_Framework_TestCase
{
	protected $userModel;
	protected $roleModel;
	protected $permissionModel;
	protected $sessionHelper;
	protected $cookieHelper;
	protected $acl;

	public function setUp()
	{
		$this->acl = new Acl(
			$this->userModel 		= m::mock('Eubby\Acl\UserModel'),
			$this->roleModel 		= m::mock('Eubby\Acl\RoleModel'),
			$this->permissionModel 	= m::mock('Eubby\Acl\PermissionModel'),
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
		$this->userModel->shouldReceive('getPersistCode')->once()->andReturn('persist_code');
		$this->sessionHelper->shouldReceive('put')->with(array('foo', 'persist_code'))->once();

		$this->acl->login($this->userModel);
	}
}