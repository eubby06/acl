<?php namespace Eubby\Acl;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
	protected $table = 'roles';

	public function getId()
	{
		return $this->getKey();
	}

	public function users()
	{
		return $this->belongsToMany('Eubby\Acl\UserModel', 'role_user');
	}

	public function getPermissions()
	{
		return $this->permissions;
	}

	public function hasAccess($permissions, $all = true)
	{
		$RolePermissions = $this->getPermissions();

		if ( ! is_array($permissions))
		{
			$permissions = (array) $permissions;
		}

		foreach($permissions as $permission)
		{
			$matched = true;

			if ((strlen($permission) > 1) and ends_with($permission, '*'))
			{

			}
		}
	}

	public function hasAnyAccess(array $permissions)
	{
		return $this->hasAccess($permissions, false);
	}

	public function setPermissionsAttribute(array $permission)
	{
		$permissions = array_merge($this->getPermissions(), $permissions);

		foreach ($permissions as $permission => &$value)
		{
			if(! in_array($value = (int) $value, $this->allowedPermissionsValues))
			{
				throw new \InvalidArgumentException('Invalid value for permission');
			}

			if ($value === 0)
			{
				unset($permissions[$permission])
			}
		}

		$this->attributes['permissions'] = (! empty($permissions)) ? json_encode($permissions) : '';
	}

	public function toArray()
	{
		
	}
}