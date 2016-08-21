<?php
require_once("sql.php");

ini_set('session.save_path', '/tmp');

session_start();

$error = "";

function login()
{
	if (!isset($_SESSION['user']))
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			global $sql_user, $sql_passwd;
			
			if (empty($_POST["name"]) || empty($_POST["password"]))
				return '用户名或者密码错误！';
			
			$sql_connect = mysql_connect('localhost:3306', $sql_user, $sql_passwd);
			
			if (!$sql_connect)
				return '连接失败！: '.mysql_error();
			
			$name = mysql_real_escape_string($_POST["name"]);			
			$passwd = mysql_real_escape_string($_POST["password"]);
			
			mysql_select_db('txt_book');
			
			if (!mysql_query("SET NAMES 'UTF8'"))
				return "mysql设置编码错误！\n";
			
			// 在中文边上加上单引号，否则无法查询
			$sql_query = "SELECT passwd FROM txt_book_users WHERE name='$name'";
			
			$ret = mysql_query($sql_query, $sql_connect);
			
			if (!$ret)
				return '查询失败: '.mysql_error();
			
			if (!mysql_num_rows($ret))
				return '账号不存在';
			
			if ((int)mysql_result($ret, 0) != (md5($name) & md5($passwd)))
				return '账号或者密码错误！';
			
			$_SESSION['user'] = $name;
			header('localtion: loged.html');
		}
	}
	else
	{
		header('location: loged.html');
	}
}

$error = login();

?>

<!doctype html>
<html>
<meta charset="utf-8">
<head>
<title>请登录</title>
</head>

<style></style>

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

<form name="login" action="login.php" method="post" onsubmit="return checkForm()">
用户名:<input type="text" name="name"><br>
密码:<input type="password" name="password"><br>
<input type="submit" value="登陆">
<?php echo "<div>".$error."</div>" ?>
</form>

</body>

</html>