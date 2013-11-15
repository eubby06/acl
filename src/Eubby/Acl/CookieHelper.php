<?php namespace Eubby\Acl;

use Illuminate\Container\Container;
use Illuminate\Cookie\CookieJar;
use Symfony\Component\HttpFoundation\Cookie;

class CookieHelper {


	protected $key = 'cartalyst_sentry';

	protected $jar;

	protected $cookie;


	public function __construct(CookieJar $jar, $key = null)
	{
		$this->jar = $jar;

		if (isset($key))
		{
			$this->key = $key;
		}
	}

	public function getKey()
	{
		return $this->key;
	}

	public function put($value, $minutes)
	{
		$this->cookie = $this->jar->make($this->getKey(), $value, $minutes);
	}

	public function forever($value)
	{
		$this->cookie = $this->jar->forever($this->getKey(), $value);
	}

	public function get()
	{
		return $this->jar->get($this->getKey());
	}

	public function forget()
	{
		$this->cookie = $this->jar->forget($this->getKey());
	}
 
	public function getCookie()
	{
		return $this->cookie;
	}

}
