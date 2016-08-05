<?php

ini_set('session.save_path', '/tmp');

session_start();

$ip = "127.0.0.1";
$port = 9585;

$error_msg = "";

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if (!isset($_SESSION['user']))
{
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (empty($_POST["name"]) || empty($_POST["password"]))
		{
			echo "用户名或者密码错误！";
			exit();
		}
		$_SESSION['user'] = trim($_POST['name']);//mysqli_real_escape_string($dbc,trim($_POST['name']));
		
		//$name = mysqli_real_escape_string($dbc,trim($_POST['name']));
		//$passwd = mysqli_real_escape_string($dbc,trim($_POST['name']));
		
		$name = trim($_POST['name']);
		$passwd = trim($_POST['password']);

		//$checksum = sha256($name) & sha256($passwd);
		$checksum = $name.$passwd;
		
		/*
		进行mysql查询
		*/
		
		socket_connect($socket, $ip, $port);
		socket_write($socket, "1"."|".$_SESSION['user']);
		
		$ret = "";
		
		$ret = socket_read($socket, 1024);
		
		// 这里判断返回值是不是成功，如果不成功则删除session中的对象，否则跳转页面
	}
}
else
{
	header('location: loged.html');
}

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

<form name="login" action="<?php $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return checkForm()">
用户名:<input type="text" name="name"><br>
密码:<input type="password" name="password"><br>
<input type="submit" value="登陆">
</form>

</body>

</html>
