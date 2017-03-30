<?php

require_once("base/user.class.php");
require_once("data.class.php");
require_once("error.class.php");
require_once("config.php");

class UserAdmin extends User
{
	private $data;
	
	public $error;
	public $permission = 2;
	
	public function __construct($name)
	{
		parent::__construct($name);
		
		$this->data = new Data(SQL_DATABASE, SQL_USERNAME, SQL_PASSWORD);
		$this->error = new Error();
		
		//$this->get_permission();
	}

	public function __destruct()
	{
	}
	
	public function upload($temp_name, $filename)
	{
		if (!file_exists($temp_name))
			return $this->error->error_handle(4, "文件不存在！");
		
		$encode_name = mysql_real_escape_string($this->data->encode($filename));
		$filepath = "upload/" . $encode_name;
		
		
		if (file_exists($this->data->encode($filepath)))
			return $this->error->error_handle(4, "文件已存在！");
		
		$result = $this->data->query("SELECT * FROM txt_book_books WHERE name = '$encode_name'");
		
		if (!$this->data->book_exist($encode_name) && $this->error->is_no_error($result))
			if (!$this->data->query("DELETE * FROM txt_book_books WHERE name = '$encode_name'", false))
				return $this->error->error_handle(4, "数据库中的书籍路径与文件路径不一致！");
		else
			return $this->data->error->get_last_error();
		
		if (!move_uploaded_file($temp_name, $filepath))
			return $this->error->error_handle(4, "上传文件失败！");
		
		/* 写入作者、分类、介绍等信息 */
		$class = "other";
		$author = "佚名";
		$introduction = "没有介绍";
		
		if (!empty($_POST["fileClass"]))
			$class = $_POST["fileClass"];
		
		if (!empty($_POST["author"]))
			$author = $_POST["author"];
		
		if (!empty($_POST["introduction"]))
			$introduction = $_POST["introduction"];
		
		$class = mysql_real_escape_string($this->data->encode($class));
		$author = mysql_real_escape_string($this->data->encode($author));
		$introduction = mysql_real_escape_string($this->data->encode($introduction));
		
		if (!$this->data->query("INSERT INTO txt_book_books".
						"(class, name, author, introduction, path, score) ".
						"VALUES ".
						"('$class', '$encode_name', '$author', '$introduction', '".
						$filepath . "', 0)", false))
				return $this->error->error_handle(4, "存储路径出错".mysql_error());
				
		return $this->error->no_error();
	}
	
	protected function get_permission()
	{
		$name = mysql_real_escape_string($this->name);
		
		$result = $this->data->query("SELECT permission FROM txt_book_users WHERE name='$name'");
		
		try
		{
			if (!$this->error->is_no_error($result))
				throw new Exception("初始化权限失败！");
			
			$this->permission = (int)$result;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}

}

?>
