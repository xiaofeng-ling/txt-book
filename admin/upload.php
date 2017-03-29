<?php

session_start();

require_once("../include/user.admin.class.php");

function upload()
{
	if (!isset($_SESSION['user']))
	{
		header("location: ../login.php");
		exit();
	}

	$user = new UserAdmin($_SESSION['user']);

	if (!($user->permission & 2))
	{
		echo "没有权限访问这个页面！";
		exit();
	}

	if (!isset($_FILES["file"]))
	{
		return "请上传文件";
	}

	if ($_FILES["file"]["error"] > 0)
	{
		return "上传文件失败";
	}

	if ($_FILES["file"]["type"] != "text/plain")
	{
		return "格式不正确";
	}
	
	$result = $user->upload($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
	
	return $result['error_message'];
}

$error = upload();

// 以下为html代码

?>

<!doctype html>
<meta charset="utf-8">

<html>

<head>
	<title>上传文件</title>
</head>

</script>

<body>

<form id="upload" action="upload.php" method="post" enctype="multipart/form-data">
	<input id="fileuplaod" name="file" type="file" accept="text/plain" />
	<input id="submit" type="submit" value="上传" />
	<?php echo "<div>".$error."</div>"; ?>
</form>

</body>

</html>