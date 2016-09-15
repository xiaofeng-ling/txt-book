<?php

set_time_limit(0);

include "thread.php";

$ip = "127.0.0.1";
$port = 9585;

/*
创建套接字并监听，遇上新的套接字后创建一个新的线程用于处理
*/

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_block($socket);
socket_bind($socket, $ip, $port);
socket_listen($socket, 100);

do {

	echo "开始监听!\n";
	$msgsock = socket_accept($socket);
	socket_exe($msgsock);

} while (true);

socket_close($socket);

?>
