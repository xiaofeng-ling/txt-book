<?php

function splite($string, $symbol)
{
	// 分割字符串
	// 例: splite("你是好人|人人人|好好好|111", "|")
	$pos = 0;
	
	// 创建一个空数组
	$buffer = array();
	for ($i=0; $i<strlen($string); $i++)
	{
		if ($string[$i] == $symbol)
		{
			// 将每个分割符的文件加入(入栈)到数组中
			array_push($buffer, substr($string, $pos, $i-$pos));
			$pos = $i+1;
		}		
	}
	// 最后再执行一次入栈
	array_push($buffer, substr($string, $pos, $i-$pos));
	return $buffer;
}

function array_to_string($array, $symbol="")
{
	// 此函数用于将数组转换为字符串
	$ret = "";
	for ($i=0; $i<count($array); $i++)
	{
		// 连接字符串
		$ret = $ret.$array[$i].$symbol;
	}
	
	if ($symbol != "")
		// 移除字符串最右侧的多余符号
		$ret = substr($ret, 0, strlen($ret)-1);
	
	return $ret;
}

function array_to_string_key($array, $symbol="")
{
	// 此函数用于将关联数组转换为字符串，例如123,431,455,
	$ret = "";

	while (list($key, $value) = each($array))
	{
		$ret = $ret.$key.$symbol.$value.$symbol;
	}

	if ($symbol != "")
		// 移除字符串最右侧的多余符号
		$ret = substr($ret, 0, strlen($ret)-1);

	return $ret;
}

function splite_key($string, $symbol)
{
	// 将字符串分割到关联数组中，关联数组大小必须为2的倍数
	$ret = array();
	$key = "";
	$key_flag = 0;
	$pos = 0;

	for ($i=0; $i<strlen($string); $i++)
	{

		if ($string[$i] == $symbol)
		{
			if ($key_flag == 0)
			{
				$key = substr($string, $pos, $i-$pos);
				$pos = $i+1;
				$key_flag = 1;
			}
			else
			{
				$ret[$key] = substr($string, $pos, $i-$pos);
				$key = "";
				$key_flag = 0; 
				$pos = $i+1;
			}
		}
	}
	
	// 最后再将最后一个值放入最后一个key中
	$ret[$key] = substr($string, $pos, $i-$pos);

	return $ret;
}
?>
