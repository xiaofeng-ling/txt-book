<?php
require_once("base/user.class.php");
require_once("data.class.php");
require_once("error.class.php");
require_once("config.php");

class UserCommon extends User
{
	private $books;
	private $data;
	private $error;

	/*
	description:	构造函数
	params:		string $name(用户名)
	return:		无返回值
	*/
	public function __construct($name)
	{
		parent::__construct($name, 1);

		$this->error = new Error();
				
		$this->data = new Data(SQL_DATABASE, SQL_USERNAME, SQL_PASSWORD, SQL_ADDRESS, $this->error);

		$this->read_books($name);
	}

	/*
	description:	获取下一段文本
	params:		string $book(书名), int $size(字数)
	return:		string 不会乱码的中文
	*/
	
	public function get_next($book, $size)
	{
		if (!array_key_exists($book, $this->books))
			return $this->error->error_handle(4, '没有这本书！');
		
		$buffer = $this->data->get_data($book, $this->books[$book]['next_offset'], $size*4);	// 采用utf-8编码存储的文本文件
		
		// 采用mb_substr用于截取中文
		$ret = mb_substr($buffer, 0, $size, "utf-8");
	
		// 采用strlen用于计算中文所占字节数
		$this->books[$book]['next_offset'] += strlen($ret);
		
		return array('error_code'=>-1, 'data'=>$ret);
	}

	/*
	description:	获取上一段文本
	params:		string $book(书名), int $size(字数)
	return:		string 不会乱码的中文
	*/

	public function get_prev($book, $size)
	{
		if (!array_key_exists($book, $this->books))
			return $this->error->error_handle(4, '没有这本书！\n');
		
		$prev_offset = $this->books[$book]['prev_offset'];
		
		$buffer = "";

		if ($prev_offset - $size*4 < 0)
		{
			if ($prev_offset != 0)
				$buffer = $this->data->get_data($book, 0, $prev_offset * -1);
		}
		else
		{
			/* 倒序读取 */
			$buffer = $this->data->get_data($book, $prev_offset, $size * -4);
		}	

		// 倒序提取字符
		$ret = mb_substr($buffer, $size*-1, $size, "utf-8");

		$prev_offset -= strlen($ret);
		$prev_offset = $prev_offset > 0 ? $prev_offset : 0;
		$this->books[$book]['prev_offset'] = $prev_offset;

		return array('error_code'=>-1, 'data'=>$ret);
	}
	
	/*
	description:	保存用户正确的页面偏移量
	params:		string $book(书名), int $offset(相较于next_offset的偏移量)
	return:		成功返回no_error(), 不成功返回相应的错误
	*/

	public function save_offset($book, $offset)
	{
		/* 这个函数将next_offset设置为保存时的页面偏移量
		   参数offset是相较于next_offset的偏移量,因此,使用
		  next_offset - offset将能够正确设置用户浏览时的页面位置 */
		$buffer = "";
		
		if (!array_key_exists($book, $this->books))
			return $this->error->error_handle(4, "没有这本书！");
		
		if ($this->books[$book]['next_offset'] == $offset)
			return 0;
		
		$buffer = $this->data->get_data($book, $this->books[$book]['next_offset'] - $offset, $offset);

		// 防止出现乱码,根据UTF-8规则判断准确的中文字节数
		$buffer = mb_substr($buffer, $offset/3*-1, $offset, 'utf-8');

		// 此时的offset就已经是正确的中文字节数,不会出现乱码
		$offset = strlen($buffer);

		$next_offset = $this->books[$book]['next_offset'] - $offset;
		
		$this->books[$book]['next_offset'] = $next_offset > 0 ? $next_offset : 0;
		
		return $this->error->no_error();
	}

	/*
	description:	保存所有的书籍名
	params:		无参数	
	return:		成功返回no_error(), 不成功返回相应的错误
	*/

	private function save_books()
	{
		/*
		访问数据库，存储书籍
		*/
		
		$new_books = $this->data->encode_array($this->books, true);
		
		$books = json_encode($new_books);
		
		if (!$books)
			return $this->error->error_handle(4, '编码失败');
		
		$result = $this->data->query("UPDATE txt_book_users SET books='$books' WHERE name='$this->name'", FALSE);
		
		if (!$this->error->is_no_error($result))
			return $this->error->error_handle(4, "保存书籍失败！");
		
		return $this->error->no_error();
	}

	/*
	description:	增加书籍
	params:		string $book(书名)
	return:		成功返回no_error(), 不成功返回相应的错误
	*/

	public function add_book($book)
	{
		if (!$this->data->book_exist($book))
			return $this->error->error_handle(1, "文件不存在!");

		if (array_key_exists($book, $this->books))
			return $this->error->error_handle(4, "书籍已存在");
		
		$this->books[$book] = Array("next_offset"=>0, "prev_offset"=>0);

		return $this->error->no_error(); 
	}

	/*
	description:	删除书籍
	params:		string $book(书名)
	return:		成功返回no_error(), 不成功返回相应的错误
	*/

	public function del_book($book)
	{
		if (!array_key_exists($book, $this->books))
			return $this->error->error_handle(4, "你没有这本书");

		unset($this->books[$book]);

		return $this->error->no_error();
	}
	
	/*
	description:	获取所有的书籍,不是从数据库中获取
	params:		无参数	
	return:		成功返回json格式的书籍名
	*/

	public function get_all_books()
	{		
		return array_keys($this->data->decode_array($this->books, TRUE));
	}
	
	/*
	description:	重置上一段的页面偏移量
	params:		string $book(书名)
	return:		成功返回no_error(), 不成功返回相应的错误
	*/

	public function reset_prev_offset($book)
	{
		if (!array_key_exists($book, $this->books))
			return $this->error->error_handle(4, "书名不存在!");
		
		$this->books[$book]['prev_offset'] = $this->books[$book]['next_offset'];
		return $this->error->no_error();
	}

	/*
	description:	析构函数
	params:		无参数
	return:		无返回值
	*/

	public function __destruct()
	{
		/*
		析构函数
		*/
		$this->save_books($this->name, $this->books);
	}
	
	/*
	description:	读取用户的所有书籍
	params:		无参数
	return:		成功返回no_error(), 不成功返回相应的错误
	*/
	
	public function read_books()
	{
		$result = $this->data->query("SELECT books FROM txt_book_users WHERE name='$this->name'");
		
		if (!$this->error->is_no_error($result))
			return $this->error->error_handle(4, "读取书籍失败！");
		
		$books = json_decode($result, true);
		
		$new_books = $this->data->decode_array($books, true);
		
		$this->books = $new_books;
		
		return $this->error->no_error();
	}
}

?>
