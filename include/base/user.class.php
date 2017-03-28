<?php

/*
用户基类
*/

class User
{

	protected $name;		// @string
	protected $permission;	// @array
	
	public function __construct($name, $permission)
	{
		$this->name = $name;
		$this->permission = $permission;
	}

	public function __destruct()
	{
		// do nothing
	}
}

?>
