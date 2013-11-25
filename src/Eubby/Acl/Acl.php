<?php namespace Eubby\Acl;

use Eubby\Acl\UserModel;
use Eubby\Acl\RoleModel;
use Eubby\Acl\ThrottleModel;
use Eubby\Acl\SessionHelper;
use Eubby\Acl\CookieHelper;

class Acl
{
	protected $userModel;
	protected $throttleModel;
	protected $roleModel;
	protected $sessionHelper;
	protected $cookieHelper;

	protected $privilegedUser = null;

	public function __construct(
		UserModel $userModel = null, 
		RoleModel $roleModel = null, 
		ThrottleModel $throttleModel = null,
		SessionHelper $session = null,
		CookieHelper $cookie = null
		)
	{
		$this->userModel = $userModel ?: new UserModel;
		$this->roleModel = $roleModel ?: new RoleModel;
		$this->throttleModel = $throttleModel ?: new ThrottleModel;
		$this->sessionHelper = $session;
		$this->cookieHelper = $cookie;
	}

	public function createUser(array $credentials)
	{
		return $this->privilegedUser = $this->userModel->attemptSave($credentials);
	}

	public function register(array $credentials, $activate = false)
	{
		$this->privilegedUser = $this->userModel->attemptSave($credentials);

		if ($activate)
		{
			$this->privilegedUser->activate($this->privilegedUser->getActivateCode());
		}

		return $this->privilegedUser;
	}

	public function authenticate(array $credentials, $remember = false)
	{
		//return either email or username
		$loginName = $this->userModel->getLoginName(); 

		//make sure login name and password is not empty
		if (empty($credentials[$loginName]))
		{
			return false;
		}

		if (empty($credentials['password']))
		{
			return false;
		}

		//todo: check if user is ban

		//find user by credentials
		$this->privilegedUser = $this->userModel->findByCredentials($credentials);

		if ($this->privilegedUser)
		{
			$this->login($this->privilegedUser, $remember);
		}
		
		return $this->privilegedUser;
	}

	public function check()
	{
		if (is_null($this->privilegedUser))
		{
			if ( ! $userArray = $this->sessionHelper->get() and ! $userArray = $this->cookieHelper->get())
			{
				return false;
			}

			if ( ! is_array($userArray) or count($userArray) !== 2)
			{
				return false;
			}

			list($id, $persistCode) = $userArray;

			$this->privilegedUser = $this->userModel->find($id);

			if (is_null($this->privilegedUser)) return false;

			if (! $this->privilegedUser->checkPersistCode($persistCode))
			{
				return false;
			}
		}

		return true;
	}

	public function login(UserModel $user, $remember = false)
	{
		//check if user is activated

		$this->privilegedUser = $user;

		$toPersist = array($this->privilegedUser->getId(), $this->privilegedUser->getPersistCode());

		$this->sessionHelper->put($toPersist);

		if ($remember)
		{
			$this->cookieHelper->forever($toPersist);
		}
	}

	public function logout()
	{
		$this->privilegedUser = null;

		$this->sessionHelper->forget();
		$this->cookieHelper->forget();
	}

	public function setUser(UserModel $user)
	{
		$this->privilegedUser = $user;
	}

	public function getUser()
	{
		if (is_null($this->privilegedUser))
		{
			$this->check();
		}

		return $this->privilegedUser;
	}

	public function roleProvider()
	{
		return $this->roleModel;
	}

	public function permissionProvider()
	{
		return $this->permissionModel;
	}

	public function userProvider()
	{
		return $this->userModel;
	}

	public function getErrors()
	{
		return $this->userModel->getErrors();
	}
}