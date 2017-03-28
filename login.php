<?php

require_once("./include/data.class.php");

session_start();

$result = "";

function login()
{
	if (!isset($_SESSION['user']))
	{
		if ($_SERVER["REQUEST_METHOD"] ==  "POST")
		{
			if (empty($_POST["name"]) || empty($_POST["password"]))
				return '用户名或者密码不能为空！';
		
			$data = new Data('txt_book', 'root', 'qazQ19965110.0.');
		
			$name = mysql_real_escape_string($_POST["name"]);
			$password = mysql_real_escape_string($_POST["password"]);
			
			$error = $data->login($name, $password);

			if (!$data->error->is_no_error($error))
				return '用户名或者密码错误！';

			$_SESSION['user'] = $name;

		header('location: index.html');
		exit();
		}
		else
			return '参数错误！';
	}
	else
	{
		header('location: index.html');
		exit();
	}
}

$result = login();

?>

<!doctype html>

<html>

<meta charset="UTF-8" />

<head>
	<title>登录页面</title>
</head>

<style></style>

<script>
	function checkForm() {

		var name = document.forms["login"]["name"].value,
	    		password = document.forms["login"]["password"].value;

		if (null == name || "" == name) {
			alert("用户名不能为空！");
			return false;
		}

		if (null == password || "" == password) {
			alert("密码不能为空!");
			return false;
		}

	}

	function register() {

		location.href = "register.php";
		
	}
</script>

<body>

	<form name="login" action="login.php" method="post" onsubmit="return checkForm()">

	用户名:<input type="text" name="name" /><br />
	密码:<input type="password" name="password" /><br />
	<input type="submit" value="登录" />

	<!-- 默认type为submit，设置为button来禁止点击时提交表单 -->
	<button type="button" onclick="register()">注册</button>
		<?php echo "<div>".$result."</div>" ?>
	</form>
</body>

</html>
