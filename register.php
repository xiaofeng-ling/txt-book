<?php
require_once("sql.php");

$error = "";

function register()
{
	if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			if (empty($_POST["name"]) || empty($_POST["password"]))
				return '用户名或者密码不符合规则！\n';
			
			$sql = mysql_connect(SQL_ADDRESS, SQL_USERS, SQL_PASSWD);
			
			if (!$sql)
			{
				return "could not connect: ".mysql_error();
			}
			
			mysql_select_db('txt_book');
			
			$name = mysql_real_escape_string($_POST["name"]);
			
			if (!mysql_query("SET NAMES 'UTF8'"))
				return "mysql设置编码错误！\n";
			
			// 在中文边上加上单引号，否则无法查询
			if ($unique_name = mysql_query("SELECT name FROM txt_book_users WHERE name='$name'"))
				if (mysql_num_rows($unique_name))
					return "用户已存在！\n";
			
			$temp_passwd = mysql_real_escape_string($_POST["password"]);
			$passwd = (md5($name) & md5($temp_passwd));
			
			// 使用urlencode预编码中文，防止json_decode解码出中文无效
			$temp_book = array(urlencode("新手指南")=>array
								("prev_offset"=>0, 
								 "next_offset"=>0));
			$books = json_encode($temp_book, true);
			
			if (!$books)
				return '编码失败！\n';
			
			$sql_query = "INSERT INTO txt_book_users (name, passwd, books) VALUES
													 ('$name', '$passwd', '$books')";
			
			$ret = mysql_query($sql_query, $sql);
			
			if (!$ret)
				return '注册失败！\n'.mysql_error();
			
			return "注册成功！\n";
		}
}

$error = register();
?>

<!doctype html>
<html>
<meta charset="utf-8">
<head>
<title>注册</title>
</head>

<script>
function checkForm()
{
	var name = document.forms["login"]["name"].value;
	var passwd = document.forms["login"]["password"].value;
	
	if (name==null || name=="")
	{
		alert("用户名不能为空！");
		return false;
	}
	
	if (passwd==null || passwd=="")
	{
		alert("密码不能为空！");
		return false;
	}
}
</script>

<body>

<form name="login" action="register.php" method="post" onsubmit="return checkForm()">
用户名:<input type="text" name="name"><br>
密码:<input type="password" name="password"><br>
<input type="submit" value="注册">
<?php echo "<div>".$error."</div>" ?>
</form>

</body>

</html>