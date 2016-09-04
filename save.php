<?php
header("Content-type: text/html; charset='utf-8'");

ini_set("session.save_path", "/tmp");

session_start();

require_once("user.php");

if (!isset($_SESSION['user']))
{
	die("未登陆！\n");
}

if (empty($_GET['book']) || empty($_GET['offset']))
{
	die("参数错误!\n");
}

$user = new User($_SESSION['user']);

echo $user->save_offset($_GET['book'], $_GET['offset']);

?>