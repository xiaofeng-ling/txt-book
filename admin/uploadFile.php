<?php

ini_set('session.save_path', '/tmp');

session_start();

function uploadFile() 
{
	/*	在nginx上需要手工写一个getallheaders()函数	*/
	$header = _my_get_all_headers();
	$config = get_json_array('config.json');

	define('PATH', $config['path']);

	if (!file_exists(PATH))
		mkdir(PATH);

	if (empty($_GET['filename']) || empty($_GET['slices']))
	{
		if ($_SESSION['filename'] == urldecode($header['FILENAME']))
		{
			/*	从post原始数据中读取信息	*/
			$fp_input = fopen("php://input", 'rb');
			$data = '';
			$i = 0;
			
			if ($header['SLICESIZE'] < 8192)
				$data = fread($fp_input, $header['SLICESIZE']);
			else
			{
				/*	对于网络流, fread每次最多读取8192	*/
				for (; $i<$header['SLICESIZE']/8192; $i++)
				{
					$temp_data = fread($fp_input, 8192);
					$data .= $temp_data;
				}
			
				/*	数据有多余的话再读取剩余不足8192的部分	*/
				if ($header['SLICESIZE']%8192)
				{
					$temp_data = fread($fp_input, $header['SLICESIZE'] - $i*8192);
					$data .= $temp_data;
				}
			}
			
			/*
			此处可以采用md5算法验证数据准确性
			if (md5($data) != $header['CHECKSUM'])
				exit('数据校验失败');
			*/
			
			if (file_exists(PATH.$header['FILENAME'].".".$header['SLICEINDEX']))
				return "分片文件已存在";
			
			/*	临时文件的格式为 文件名.01、文件名.02 ...	*/
			$fp_write = fopen(PATH.urldecode($header['FILENAME']).".".$header['SLICEINDEX'], "wb");
			
			if (!$fp_write)
				return '打开写入文件失败';
			
			fwrite($fp_write, $data);
			fflush($fp_write);
			
			$_SESSION['slice']++;
			
			/*	如果获取的数据已经达到足够，则进行合并	*/
			if ($_SESSION['slice'] == $_SESSION['slices'])
			{
				set_time_limit($config['time_limit']);
				return merge_file($_SESSION['filename'], $_SESSION['slices']);
			}
			
			return "success";
		}
		else
			return "未初始化";
	}
	else if(!empty($_GET['filename']) && !empty($_GET['slices']))
	{
		/*	此处使用GET方法时进行初始化	*/
		$_SESSION['filename'] = $_GET['filename'];
		$_SESSION['slices'] = $_GET['slices'];
		$_SESSION['slice'] = 0;
		
		return "success";
	}
	else
	{
		return "参数错误！";
	}
}

function _my_get_all_headers()
{
	/*	取得http请求头中的信息	*/
	$result = Array();
	foreach ($_SERVER as $key=>$value)
	{
		if (substr($key, 0, 5) == 'HTTP_')
			$result[str_replace('HTTP_', '', $key)] = $value;
	}
	
	return $result;
}

function merge_file($filename, $num)
{
	/*	合并文件用	*/
	$fp_write = fopen(PATH.$filename, "ab+");
	
	if (!$fp_write)
		return '打开写入文件失败';
	
	for ($i=0; $i<$num; $i++)
	{
		/*	文件锁	*/
		if (flock($fp_write, LOCK_EX)) 
		{
			$fp_input = fopen(PATH.$filename.".".$i, "rb");
			
			if (!$fp_input)
				return "打开输入文件失败";
			
			fwrite($fp_write, fread($fp_input, get_file_size($fp_input)));
			fflush($fp_write);
			fclose($fp_input);
			flock($fp_write, LOCK_UN);
		}
	}
	
	return "合并完成";
}

function get_file_size($fp)
{
	/*	返回文件大小	*/
	if (!$fp)
		return '错误的文件指针';
	
	fseek($fp, 0, SEEK_END);
	$size = ftell($fp);
	fseek($fp, 0, SEEK_SET);
	
	return $size;
}

function get_json_array($filename)
{
	/* 从json文件中获取数据	*/
	$fp = fopen($filename, "rb");
	
	if (!$fp)
		exit('打开配置文件失败');
	
	$buffer = fread($fp, get_file_size($fp));
	
	return json_decode($buffer, true);
}

?>