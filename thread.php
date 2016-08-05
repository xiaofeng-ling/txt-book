<?php

include "user.php";

/*
多线程模块，用于处理用户请求
*/

/*
0x01	创建新用户
0x02	获取下一页
0x04	获取上一页
0x08	增加书籍
0x16	删除书籍
0x32	退出
0x64	保存书籍进度


*/

$global_users = array();

function socket_exe($sock)
{
	global $global_users;

	$buffer = socket_read($sock, 1024);
	echo "socket_exe".$buffer."\n";
	$buffer_array = splite($buffer, "|");

	switch ((int)$buffer_array[0])
	{
		case 1:
			if (array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户已存在，请勿重复登录!\n");
				return -1;
			}
			
			$global_users[$buffer_array[1]] = new User($buffer_array[1]);
			socket_write($sock, "登录成功!");
			
			break;

		case 2:
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，请登录!\n");
				return -2;
			}

			$global_users[$buffer_array[1]]->push_function($sock, $global_users[$buffer_array[1]]->get_next($buffer_array[2], 2048));
			$global_users[$buffer_array[1]]->run();

			break;

		case 4:	
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，请登录!\n");
				return -4;
			}

			$global_users[$buffer_array[1]]->push_function($sock, $global_users[$buffer_array[1]]->get_prev($buffer_array[2], 2048));
			$global_users[$buffer_array[1]]->run();

			break;

		case 8:
			
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，请登录!\n");
				return -8;
			}

			$global_users[$buffer_array[1]]->push_function($sock, $global_users[$buffer_array[1]]->add_book($buffer_array[2]));
			$global_users[$buffer_array[1]]->run();

			break;

		case 16:
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，请登录!\n");
				return -16;
			}

			$global_users[$buffer_array[1]]->push_function($sock, $global_users[$buffer_array[1]]->del_book($buffer_array[2]));
			$global_users[$buffer_array[1]]->run();

			break;

		case 32:
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，无需退出！\n");
				return -32;
			}

			unset($global_users[$buffer_array[1]]);

			socket_write($sock, "注销成功！\n");

			break;
			
		case 64:
			if (!array_key_exists($buffer_array[1], $global_users))
			{
				socket_write($sock, "用户不存在，请登录!\n");
				return -16;
			}
			
			$global_users[$buffer_array[1]]->push_function($sock, $global_users[$buffer_array[1]]->save_offset($buffer_array[2], $buffer_array[3]));
			$global_users[$buffer_array[1]]->run();

		default:
			break;
	}
}
?>
