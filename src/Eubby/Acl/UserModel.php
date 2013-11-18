<?php namespace Eubby\Acl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Hashing\BcryptHasher;
use Eubby\Acl\RoleModel;
use Eubby\Acl\UserModel;

class UserModel extends Model
{
	protected $table = 'users';

	protected $hashableAttributes = array(
		'password',
		'persist_code'
		);

	protected $guarded = array(
		'reset_password_code',
		'activation_code',
		'persist_code'
		);

	protected static $loginAttribute = 'email';

	protected static $hasher;

	protected $userRoles = null;

	public function getId()
	{
		return $this->getKey();
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function activate($code)
	{
		if (! $this->activated)
		{
			if ($code == $this->activation_code)
			{
				$this->activation_code = null;
				$this->activated = true;
				$this->activated_at = new DateTime;
				return $this->save();
			}
		}

		return false;
	}

	public function getActivationCode()
	{
		return $this->activation_code;
	}

	public function getLoginName()
	{
		return static::$loginAttribute;
	}

	public function getLogin()
	{
		return $this->{$this->getLoginName()};
	}

	public function setAttribute($key, $value)
	{
		if (in_array($key, $this->hashableAttributes) and !empty($value))
		{
			$value = static::$hasher->make($value);
		}

		return parent::setAttribute($key, $value);
	}

	public static function setLoginAttributeName($loginAttribute)
	{
		static::$loginAttribute = $loginAttribute;
	}

	public static function getLoginAttributeName()
	{
		return static::$loginAttribute;
	}

	public static function setHasher(BcryptHasher $hasher = null)
	{
		static::$hasher = $hasher ?: new BcryptHasher;
	}

	public function getPersistCode()
	{
		$this->persist_code = $this->getRandomString();

		$this->save();

		return $this->persist_code;
	}

	public function checkPersistCode($code)
	{
		if ($this->persist_code == $code) 
			return true;
		else
			return false;
	}

	public function getRandomString($length = 42)
	{
		// We'll check if the user has OpenSSL installed with PHP. If they do
		// we'll use a better method of getting a random string. Otherwise, we'll
		// fallback to a reasonably reliable method.
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			// We generate twice as many bytes here because we want to ensure we have
			// enough after we base64 encode it to get the length we need because we
			// take out the "/", "+", and "=" characters.
			$bytes = openssl_random_pseudo_bytes($length * 2);

			// We want to stop execution if the key fails because, well, that is bad.
			if ($bytes === false)
			{
				throw new \RuntimeException('Unable to generate random string.');
			}

			return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
		}

		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
	}

	public function roles()
	{
		return $this->belongsToMany('Eubby\Acl\RoleModel', 'role_user');
	}

	public function getRoles()
	{
		if (! $this->userRoles)
		{
			$this->userRoles = $this->roles()->get();
		}

		return $this->userRoles;
	}

	public function addRole(RoleModel $role)
	{
		if (! $this->hasRole($role))
		{
			$this->roles()->attach($role);
			$this->userRoles = null;
		}

		return true;
	}

	public function removeRole(RoleModel $role)
	{
		if ($this->hasRole($role))
		{
			$this->roles()->detach($role);
			$this->userRoles = null;
		}

		return true;
	}

	public function hasRole(RoleModel $role)
	{
		foreach($this->getRoles() as $_role)
		{
			if ($_role->getId() == $role->getId())
			{
				return true;
			}
		}

		return false;
	}
}