<?php
require_once("sql.php");

class User
{
	private $name = "";
	private $books = array();
	private $sql;

	public function __construct($name)
	{
		/*
		这里添加代码，用于根据name来取得保存的信息，如果目标文件不存在，则创建，并初始化目标文件
		*/
		$this->sql = mysql_connect(SQL_ADDRESS, SQL_USERS, SQL_PASSWD);
		
		if (!$this->sql)
		{
			die('could not connect: '.mysql_error());
		}
		
		$this->name = $name;
		
		$this->read_books();
	}
	
	public function get_next($book, $size)
	{
		$path = '';
		if (!file_exists($path = $this->get_book_path($book)))
			die('文件不存在!');
			
		if (!array_key_exists($book, $this->books))
			die('没有这本书！');
		
		
		$fp = fopen($path, "r");
		
		if (!$fp)
			die('打开文件失败！');
		
		$next_offset = $this->books[$book]['next_offset'];		
		
		fseek($fp, $next_offset);
		$buffer = fread($fp, $size*4);	// 采用utf-8编码存储的文本文件
		// 采用mb_substr用于截取中文
		$ret = mb_substr($buffer, 0, $size, "utf-8");
	
		// 采用strlen用于计算中文所占字节数
		$next_offset = $next_offset + strlen($ret);
		$this->books[$book]['next_offset'] = $next_offset;
		fclose($fp);
		
		return $ret;
	}

	public function get_prev($book, $size)
	{
		$path = '';
		if (!file_exists($path = $this->get_book_path($book)))
			die('文件不存在!');
			
		if (!array_key_exists($book, $this->books))
			die('没有这本书！\n');
		
		
		$fp = fopen($path, "r");
		
		if (!$fp)
			die('打开文件失败！');
		
		$buffer = "";
		$prev_offset = $this->books[$book]['prev_offset'];

		if ($prev_offset-$size*4 < 0)
		{
			if ($prev_offset != 0)
				$buffer = fread($fp, $prev_offset);
		}
		else
		{
			fseek($fp, $prev_offset-$size*4);
			$buffer = fread($fp, $size*4);
		}		

		// 倒序提取字符
		$ret = mb_substr($buffer, $size*-1, $size, "utf-8");

		$prev_offset -= strlen($ret);
		$prev_offset = $prev_offset > 0 ? $prev_offset : 0;
		$this->books[$book]['prev_offset'] = $prev_offset;
		fclose($fp);
		return $ret;
	}
	
	public function save_offset($book, $offset)
	{
		$path = "";
		$buffer = "";
		
		if (!file_exists($path = $this->get_book_path($book)))
			return "文件不存在！";
		
		if (!array_key_exists($book, $this->books))
			return "没有这本书！";
		
		if ($this->books[$book]['next_offset'] == $offset)
			return "保存成功！";
		
		$fp = fopen($path, "r");
		
		if (!$fp)
			return('打开文件失败！');
		
		fseek($fp, $this->books[$book]['next_offset'] - $offset);
		
		$buffer = fread($fp, $offset);

		// 防止出现乱码,根据UTF-8规则判断判断
		$buffer = mb_substr($buffer, $offset*-1, $offset, 'utf-8');

		$offset = strlen($buffer);

		$next_offset = $this->books[$book]['next_offset'] - $offset;
		
		$this->books[$book]['next_offset'] = $next_offset > 0 ? $next_offset : 0;
		
		return "保存成功！";
	}

	public function save_books()
	{
		/*
		访问数据库，存储书籍
		*/
		
		if (!$this->sql)
			die('未连接！\n');
		
		$new_books = array();
		
		foreach($this->books as $key=>$value)
		{
			$new_books[urlencode($key)] = $value;
		}
		
		$books = json_encode($new_books);
		
		if (!$books)
			die('编码失败！\n');
		
		$sql_query = "UPDATE txt_book_users SET books='$books' WHERE name='$this->name'";
		
		mysql_select_db('txt_book');
		
		$ret = mysql_query($sql_query, $this->sql);
		
		if (!$ret)
				die("更新数据库失败: ".mysql_error());		
	}

	public function read_books()
	{
		/*
		访问数据库，提取用户的所有书籍
		*/
		if (!$this->sql)
			die('未连接！\n');
		
		$sql_query = "SELECT books FROM txt_book_users WHERE name='$this->name'";
		
		mysql_select_db('txt_book');
		
		$ret = mysql_query($sql_query, $this->sql);
		
		if (!$ret || !mysql_num_rows($ret))
			die('查询失败: '.mysql_error());
		
		$this->books = json_decode(mysql_result($ret, 0), true);
		
		$new_books = array();
		
		foreach($this->books as $key=>$value)
		{
			$new_books[urldecode($key)] = $value;
		}
		
		unset($this->books);
		$this->books = $new_books;
		
		if (!$this->books)
			die('获取书籍失败！\n');
	}

	public function add_book($book)
	{
		if (!file_exists($this->get_book_path($book)))
			return "文件不存在!";

		if (array_key_exists($book, $this->books))
			return "已存在！";
		
		$this->books[$book] = Array("next_offset"=>0, "prev_offset"=>0);

		return "添加书籍《".$book."》成功！\n";
	}

	public function del_book($book)
	{
		if (!array_key_exists($book, $this->books))
			return "书名不存在!";

		unset($this->books[$book]);

		return "删除成功！\n";
	}
	
	public function get_all_books()
	{
		/*
		本函数用于返回用户的所有书籍,返回已使用urlencode编码后的数据，使用js解码
		*/
		$temp = array_keys($this->books);
		$all_book = array();
		for($i=0; $i<count($temp); $i++)
		{
			array_push($all_book, urlencode($temp[$i]));
		}
		return json_encode($all_book);
	}
	
	public function reset_prev_offset($book)
	{
		/*
		本函数重置上一页偏移量
		*/
		if (!array_key_exists($book, $this->books))
			return "书名不存在!";
		
		$this->books[$book]['prev_offset'] = $this->books[$book]['next_offset'];
		return "重置成功！";
	}

	public function __destruct()
	{
		/*
		析构函数
		*/
		$this->save_books();
		
		if ($this->sql)
			mysql_close($this->sql);
	}
	
	/* private function() */
	private function get_book_path($book)
	{
		/*
		本函数用于查询书籍的路径
		*/
		if (!$this->sql)
			die('未连接');
		
		mysql_select_db('txt_book');
		
		$book = urlencode($book);
		
		$ret = mysql_query("SELECT path FROM txt_book_books WHERE name='$book'");
		
		if (!$ret)
			die("查询失败：".mysql_error());
		
		if (!mysql_num_rows($ret))
			die("书籍不存在！\n");
		
		// 返回实际路径
		return mysql_result($ret, 0);
	}
}

?>
