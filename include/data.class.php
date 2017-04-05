<?php

/* 这是数据类，用于处理本地数据 */
/* 该类仅用于读取、写入本地数据 */
/* 以及数据库操作		*/

/* 错误代码
1 文件未找到
2 数据库未查询到
3 未找到对应的数据库
4 其他错误
*/
require_once("error.class.php");


class Data
{
	private $mysql_address = "";
	private $mysql_username = "";
	private $mysql_password = "";
	private $mysql_instance = NULL;
	private $nysql_db_name = "";
	public $error;

	public function __construct($db_name, $username, $password, $address="localhost", &$error = NULL)
	{
		$this->error = new Error();
		
		$this->mysql_db_name = $db_name;
		$this->mysql_username = $username;
		$this->mysql_password = $password;
		$this->mysql_address = $address;

		if (NULL != $error)
			$this->error = $error;

		/* 连接数据库 */
		try
		{
			if (!($this->mysql_instance = mysql_connect($this->mysql_address, $this->mysql_username, $this->mysql_password)))
				throw new Exception('数据库连接失败');

			if (!mysql_select_db($this->mysql_db_name))
				throw new Exception('数据表不存在');

			if (!mysql_query("SET NAMES 'UTF8'"))
				throw new Exception('mysql设置编码错误！');
		}
		catch(Exception $e)
		{
			echo "无法成功连接mysql!\n".$e->getMessage();
			exit();
		}

	}

	public function __destruct()
	{
		// nothing to do...
	}
	
	/*
	description:	执行查询语句
	params:		string $string, bool $array
	return:		失败返回错误消息，如果$array为真，返回查询结果，否则返回查询是否成功
	*/
	public function query($string, $array=TRUE)
	{
		/* 执行查询语句，返回结果 */
		/* 参数$array如果为真，返回查询结果，否则返回查询是否成功 */
		mysql_select_db($this->mysql_db_name);
		
		$result = mysql_query($string, $this->mysql_instance);
		
		if (FALSE == $array)
			return $result;
		
		if (!$result)
			return $this->error->error_handle(4, "查询失败！".mysql_error());
		
		if (!mysql_num_rows($result))
			return $this->error->error_handle(4, "没有查询结果！");
		
		return mysql_result($result, 0);
	}

	/*
	description:	打开书籍，取得数据
	params:		string $book, int $offset, int $size
	return:		错误返回错误消息，否则返回数据
	*/
	public function get_data($book, $offset, $size)
	{
		/* 取得文件数据 */
		$path = '';
		
		if (!$this->error->is_no_error($path = $this->get_book_path($book)))
			return $this->error->get_last_error();
		
		if (!file_exists($path))
			return $this->error->error_handle(1, '文件不存在!');
		
		$fp = fopen($path, "r");

		if (!$fp)
			return $this->error->error_handle(4, '打开文件失败！');

		/* 支持倒序读取 */
		
		if ($size < 0)
			$offset = $offset + $size < 0 ? 0 : $offset + $size;

		fseek($fp, $offset);

		$ret = fread($fp, abs($size));
	
		fclose($fp);
		
		return $ret;
	}

	/*
	description:	从数据库中查询书籍的物理路径
	params:		string $book
	return:		错误返回错误消息，否者返回实际路径
	*/
	public function get_book_path($book)
	{
		/*
		本函数用于查询书籍的本地路径
		*/
		
		mysql_select_db($this->mysql_db_name);
		
		/* 将书名进行编码, 因为本地存储的文件就是使用的对应的url编码 */
		$book = mysql_real_escape_string($this->encode($book));
		
		$result = $this->query("SELECT path FROM txt_book_books WHERE name='$book'");
		
		if (!$this->error->is_no_error($result))
			return $this->error->get_last_error();
		
		// 返回书籍的本地路径
		return getcwd()."/admin/".$result;
	}
	
	/*
	description:	登录函数
	params:		string $name, string $password
	return:		成功返回no_error，否则返回错误消息
	*/
	public function login($name, $password)
	{
		/*
		登录用函数
		*/
		// mysql查询中文需要在中文边上加上单引号
		$name = mysql_real_escape_string($name);
		$result = $this->query("SELECT passwd FROM txt_book_users WHERE name='$name'", $this->mysql_instance);

		if (!$this->error->is_no_error($result))
			return $this->error->get_last_error();

		if ((int)$result != (md5($name) & md5($password)))
			return $this->error->error_handle(4, "密码错误");

		return $this->error->no_error();
	}

	/*
	description:	注册函数，注册并初始化用户的相关信息
	params:		string $name, string $password
	return:		成功返回no_error,否则返回对应的错误消息
	*/
	public function register($name, $password)
	{
		/*
		注册用函数
		*/

		mysql_select_db($this->mysql_db_name);
		
		$name = mysql_real_escape_string($name);

		if (mysql_num_rows(mysql_query("SELECT name FROM txt_book_users WHERE name='$name'", $this->mysql_instance)))
			return $this->error->error_handle(4, "用户已存在");

		$md5_password = (md5($name) & md5($password));
		
		$book = json_encode(Array('book.txt'=>Array('next_offset'=>0, 'prev_offset'=>0)));
		
		if (!$this->query("INSERT INTO txt_book_users (name, passwd, books, permission) VALUES ('$name', '$md5_password', '$book', 1)", FALSE))
			return $this->error->get_last_error();

		return $this->error->no_error();
	}
	
