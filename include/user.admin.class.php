<?php

require_once("base/user.class.php");
require_once("data.class.php");
require_once("error.class.php");
require_once("config.php");

class UserAdmin extends User
{
	private $data;
	
	public $permission = 2;
	public $error;
	
	public function __construct($name)
	{
		parent::__construct($name, $this->permission);
		
		$this->data = new Data(SQL_DATABASE, SQL_USERNAME, SQL_PASSWORD);
		$this->error = new Error();
	}

	public function __destruct()
	{
	}
	
	public function upload($temp_name, $filename)
	{
		if (!file_exists($temp_name))
			return $this->error->error_handle(4, "文件不存在！");
		
		$encode_name = $this->data->encode($filename);
		$filepath = "upload/" . $encode_name;
		
		
		if (file_exists($this->encode($filepath)))
			return $this->error->error_handle(4, "文件已存在！");
		
		$result = $this->data->query("SELECT * FROM txt_book_books WHERE name = '$encodename'");
		
		if (!$this->data->book_exist($encode_name) && $this->error->is_node_error($result))
			if (!$this->data->query("DELETE * FROM txt_book_books WHERE name = '$encodename'", false))
				return $this->error->error_handle(4, "数据库中的书籍路径与文件路径不一致！");
		else
			return $this->data->error->get_last_error();
		
		if (!move_uploaded_file($temp_name, $filepath))
			return $this->error->error_handle(4, "上传文件失败！");
		
		if ($this->data->query("INSERT INTO txt_book_books".
						"(class, name, author, introduction, path, score) ".
						"VALUES ".
						"('test', '$encodename', 'test', 'test', '".
						$filepath . "', 0)", false))
				return $this->error->error_handle(4, "存储路径出错".mysql_error());
				
		return $this->error->no_error();
	}

}

?>
