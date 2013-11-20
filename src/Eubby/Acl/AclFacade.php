<?php namespace Eubby\Acl;

use Illuminate\Support\Facades\Facade;

class Acl extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'Acl'; }

}