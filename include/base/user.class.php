<?php

/*
用户基类
*/

abstract class User
{

	protected $name;		// @string
	protected $permission;	// @array
	
	public function __construct($name)
	{
		$this->name = $name;
	}

	public function __destruct()
	{
		// do nothing
	}
	
	abstract protected function get_permission();
}

?>
