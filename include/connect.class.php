<?php

/* 这是用于通信的类，主要与前台界面进行通信，对其他类隐藏通信细节 */

class Connect
{
	private $net_buffer = '';
	private $input;
	private $output;
	public $error;

	public function __construct()
	{
		$this->error = new Error();

		$this->input = fopen('php://input', "r");
		$this->output = fopen('php://output', "w");
	}

	public function __destruct()
	{
		if ($this->input)
			fclose($this->input);
		
		if ($this->output)
			fclose($this->output);
	}

	public function init()
	{
		if (!$this->input)
			$this->error->error_handle(4, "init input stream failed!\n");

		if (!$this->output)
			$this->error->error_handle(4, "init output stream failed!\n");

		return $this->error->get_last_error();
	}

	/*
	description:	获取数据
	params:		string &$data
	return:		失败返回错误消息，否则返回数据
	*/
	public function get_data(&$data = NULL)
	{
		// 仅支持post提交的数据
		if (!strcmp('POST', $_SERVER['REQUEST_METHOD']))
		{
			$this->net_buffer = '';

			while (!feof($this->input))
				$this->net_buffer .= fread($this->input, 8192);

			if (NULL != $data)
				$data = $this->net_buffer;
		}
		else if (!strcmp('GET', $_SERVER['REQUEST_METHOD']))
			return $_GET;
		
		return $this->net_buffer;
	}

	/*
	description:	发送数据到前台页面
	params:		string $data
	return:		失败返回错误消息，否则返回no_error
	*/
	public function send_data($data)
	{
		if (!is_string($data))
			$data = json_encode($data);
		
		if (ord($data) != ord("[") && ord($data) != ord("{"))
			return $this->error->error_handle(4, "数据不是json格式！");
		
		if (strlen($data) == 0)
			return $this->error->error_handle(4, "没有发送数据!\n");
		
		$this->net_buffer = $data;

		$len = 0;

		while ($len < strlen($data))
			$len += fwrite($this->output, $data);

		ob_flush();
		fflush($this->output);

		return $this->error->no_error();
	}

	public function get_net_buffer()
	{
		return $this->net_buffer;
	}
}

?>
