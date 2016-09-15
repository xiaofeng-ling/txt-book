<?php
/* 2016/7/29
** 筱枫
** 用于测试函数指针以及多线程类的文件
*/


$function = array();
$run_lock = 0;	// 为0表示未运行，为1表示运行中

class CTest
{
	var $string;
	
	function __construct($string)
	{
		$this->string = $string;
	}
	
	function say()
	{
		echo $this->string;
	}
}

function test($string)
{
	echo $string;
}

function test2($a, $b)
{
	echo $a*$b,"<br>";
}

function run()
{
	global $function;
	global $run_status;
	
	if ($run_status)
		return -1;
	
	// $run_lock 全局变量用于加锁，解锁
	$run_lock = 1;
	
	while (count($function))
	{
		$function[0];
		array_shift($function);
	}
	
	$run_lock = 0;
	
	return 0;
}

array_push($function, test("你好，世界！<br>"));
array_push($function, test("你好，中国！<br>"));
array_push($function, test2(1000, 2));
array_push($function, test("你好，北京！<br>"));
array_push($function, test("你好，上海！<br>"));
run();

?>