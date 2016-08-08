<?php

include "tools.php";

class User
{
	private $name;
	private $prev_offset;
	private $next_offset;
	private $books;
	private $current_book;
	private $exe_function;
	private $run_lock;

	public function __construct($name)
	{
		/*
		这里添加代码，用于根据name来取得保存的信息，如果目标文件不存在，则创建，并初始化目标文件
		*/
		$this->name = $name;
		$this->read_user();
		$this->current_book = array_keys($this->books)[0];
		$this->prev_offset = $this->next_offset = $this->books[$this->current_book];
		$this->exe_function = array();
		$this->run_lock = 0;
	}
	
	public function get_next($book, $size)
	{
		if (!file_exists($book))
			return "文件不存在!";
		
		// 切换书籍
		if (strcmp($this->current_book, $book))
		{
			$books[$this->current_book] = $this->next_offset;
			$this->current_book = $book;
			$this->next_offset = $this->prev_offset = $this->books[$book];
		}

		$fp = fopen($book, "r");
		fseek($fp, $this->next_offset);
		$buffer = fread($fp, $size*4);	// 采用utf-8编码存储的文本文件
		// 采用mb_substr用于截取中文
		$ret = mb_substr($buffer, 0, $size, "utf-8");
	
		// 采用strlen用于计算中文所占字节数
		$this->next_offset = $this->next_offset + strlen($ret);
		$this->books[$this->current_book] = $this->next_offset;
		fclose($fp);
		
		return $ret;
	}

	public function get_prev($book, $size)
	{
		if (!file_exists($book))
			return "文件不存在!";
		
		// 切换书籍
		if (strcmp($this->current_book, $book))
		{
			$books[$this->current_book] = $this->next_offset;
			$this->current_book = $book;
			$this->next_offset = $this->prev_offset = $this->books[$book];
		}

		$fp = fopen($book, "r");
		
		$offset = 0;
		$buffer = "";

		if ($this->prev_offset-$size*4 < 0)
		{
			if ($this->prev_offset != 0)
				$buffer = fread($fp, $this->prev_offset);
		}
		else
		{
			fseek($fp, $this->prev_offset-$size*4);
			$buffer = fread($fp, $size*4);
		}		

		// 倒序提取字符
		$ret = mb_substr($buffer, -$size, $size, "utf-8");
		
		$this->prev_offset -= strlen($ret);
		$this->prev_offset = $this->prev_offset > 0 ? $this->prev_offset : 0;
		
		fclose($fp);
		return $ret;
	}
	
	public function save_offset($book, $offset)
	{
		if (!file_exists($book))
			return "文件不存在！";
		
		if (!array_key_exists($book, $this->books))
			return "没有这本书！";
		
		$this->books[$book] = $this->books[$book] - $offset;
	}

	public function save_user()
	{
		/*
		这里采用合并函数进行打包存储
		*/

		$fp = fopen($this->name, "w+");
		$key_array_string = array_to_string_key($this->books, "|");
		fwrite($fp, $key_array_string);

		fclose($fp);
		
	}

	public function read_user()
	{
		/*
		这里打开文件，并从文件中获取足够的数据
		*/
		if (!file_exists($this->name))
		{
			$this->books = array("新手指南"=>0);
			return 1;
		}

		$fp = fopen($this->name, "r");
		$buffer = fread($fp, 10240);

		$this->books = splite_key($buffer, "|");
		fclose($fp);

	}

	public function add_book($book)
	{
		if (!file_exists($book))
			return "文件不存在!";

		if (array_key_exists($book, $this->books))
			return "已存在！";

		$this->books[$book] = 0;

		return "添加成功！\n";
	}

	public function del_book($book)
	{
		if (!file_exists($book))
			return "文件不存在!";

		if (!array_key_exists($book, $this->books))
			return "书名不存在!";

		unset($this->books[$book]);

		return "删除成功！\n";
	}
	
	public function get_books()
	{
		/*
		本函数用于返回用户的所有书籍
		*/
		$temp = array_keys($this->books);
		return array_to_string($temp, "|");
	}

	private function ret_sock($sock, $function)
	{
		/*
		这里先用buffer变量测试函数，在实际应用中应该采用如下形式
		*/
		$buffer = $function;
		socket_write($sock, $buffer);
		socket_close($sock);
	}

	public function push_function($sock, $function)
	{
		/*
		很简单的函数，将待执行函数压栈
		*/
		echo "push_public function exe!\n";
		array_push($this->exe_function, $this->ret_sock($sock, $function));
	}

	public function run()
	{
		/*
		开始逐项执行数组中对应的函数，采用锁形式
		*/
		if ($this->run_lock)
			return -1;

		$this->run_lock = 1;

		while (count($this->exe_function))
		{
			echo $this->exe_function[0];
			array_shift($this->exe_function);
		}

		$this->run_lock = 0;
	}

	public function __destruct()
	{
		/*
		析构函数，目前不做任何事情
		*/
		$this->save_user();
	}
}

?>
