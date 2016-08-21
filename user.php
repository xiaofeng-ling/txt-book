<?php
require_once("sql.php");

class User
{
	private $name;
	private $prev_offset;
	private $next_offset;
	private $books;
	private $sql;

	public function __construct($name)
	{
		/*
		这里添加代码，用于根据name来取得保存的信息，如果目标文件不存在，则创建，并初始化目标文件
		*/
		global $sql_user, $sql_passwd;
		$this->sql = mysql_connect('localhost:3306', $sql_user, $sql_passwd);
		
		if (!$this->sql)
		{
			die('could not connect: '.mysql_error());
		}
		
		echo 'connect success!\n';
		
		$this->name = $name;
		
		
		
		
		$this->read_user();
		$this->current_book = array_keys($this->books)[0];
		$this->prev_offset = $this->books[0]['prev_offset'];
		$this->next_offset = $this->books[0]['next_offset'];
	}
	
	public function get_next($book, $size)
	{
		$path = '';
		if (!file_exists($path = get_book_path($book)));
			die('文件不存在!');
			
		if (!array_key_exists($book, $this->books))
			die('没有这本书！\n');
		
		
		$fp = fopen($book, "r");
		
		if (!$fp)
			die('打开文件失败！');
		
		
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
		$path = '';
		if (!file_exists($path = get_book_path($book)));
			die('文件不存在!');
			
		if (!array_key_exists($book, $this->books))
			die('没有这本书！\n');
		
		
		$fp = fopen($book, "r");
		
		if (!$fp)
			die('打开文件失败！');
		
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
		if (!file_exists(get_book_path($book)))
			return "文件不存在！";
		
		if (!array_key_exists($book, $this->books))
			return "没有这本书！";
		
		$this->books[$book]['next_offset'] = $this->books[$book]['next_offset'] - $offset;
	}

	public function save_books()
	{
		/*
		访问数据库，存储书籍
		*/
		
		if (!$this->sql)
			die('未连接！\n');
		
		$books = json_encode($this->books);
		
		if (!$books)
			die('编码失败！\n');
		
		$sql_query = "UPDATE txt_book_users SET books='$books' WHERE name='$this->name'";
		
		mysql_select_db('txt_book');
		
		$ret = mysql_query($sql_query, $this->sql);
		
		if (!$ret)
				die('更新数据库失败: '.mysql_error());
			
		mysql_free_result($ret);

		fclose($fp);
		
	}

	public function read_books()
	{
		/*
		访问数据库，提取书籍
		*/
		if (!$this->sql)
			die('未连接！\n');
		
		$sql_query = "SELECT books FROM txt_book_users WHERE name='$this->name'";
		
		mysql_select_db('txt_book');
		
		$ret = mysql_query($sql_query, $this->sql);
		
		if (!$ret)
			die('查询失败: '.mysql_error());
		
		$this->books = json_decode(mysql_result($ret, 0), true);
		
		if (!$this->books)
			die('获取书籍失败！\n');
		
		mysql_free_result($ret);
	}

	public function add_book($book)
	{
		if (!file_exists(get_book_path($book)))
			return "文件不存在!";

		if (array_key_exists($book, $this->books))
			return "已存在！";

		$this->books[$book]['next_offset'] = 0;
		$this->books[$book]['prev_offset'] = 0;

		return "添加成功！\n";
	}

	public function del_book($book)
	{
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
		return json_encode($temp);
	}
	
	public function get_book_path($book)
	{
		/*
		本函数用于查询书籍的路径
		*/
		
		$sql = mysql_connect('localhost:3306', $sql_user, $sql_passwd);
		
		if (!$sql)
			die('连接失败: '.mysql_error());
		
		mysql_select_db('txt_book');
		
		$ret = mysql_query("SELECT path FROM txt_book_books WHERE name='$book'");
		
		if (!ret)
			die('查询失败：\n'.mysql_error());
		
		mysql_close($sql);
		
		return mysql_result($ret, 0);
	}
	
	public function reset_prev_offset()
	{
		/*
		本函数重置上一页偏移量
		*/
		$this->prev_offset = $this->next_offset;
	}

	public function __destruct()
	{
		/*
		析构函数，目前不做任何事情
		*/
		$this->save_user();
		
		if ($this->sql)
			mysql_close($this->sql);
	}
}

?>
