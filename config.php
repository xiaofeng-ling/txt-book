<?php

$error = "";

function register()
{
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (empty($_POST["address"]) || empty($_POST["name"]) || empty($_POST["password"]))
			return "输入不能为空！";
		
		$fp = fopen("sqlllll.php", "w");
		
		if (!$fp)
			return "写入文件失败！\n";
		
		$address = $_POST["address"];
		$user = $_POST["name"];
		$passwd = $_POST["password"];
		
		$sql = mysql_connect($address, $user, $passwd);
		
		if (!$sql)
			return "登录失败！".mysql_error();
		
		if (!mysql_query("SET NAMES 'UTF8'"))
			return "mysql设置编码错误！\n";
		
		if (!mysql_query("CREATE DATABASE txt_book1 DEFAULT CHARACTER SET utf8"))
			return "数据库创建失败！".mysql_error();
		
		mysql_select_db("txt_book1");
		
		if (!mysql_query("CREATE TABLE txt_book_users(id int(11) NOT NULL AUTO_INCREMENT,
						name varchar(255) NOT NULL,
						passwd text CHARACTER SET latin1 NOT NULL,
						books text CHARACTER SET latin1 NOT NULL,
						date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY (id),
						UNIQUE KEY name (name))
						DEFAULT CHARSET=utf8"))
			return "用户数据表创建失败！".mysql_error();
			
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
			return "书籍书籍表创建失败！".mysql_error();
		
		if (!fwrite($fp, "<?php define(\"SQL_ADDRESS\", \"$address\"); 
								define(\"SQL_USER\", \"$user\"); 
								define(\"SQL_ADDRESS\", \"$passwd\");?>"))
			return "写入文件失败！\n";
		
		fclose($fp);	
		
		return "成功！\n";
	}
}

$error = register();

?>

<!doctype html>
<meta charset="utf-8">
<html>
	<head>
	<title>配置</title>
	</head>
	
	<script>
		function checkForm() {
			var address = document.forms["config"]["address"].value;
			var name = document.forms["config"]["name"].value;
			var passwd = document.forms["config"]["password"].value;
			
			if (address == null || address == "") {
				alert("地址不能为空!");
				return false;
			}
				
			if (name == null || name == "") {
				alert("用户名不能为空！");
				return false;
			}
			
			if (passwd == null || passwd == "") {
				alert("密码不能为空！");
				return false;
			}
			
		}
	</script>
	
	<body>
		<form name="config" action="config.php" method="post" onsubmit="return checkForm()">
		
		sql地址：<input type="text" name="address"><br>
		sql用户名：<input type="text" name="name"><br>
		sql密码：<input type="password" name="password"><br>
		<input type="submit" value="提交">		
		</form>
		
	<?php echo "<div>".$error."</div>" ?>
	
	</body>
</html>