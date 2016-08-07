<?php

include "user.php";

$temp = new User("123");

echo $temp->get_prev("1111.txt", 20);
echo "\n";

?>
