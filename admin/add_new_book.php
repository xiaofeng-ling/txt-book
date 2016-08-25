<?php

require_once("../sql.php");

$ret = '';

function upload() 
{
	global $sql_user, $sql_passwd;
	
	if (!isset($_FILES["file"]))
		return "请上传txt文件！\n";
	
	if ($_FILES["file"]["error"] > 0)
		return "上传文件出错！\n";
	
	if ($_FILES["file"]["type"] != "text/plain")
		return "请上传txt文件！\n";
	
	$filename = $_FILES["file"]["name"];
	
	if (file_exists("../txt/" . $filename))
		return "文件已存在！\n";
	
	if ($_FILES["file"]["size"] > 10*1024*1024)
		return "文件太大，请上传10M以下的文件！\n";
	
	// mysql相关
	$sql = mysql_connect("localhost:3306", $sql_user, $sql_passwd);
	
	if (!$sql)
		return "上传文件失败！\n";
	
	mysql_select_db('txt_book', $sql);
	
	move_uploaded_file($_FILES["file"]["tmp_name"], "../txt/" . $filename);
	
	// 转换文件编码
	$fp = fopen("../txt/" . $filename, "r+");
	
	if (!$fp)
		return "上传文件出错！\n";
	
	$in_buffer = fread($fp, 10*1024*1024);
	$out_buffer = mb_convert_encoding($in_buffer, "UTF-8", "GB2312, GBK, UTF-8, UNICODE");
	
	// 移动文件指针到头部
	fseek($fp, 0);
	
	if (!fwrite($fp, $out_buffer))
		return "上传文件出错！\n";
	
	fclose($fp);
	
	$sql_query = "INSERT INTO txt_book_books ".
				 "(class, name, author, introduction, path, score) ".
				 "VALUES ".
				 "('temp', '$filename', 'temp', 'temp', " . "'./txt/". "$filename'". ", 0)";
				 // 此处采用./txt/是因为对应的运行文件并不在这里
				 
	if (!$ret = mysql_query($sql_query, $sql))
	{
		unlink("../txt/".$filename);
		return "上传失败！\n";
	}
	
	return "上传成功！\n";
}

$ret = upload();

?>

<!doctype html>
<meta charset="utf-8">
<html>
	<head>
		<title>增加书籍</title>
	</head>


	<body>
	
	请选择上传的书籍<br>
	<form action="add_new_book.php" method="post" enctype="multipart/form-data">
		<label for="file">文件名:</label>
		<input type="file" name="file" id="file" /><br>
		<input type="submit" name="submit" value="上载" />
	</form>
	
	<?php echo "<div>" . $ret . "</div>" ?>
	
	</body>

</html>