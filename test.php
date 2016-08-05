<?php

include "user.php";

$temp = new User("123");
$buffer = "";

$temp->push_function($buffer, $temp->get_next("天才黄金手.txt", 3000));
$temp->run();

echo $buffer;

?>
