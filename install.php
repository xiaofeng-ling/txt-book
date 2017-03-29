<?php

require_once("include/data.class.php");

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if (empty($_POST["database"]) || empty($_POST["password"]) ||
		empty($_POST["username"]) || empty($_POST["address"]))
			error("不能为空！");
		
	$fp = fopen("include/config.php", "w");
	
	if (!$fp)
		error("写入文件失败，请确认是否有访问权限！");
	
	$sql = mysql_connect($_POST["address"], $_POST["username"], $_POST["password"]);
	
	if (!$sql)
		error("连接mysql数据库失败！" . mysql_error());
	
	if (!mysql_query("SET NAMES 'UTF8'") || !mysql_query("use " . $_POST["database"]))
		error(mysql_error());
	
	/* 创建用户数据表 */
	if (!mysql_query("CREATE TABLE txt_book_users(id int(11) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						passwd text CHARACTER SET latin1 NOT NULL,
						books text CHARACTER SET latin1 NOT NULL,
						permission tinyint NOT NULL,
						date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY (id),
						UNIQUE KEY name (name))
						DEFAULT CHARSET=utf8"))
		error(mysql_error());
	
	/* 创建书籍数据表 */
	if (!mysql_query("CREATE TABLE txt_book_books(
						  id int(11) NOT NULL AUTO_INCREMENT,
						  class text NOT NULL,
						  name varchar(255) NOT NULL,
						  author text NOT NULL,
						  introduction text NOT NULL,
						  path text NOT NULL,
						  score int(11) NOT NULL,
						  date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (id),
						  UNIQUE KEY name (name))
					      DEFAULT CHARSET=utf8"))
		error(mysql_error());
						
	if (!fwrite($fp, "<?php define(\"SQL_ADDRESS\", \"".$_POST["address"]."\");
							define(\"SQL_USERNAME\", \"".$_POST["username"]."\");
							define(\"SQL_PASSWORD\", \"".$_POST["password"]."\");
							define(\"SQL_DATABASE\", \"".$_POST["database"]."\"); ?>"))
		error("写入文件失败！");
		
	fclose($fp);
	
	header("location: welcome.php");
	exit();	
}

function error($msg)
{
	echo $msg;
	exit();
}

?>

<!doctype html>
<html>
<meta charset="utf-8" />

<head>
	<title>安装</title>
</head>

<script>
	function checkForm() {

		var database = document.forms["install"]["database"].value,
	    		password = document.forms["install"]["password"].value,
				address = document.forms["install"]["address"].value,
				username = document.forms["install"]["username"].value;

		if (null == database || "" == database) {
			alert("数据库名不能为空！");
			return false;
		}

		if (null == password || "" == password) {
			alert("密码不能为空!");
			return false;
		}
		
		if (null == address || "" == address) {
			alert("地址不能为空!");
			return false;
		}
		
		if (null == username || "" == username) {
			alert("用户名不能为空!");
			return false;
		}

	}
</script>

<body>

	<form name="install" action="install.php" method="post" onsubmit="return checkForm()">
	
	<div>请确认所填写用户名拥有操作数据库的权限</div>
	
	数据库名：<input type="text" name="database" /> <br />
	数据库地址：<input type="text" name="address" value="localhost" /> <br />
	数据库用户名：<input type="text" name="username" /> <br />
	数据库密码：<input type="password" name="password" /> <br />
	<input type="submit" value="提交" />
	
	</form>

</body>

</html>