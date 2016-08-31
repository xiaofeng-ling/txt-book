<?php
header("Content-type: text/html; charset='utf-8'");

require_once("user.php");

ini_set('session.save_path', '/tmp');

session_start();

if (!isset($_SESSION['user']))
{
	header("Localtion: login.php");
	die('用户未登陆');
}

$user = new User($_SESSION['user']);

echo $user->get_all_books();
?>