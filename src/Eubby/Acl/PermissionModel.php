<?php namespace Eubby\Acl;

use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{
	protected $table = 'permissions';

	public function getId()
	{
		return $this->getKey();
	}
}