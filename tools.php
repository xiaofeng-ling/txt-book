<?php

require_once('sql.php');

// 这是返回书籍数据库中的所有书籍
function get_all_books()
{
	$sql = mysql_connect(SQL_ADDRESS, SQL_USERS, SQL_PASSWD);

	if (!$sql)
		return "connect mysql error!\n";

	mysql_select_db("txt_book");

	$result = mysql_query("SELECT name FROM txt_book_books");

	if (!$result)
		return "query error: ".mysql_error();

	$array_temp = "";
	$array_ret = array();

	for ($i=0; $i<mysql_num_rows($result); $i++)
	{

		if($array_temp = mysql_result($result, $i))
			$array_ret[$i] = $array_temp;
	}

	return json_encode($array_ret);
}

echo get_all_books();
	
?>
