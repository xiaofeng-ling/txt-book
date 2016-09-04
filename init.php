<?php
header("Content-type: text/html; charset='utf-8'");

ini_set("session.save_path", "/tmp");

require_once("user.php");

session_start();

if (!isset($_SESSION['user']))
{
	die("未登陆！\n");
}

if (empty($_GET['book']))
{
	die("参数错误!\n");
}

$user = new User($_SESSION['user']);

echo $user->reset_prev_offset($_GET['book']);
?>