<?php
$str = "中文";

for ($i=0; $i<strlen($str); $i++)
{
    echo (substr($str, $i, $i+1) & hex2bin('C0')) == hex2bin('80') ? 1: 0;
}
?>
