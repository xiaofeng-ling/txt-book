<?php
require_once("user.php");

$book = "111.txt";
$offset = 0;

$user = new User("123");
echo $user->get_prev($book, 4096);
?>