	/*
	description:	获取数据库中特定分类的书籍，一次10条
	params:		string $class, int $pages
	return:		失败返回错误消息，否则返回已解码后的数组
	*/
	public function get_all_books($class, $pages)
	{
		/*
		获取数据库中特定分类的书籍，一次10条
		*/
		
		/* 页数是从1开始计算，但是limit起始是从0开始
		   这里选择的办法就是将页数转换为limit起始的0
		   假设$page是1，那么语句中就是limit 0, 10
		   假设$page是2，那么语句中就是limit 10, 20
		*/
		$limit_head = ($pages - 1) * 10;
		$limit_tail = $pages * 10;
		
		mysql_select_db($this->mysql_db_name);
		
		$class = $this->encode(mysql_real_escape_string($class));
		
		$result = mysql_query("SELECT * FROM txt_book_books WHERE class = '$class' LIMIT $limit_head,$limit_tail", $this->mysql_instance);
		
		if (!$result)
			return $this->error->error_handle(4, "查询失败！".mysql_error());
		
		if (!mysql_num_rows($result))
			return $this->error->error_handle(4, "没有查询结果！");
		
		$books = array();
		
		for ($i=0; is_array($temp = mysql_fetch_array($result, MYSQL_ASSOC)); $i++)
			$books[$i] = $this->decode_array($temp);
			
		return $books;	
	}
	
	/*
	description:	获取数据库中所有的分类
	params:		无参数
	return:		失败返回错误消息，否则返回已解码的数组
	*/
	public function get_all_class()
	{
		/* 获取数据库中书籍的所有分类 */
		
		mysql_select_db($this->mysql_db_name);
		
		$result = mysql_query("SELECT DISTINCT class from txt_book_books", $this->mysql_instance);
		
		if (!$result)
			return $this->error->error_handle(4, "查询失败！".mysql_error());
		
		if (!mysql_num_rows($result))
			return $this->error->error_handle(4, "没有查询结果！");
		
		$class = array();
		
		for ($i=0; is_array($temp = mysql_fetch_array($result, MYSQL_ASSOC)); $i++)
			$class[$i] = $this->decode_array($temp);
		
		return $class;
	}
	
	/*
	description:	统计当前数据库中该分类的书籍数量
	params:		string $class
	return:		失败返回错误消息，否则返回一个关联数组
	*/
	public function get_class_num($class)
	{
		/* 统计当前数据库中的该分类的书籍数目 */
		
		$class = mysql_real_escape_string($class);
		
		$result = $this->query("SELECT COUNT(*) from txt_book_books WHERE class='$class'");
		
		if (!$this->error->is_no_error($result))
			return $this->error->error_handle(4, "查询失败".mysql_error());
		
		return array('count'=>$result);
	}

	/*
	description:	编码函数
	params:		string $string
	return:		失败返回错误消息，否则返回编码后的数据
	*/
	public static function encode($string)
	{
		if (!is_string($string))
			return $this->error->error_handle(4, "不是string类型");

		return urlencode($string);
	}

	/*
	description:	解码函数
	params:		string $string
	return:		失败返回错误消息，否则返回解码后的数据
	*/
	public static function decode($string)
	{
		if (!is_string($string))
			return $this->error->error_handle(4, "不是string类型");

		return urldecode($string);
	}
	
	/*
	description:	编码数组,参数$key_name为true表示对键操作
	params:		array $array, book $key_name
	return:		失败返回错误消息，否则返回编码后的关联数组
	*/
	public function encode_array($array, $key_name=FALSE)
	{
		/* 参数$key为true表示对键操作 */
		
		if (!is_array($array))
			return $this->error->error_handle(4, "不是数组！");
		
		$temp = array();
		
		foreach($array as $key=>$value)
		{
			if (TRUE == $key_name)
				$temp[$this->encode($key)] = $array[$key];
			else
				$temp[$key] = $this->encode($value);
		}
				
		return $temp;
	}
	
	/*
	description:	解码数组,参数$key_name为true表示对键操作
	params:		array $array, book $key_name
	return:		失败返回错误消息，否则返回解码后的关联数组
	*/
	public function decode_array($array, $key_name=FALSE)
	{	
	/* 参数$key为true表示对键操作 */
	
		if (!is_array($array))
			return $this->error->error_handle(4, "不是数组！");
		
		$temp = array();
		
		foreach($array as $key=>$value)
		{
			if (TRUE == $key_name)
				$temp[$this->decode($key)] = $array[$key];
			else
				$temp[$key] = $this->decode($value);
		}
				
		return $temp;
	}

	/*
	description:	查询书籍的物理路径是否存在
	params:		string $book;
	return:		失败或不存在返回false，否则为true
	*/
	public function book_exist($book)
	{
		/* 传入进来的为未编码的名称 */
		$path = $this->get_book_path($book);
		
		if (!$this->error->is_no_error($this->error->get_last_error()))
			return false;
		
		return file_exists($path);
	}
}

?>
