<?php namespace Eubby\Acl;

use Eubby\Acl\UserModel;
use Eubby\Acl\RoleModel;
use Eubby\Acl\PermissionModel;
use Eubby\Acl\SessionHelper;
use Eubby\Acl\CookieHelper;

class Acl
{
	protected $userModel;
	protected $permissionModel;
	protected $roleModel;
	protected $sessionHelper;
	protected $cookieHelper;

	public function __construct(
		UserModel $userModel = null, 
		RoleModel $roleModel = null, 
		PermissionModel $permissionModel = null,
		SessionHelper $session = null,
		CookieHelper $cookie = null
		)
	{
		$this->userModel = $userModel ?: new UserModel;
		$this->roleModel = $roleModel ?: new RoleModel;
		$this->permissionModel = $permissionModel ?: new PermissionModel;
		$this->sessionHelper = $session;
		$this->cookieHelper = $cookie;
	}

	public function createUser(array $credentials)
	{
		return $this->userModel->create($credentials);
	}

	public function register(array $credentials, $activate = false)
	{
		$user = $this->userModel->create($credentials);

		if ($activate)
		{
			$user->activate($user->getActivateCode());
		}

		return $this->user = $user;
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
		$user = $this->userModel->findByCredentials($credentials);

		if ($user)
		{
			$this->login($user, $remember);
		}
		
		return $user;
	}

	public function check()
	{
		if (is_null($this->userModel))
		{
			if ( ! $userArray = $this->session->get() and ! $userArray = $this->cookie->get())
			{
				return false;
			}

			if ( ! is_array($userArray) or count($userArray) !== 2)
			{
				return false;
			}

			list($id, $persistCode) = $userArray;

			$user = $this->userModel->find($id);

			if (is_null($user)) return false;

			if (! $user->checkPersistCode($persistCode))
			{
				return false;
			}

			$this->userModel = $user;
		}

		return true;
	}

	public function login(UserModel $user, $remember = false)
	{
		//check if user is activated

		$this->userModel = $user;

		$toPersist = array($user->id, $this->userModel->getPersistCode());

		//todo: create a session class
		//$this->session->put($toPersist);

		if ($remember)
		{
			//todo: create a cookie class
			//$this->cookie->forever($toPersist);
		}
	}

	public function logout()
	{
		$this->userModel = null;

		//todo:
		//$this->session->forget();
		//$this->cookie->forget();
	}

	public function getUser()
	{
		if (is_null($this->userModel))
		{
			$this->check();
		}

		return $this->userModel;
	}
}