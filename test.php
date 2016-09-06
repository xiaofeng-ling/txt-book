<?php
require_once("user.php");

$book = "5.++%E5%8F%98%E9%87%8FVar%E7%AE%80%E4%BB%8B%E3%80%81Cmd%E7%AE%80%E4%BB%8B%E3%80%81AI%E5%BC%80%E5%85%B3%E8%AE%BE%E7%BD%AE%E5%8F%8A%E6%89%8B%E6%93%8D%E6%8C%87%E4%BB%A4%E4%BF%AE%E6%94%B9.txt";
$offset = 34534;

$user = new User("123");
echo $user->save_offset($book, $offset);
?>
