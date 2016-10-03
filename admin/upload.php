<?php

require_once("../sql.php");
require_once("./uploadFile.php");

$ret = '';
if (($ret = uploadFile()) != "合并完成")
	echo $ret;
else
{
	/* 移动文件到txt目录 */
	$filename = "../txt/".urlencode($_SESSION['filename']);
	
	if (!rename(PATH.$_SESSION['filename'], $filename))
		die("移动文件失败");
	
	/* 连接数据库 */
	$sql = mysql_connect(SQL_ADDRESS, SQL_USERS, SQL_PASSWD);
	
	if (!$sql)
		return "打开数据库失败";
	
	mysql_select_db('txt_book', $sql);
	
	/* 转换文件编码 */
	$fp = fopen("../txt/" . $filename, "r+");

	if (!$fp)
		return "上传文件出错！\n";
	
	$in_buffer = fread($fp, get_file_size($fp));
	$out_buffer = mb_convert_encoding($in_buffer, "UTF-8", "GB2312, GBK, UTF-8, UNICODE");
	
	/* 移动文件指针到头部 */
	fseek($fp, 0);
	
	if (!fwrite($fp, $out_buffer))
		return "上传文件出错！\n";
	
	fclose($fp);
	
	$sql_query = "INSERT INTO txt_book_books ".
				 "(class, name, author, introduction, path, score) ".
				 "VALUES ".
				 "('temp', '$filename', 'temp', 'temp', " . "'./txt/". "$filename'". ", 0)";
				 /* 此处采用./txt/是因为对应的运行文件并不在这里 */
				 
	if (!$ret = mysql_query($sql_query, $sql))
	{
		/* 不成功则直接删除文件 */
		unlink("../txt/".$filename);
		return "上传失败！\n";
	}
	
	echo "合并完成";
}

?>
