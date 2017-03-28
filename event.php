<?php

session_start();
require_once("./include/event.common.class.php");
require_once("./include/data.class.php");

if (!isset($_SESSION['user']))
{
	$error = new Error();
	$error->error_handle(4, "noLogin");
	echo $error->get_last_error_JSON();
	exit();
}

$event = new EventCommon($_SESSION['user']);

$event->event_handle();

?>