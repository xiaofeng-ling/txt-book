<?php

require_once("include/data.class.php");
require_once("include/config.php");

$result = "";

function register()
{
	if (isset($_SESSION["user"]))
	{
		header("localtion: index.php");
		exit();
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (empty($_POST["name"]) || empty($_POST["password"]))
			return '用户名或密码不能为空！\n';

		$data = new Data(SQL_DATABASE, SQL_USERNAME, SQL_PASSWORD);	
			
		$name = mysql_real_escape_string($_POST["name"]);
		$password = mysql_real_escape_string($_POST["password"]);

		$error = $data->register($name, $password);

		if (!$data->error->is_no_error($error))
			return $error['error_message'];

		return '注册成功！\n';
	}
	else
		return '非法参数！';
}

$result = register();

?>

<!doctype html>
<html>

<meta charset="utf-8" />

<head>
	<title>注册</title>
</head

<style></style>

<script>
	function checkForm() {

		var name = document.forms["register"]["name"].value,
	    		password = document.forms["register"]["password"].value;

		if (null == name || "" == name) {
			alert("用户名不能为空！");
			return false;
		}

		if (null == password || "" == password) {
			alert("密码不能为空!");
			return false;
		}

	}
</script>

<body>
	<form name="register" action="register.php" method="post" onsubmit="return checkForm()">
	用户名:<input type="text" name="name"><br />
	密码:<input type="password" name="password"><br />
	<input type="submit" value="注册" />

	<div><?php echo $result ?></div>
	</form>
</body>


</html>
