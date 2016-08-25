<?php

ini_set("session.save_path", "/tmp");
session_start();

require_once("user.php");

if (!isset($_SESSION['user']))
{
	header("Localtion: login.php");
	die('用户未登陆!');
}

if (empty($_GET['book']))
	die('没有书籍\n');

$user = new User($_SESSION['user']);

echo $user->get_prev($_GET['book'], 4096);

?>