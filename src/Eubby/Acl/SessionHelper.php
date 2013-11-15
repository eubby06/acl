<?php namespace Eubby\Acl;

use Illuminate\Session\Store as SessionStore;

class SessionHelper
{

	protected $key = 'key_to_retrive_your_session';

	protected $session;


	public function __construct(SessionStore $session, $key = null)
	{
		$this->session = $session;

		if (isset($key))
		{
			$this->key = $key;
		}
	}

	public function getKey()
	{
		return $this->key;
	}

	public function put($value)
	{
		$this->session->put($this->getKey(), $value);
	}

	public function get()
	{
		return $this->session->get($this->getKey());
	}

	public function forget()
	{
		$this->session->forget($this->getKey());
	}

}
