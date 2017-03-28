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

	public function get_data(&$data = NULL)
	{
		// 仅支持post提交的数据
		if (strcmp('POST', $_SERVER['REQUEST_METHOD']))
			return $this->error->error_handle(4, "不是post方法提交的数据！\n");

		$this->net_buffer = '';

		while (!feof($this->input))
			$this->net_buffer .= fread($this->input, 8192);

		if (NULL != $data)
			$data = $this->net_buffer;
		
		return $this->error->no_error();
	}

	public function send_data($data)
	{
		$this->net_buffer = $data;
		
		if (strlen($data) == 0)
			return $this->error->error_handle(4, "没有发送数据!\n");

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
