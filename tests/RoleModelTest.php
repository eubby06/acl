<?php namespace Eubby\Acl\Tests;

use Mockery as m;
use Eubby\Acl\RoleModel;

class RoleModelTest extends \PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function testRoleId()
	{
		$role = new RoleModel;
		$role->id = 123;

		$this->assertEquals(123, $role->getId());
	}

	public function testRoleName()
	{
		$role = new RoleModel;
		$role->name = 'foo';

		$this->assertEquals('foo', $role->getName());
	}

	public function testPermissionsAreMergedAndRemovedProperly()
	{
		$role = new RoleModel;

		$role->permissions = array(
			'foo' => 1,
			'bar' => 1,
			);

		$role->permissions = array(
			'baz' => 1,
			'qux' => 1,
			'foo' => 0,
			);

		$expected = array(
			'bar' => 1,
			'baz' => 1,
			'qux' => 1,
			);

		$this->assertEquals($expected, $role->permissions);
	}

	public function testPermissionsAreCastAsAnArrayWhenTheModelIs()
	{
		$role = new RoleModel;
		$role->name = 'foo';
		$role->permissions = array(
			'bar' => 1,
			'baz' => 1,
			'qux' => 1,
		);

		$expected = array(
			'name' => 'foo',
			'permissions' => array(
				'bar' => 1,
				'baz' => 1,
				'qux' => 1,
			),
		);

		$this->assertEquals($expected, $role->toArray());
	}

	public function testSettingPermissionsWhenAllPermissionsAreZero()
	{
		$role = new RoleModel;

		$role->permissions = array('admin' => 0);

		$this->assertEquals(array(), $role->permissions);
	}

	public function testValidation()
	{
		$role = m::mock('Eubby\Acl\RoleModel[newQuery]');
		$role->name = 'foo';

		$query = m::mock('StdClass');
		$query->shouldReceive('where')->with('name', '=', 'foo')->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn(null);

		$role->shouldReceive('newQuery')->once()->andReturn($query);

		$role->validate();
	}
}