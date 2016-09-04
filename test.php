<?php
require_once("user.php");

$user = new User("123");
echo $user->get_prev('ttt.txt', 1024);
?>
