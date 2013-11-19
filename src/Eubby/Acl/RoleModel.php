<?php namespace Eubby\Acl;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
	protected $table = 'roles';

	protected $guarded = array();

	protected $allowedPermissionsValues = array(0, 1);

	public function getId()
	{
		return $this->getKey();
	}

	public function getName()
	{
		return $this->name;
	}

	public function users()
	{
		return $this->belongsToMany('Eubby\Acl\UserModel', 'role_user');
	}

	public function save(array $options = array())
	{
		$this->validate();
		return parent::save();
	}

	public function delete()
	{
		$this->users()->detach();
		return parent::delete();
	}

	public function getPermissions()
	{
		return $this->permissions;
	}

	public function hasAccess($permissions, $all = true)
	{
		$rolePermissions = $this->getPermissions();

		if ( ! is_array($permissions))
		{
			$permissions = (array) $permissions;
		}

		foreach($permissions as $permission)
		{
			$matched = true;

			if ((strlen($permission) > 1) and ends_with($permission, '*'))
			{
				$matched = false;

				foreach ($rolePermissions as $rolePermission => $value)
				{
					$checkPermission = substr($permission, 0, -1);

					if ($checkPermission != $rolePermission and starts_with($rolePermission, $checkPermission) and $value == 1)
					{
						$matched = true;
						break;
					}
				}
			}
			// Now, let's check if the permission starts in a wildcard "*" symbol.
			// If it does, we'll check through all the merged permissions to see
			// if a permission exists which matches the wildcard.
			elseif ((strlen($permission) > 1) and starts_with($permission, '*'))
			{
				$matched = false;

				foreach ($groupPermissions as $groupPermission => $value)
				{
					// Strip the '*' off the start of the permission.
					$checkPermission = substr($permission, 1);

					// We will make sure that the merged permission does not
					// exactly match our permission, but ends wtih it.
					if ($checkPermission != $groupPermission and ends_with($groupPermission, $checkPermission) and $value == 1)
					{
						$matched = true;
						break;
					}
				}
			}

			else
			{
				$matched = false;

				foreach ($groupPermissions as $groupPermission => $value)
				{
					// This time check if the groupPermission ends in wildcard "*" symbol.
					if ((strlen($groupPermission) > 1) and ends_with($groupPermission, '*'))
					{
						$matched = false;

						// Strip the '*' off the end of the permission.
						$checkGroupPermission = substr($groupPermission, 0, -1);

						// We will make sure that the merged permission does not
						// exactly match our permission, but starts wtih it.
						if ($checkGroupPermission != $permission and starts_with($permission, $checkGroupPermission) and $value == 1)
						{
							$matched = true;
							break;
						}
					}

					// Otherwise, we'll fallback to standard permissions checking where
					// we match that permissions explicitly exist.
					elseif ($permission == $groupPermission and $groupPermissions[$permission] == 1)
					{
						$matched = true;
						break;
					}
				}
			}

			if ($all === true and $matched === false)
			{
				return false;
			}
			elseif ($all === false and $matched === true)
			{
				return true;
			}
		}

		if ($all === false)
		{
			return false;
		}

		return true;
	}

	public function hasAnyAccess(array $permissions)
	{
		return $this->hasAccess($permissions, false);
	}

	public function getPermissionsAttribute($permissions)
	{
		if ( ! $permissions)
		{
			return array();
		}

		if (is_array($permissions))
		{
			return $permissions;
		}

		if ( ! $_permissions = json_decode($permissions, true))
		{
			throw new \InvalidArgumentException("Cannot JSON decode permissions [$permissions].");
		}

		return $_permissions;
	}

	public function setPermissionsAttribute(array $permissions)
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
				unset($permissions[$permission]);
			}
		}

		$this->attributes['permissions'] = (! empty($permissions)) ? json_encode($permissions) : '';

	}



	public function validate()
	{
		if (! $name = $this->name)
		{
			return false;
		}

		$query = $this->newQuery();
		$persistedGroup = $query->where('name', '=', $name)->first();

		if ($persistedGroup and $persistedGroup->getId() != $this->getId())
		{
			return false;
		}

		return true;
	}
}