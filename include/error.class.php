<?php

/* 错误处理类 */

class Error
{
	private $error_array;
	private $error_count = 0;

	public function __construct()
	{		
		$this->error_array[0] = Array('error_code'=>0, 'error_message'=>NULL);	
	}

	public function __destruct()
	{
		// 不做任何事
	}

	public function error_handle($code, $message)
	{
		return $this->error_array[$this->error_count++] = array('error_code'=>$code, 'error_message'=>$message);
	}

	public function get_count()
	{
		return $this->error_count;
	}

	public function get_last_error()
	{
		if ($this->error_count > 0)
			return $this->error_array[$this->error_count - 1];

		return $this->no_error();
	}

	public function get_error($num)
	{
		if ($num < $this->error_count)
			return $this->error_array[$this->error_count - $num];

		return $this->no_error();
	}
	
	public function get_last_error_JSON()
	{
		if ($this->error_count > 0)
			return json_encode($this->error_array[$this->error_count - 1]);

		return json_encode($this->no_error());
	}

	public function get_error_JSON($num)
	{
		if ($num < $this->error_count)
			return json_encode($this->error_array[$this->error_count - $num]);

		return json_encode($this->no_error());
	}

	public function no_error()
	{
		// 这是一个特殊的函数,用于标明没有错误产生
		return array('error_code'=>-1, 'error_message'=>'no error!');
	}

	public function is_no_error($error)
	{
		// 特殊函数,用于判断返回值是否无错
		if (is_array($error))
			if (array_key_exists('error_code', $error))
				return $error['error_code'] == -1;

		return true;
	}
}

?>
