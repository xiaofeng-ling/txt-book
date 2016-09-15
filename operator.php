<?php
/*
操作数		说明
1			获取下一段文字
2			获取上一段文字
3			保存进度
4			添加书籍
5			删除书籍
6			获取所有书籍
7			重设上一页偏移量
8			获取数据库中的所有书籍
*/

header("Content-type: text/html; charset='utf-8'");

ini_set("session.save_path", "/tmp");
session_start();

require_once("user.php");
require_once("tools.php");

if (!isset($_SESSION['user']))
{
	header("Localtion: login.php");
	die('用户未登陆!');
}

if (empty($_GET['operator']))
{
	die('操作无效');
}

if (empty($_GET['book']))
{
	die('没有书籍\n');
}

$user = new User($_SESSION['user']);
$book = $_GET['book'];

switch((int)$_GET['operator'])
{
	case 1:
	echo $user->get_next($book, 4096);
	break;
	
	case 2:
	echo $user->get_prev($book, 4096);
	break;
	
	case 3:
	if (empty($_GET['offset']))
		die('参数错误！\n');
	
	echo $user->save_offset($book, $_GET['offset']);
	break;
	
	case 4:
	echo $user->add_book($book);
	break;
	
	case 5:
	echo $user->del_book($book);
	break;
	
	case 6:
	echo $user->get_all_books();
	break;
	
	case 7:
	echo $user->reset_prev_offset($book);
	break;
	
	case 8:
	echo get_all_books_from_database();
	break;
	
	default:
	echo ('操作无效');
	break;
}