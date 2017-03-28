<?php
/*
** 事件处理类，主要根据前台反馈的数据处理做出不同的相应
** 与前台进行如下数据约定
** 请求数据json解码得到数组[operator_code, message]
** operator_code	操作
** 	1		get_next
**	2		get_prev
**	3		save_offset
**	4		add_book
**	5		del_book
**	6		get_all_books
**	7		reset_prev_offset
**  8		get_all_class
**	9		get_all_books(user)
**	10		get_class_num
*/

require_once("base/event.class.php");
require_once("connect.class.php");
require_once("error.class.php");
require_once("user.common.class.php");

class EventCommon extends AbstractEvent
{

	private $connect;
	private $user;
	private $error;
	private $data;

	public function __construct($user_name)
	{
		$this->user = new UserCommon($user_name);
		$this->error = new Error();
		$this->connect = new Connect();
		$this->data = new Data(SQL_DATABASE, SQL_USERNAME, SQL_PASSWORD);
	}

	public function event_handle()
	{
		if (!$this->error->is_no_error($this->connect->init()))
			return $this->connect->get_last_error();
		
		
		if (!$this->error->is_no_error($this->connect->get_data()))
			return $this->connect->error->get_last_error();
		
		/* 解码数据为数组，而非默认的对象类型 */
		$data = json_decode($this->connect->get_net_buffer(), true);
		$operator_code = (int)$data['operator_code'];

		$result;

		switch ($operator_code)
		{
			case 1:
				$result = $this->user->get_next($data['book'], 1024);
				break;

			case 2: 
				$result = $this->user->get_prev($data['book'], 1024);
				break;

			case 3: 
				$result = $this->user->save_offset($data['book'], $data['offset']);
				break;
			
			case 4: 
				$result = $this->user->add_book($data['book']);
				break;

			case 5:	
				$result = $this->user->del_book($data['book']);
				break;

			case 6:
				$result = $this->data->get_all_books($data['category'], $data['pages']);
				break;

			case 7: 
				$result = $this->user->reset_prev_offset($data['book']);
				break;
				
			case 8:
				$result = $this->data->get_all_class();
				break;
				
			case 9:
				$result = $this->user->get_all_books();
				break;
				
			case 10:
				$result = $this->data->get_class_num($data['class']);
				break;

			default:
				$result = $this->error->error_handle(4, "未识别的操作!");
				break;
		}
				// 这里可以加一句写入日志操作
				return $this->connect->send_data(json_encode($result));
	}
}


?>
